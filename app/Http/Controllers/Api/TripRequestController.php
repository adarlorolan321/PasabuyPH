<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Services\FareCalculator;
use App\Services\CancellationLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TripRequestController extends Controller
{
    /**
     * Create a new trip request.
     */
    public function store(Request $request, FareCalculator $fareCalculator): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'type' => ['required', 'string', 'in:ride,parcel,food'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['nullable', 'numeric'],
            'pickup_lng' => ['nullable', 'numeric'],
            'dropoff_location' => ['nullable', 'string', 'max:255'],
            'dropoff_lat' => ['nullable', 'numeric'],
            'dropoff_lng' => ['nullable', 'numeric'],
            'details' => ['nullable', 'string'],
            'price_offer' => ['nullable', 'numeric', 'min:0'],
            'parcel_length_cm' => ['nullable', 'numeric', 'min:0'],
            'parcel_width_cm' => ['nullable', 'numeric', 'min:0'],
            'parcel_height_cm' => ['nullable', 'numeric', 'min:0'],
            'parcel_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'parcel_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $trip = null;
        if (! empty($validated['trip_id'])) {
            $trip = Trip::findOrFail($validated['trip_id']);

            // Ensure request type is allowed by trip's services
            $services = $trip->services ?? [];
            if (! in_array($validated['type'], $services, true)) {
                return response()->json([
                    'message' => 'This trip does not accept that type of request.',
                ], 422);
            }

            // Prevent owner from requesting their own trip
            if ((int) $trip->user_id === (int) $user->id) {
                return response()->json([
                    'message' => 'You cannot request your own trip.',
                ], 422);
            }
        }

        // Limit active pending/accepted requests per user
        $maxActive = (int) config('request.max_active_pending', 3);
        $activeCount = TripRequest::query()
            ->where('requester_id', $user->id)
            ->whereIn('status', [TripRequest::STATUS_PENDING, TripRequest::STATUS_ACCEPTED])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();

        if ($activeCount >= $maxActive) {
            return response()->json([
                'message' => 'You already have active requests. Please wait or cancel existing ones.',
            ], 422);
        }

        $parcelPhotoPath = null;
        if ($request->hasFile('parcel_photo')) {
            $parcelPhotoPath = $request->file('parcel_photo')->store('parcel-photos', 'public');
        }

        $parcelData = [
            'parcel_length_cm' => null,
            'parcel_width_cm' => null,
            'parcel_height_cm' => null,
            'parcel_weight_kg' => null,
            'parcel_photo_path' => $parcelPhotoPath,
        ];

        if ($validated['type'] === 'parcel') {
            $parcelData['parcel_length_cm'] = $validated['parcel_length_cm'] ?? null;
            $parcelData['parcel_width_cm'] = $validated['parcel_width_cm'] ?? null;
            $parcelData['parcel_height_cm'] = $validated['parcel_height_cm'] ?? null;
            $parcelData['parcel_weight_kg'] = $validated['parcel_weight_kg'] ?? null;
        }

        $requestModel = TripRequest::create([
            'trip_id' => $trip?->id,
            'requester_id' => $user->id,
            'type' => $validated['type'],
            'pickup_location' => $validated['pickup_location'],
            'pickup_lat' => $validated['pickup_lat'] ?? null,
            'pickup_lng' => $validated['pickup_lng'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'dropoff_lat' => $validated['dropoff_lat'] ?? null,
            'dropoff_lng' => $validated['dropoff_lng'] ?? null,
            'details' => $validated['details'] ?? null,
            'price_offer' => $validated['price_offer'] ?? null,
            'status' => TripRequest::STATUS_PENDING,
            'expires_at' => now()->addMinutes(
                (int) config('request.expiry_minutes', 15)
            ),
        ] + $parcelData);

        // Enforce minimum fare: price_offer must not be less than estimated fare
        $estimated = $fareCalculator->estimateForRequest($requestModel);
        if ($estimated !== null && ($requestModel->price_offer === null || $requestModel->price_offer < $estimated)) {
            $requestModel->price_offer = $estimated;
            $requestModel->save();
        }

        return response()->json(['data' => $requestModel->fresh()], 201);
    }

    public function accept(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        $tripRequest->transitionTo(TripRequest::STATUS_ACCEPTED);
        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function reject(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        $tripRequest->transitionTo(TripRequest::STATUS_REJECTED);
        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function complete(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        $tripRequest->transitionTo(TripRequest::STATUS_COMPLETED);
        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function cancelByCustomer(Request $request, TripRequest $tripRequest, CancellationLogger $logger): JsonResponse
    {
        if ((int) $tripRequest->requester_id !== (int) $request->user()->id) {
            abort(403);
        }

        $tripRequest->transitionTo(TripRequest::STATUS_CANCELLED_BY_CUSTOMER);

        $logger->logCancellation($tripRequest, 'customer', $request->input('reason'));

        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function cancelByDriver(Request $request, TripRequest $tripRequest, CancellationLogger $logger): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);

        $tripRequest->transitionTo(TripRequest::STATUS_CANCELLED_BY_DRIVER);

        $logger->logCancellation($tripRequest, 'driver', $request->input('reason'));

        return response()->json(['data' => $tripRequest->fresh()]);
    }

    protected function authorizeOwner(Request $request, TripRequest $tripRequest): void
    {
        $user = $request->user();
        $tripRequest->loadMissing('trip');
        if (! $tripRequest->trip || (int) $tripRequest->trip->user_id !== (int) $user->id) {
            abort(403, 'You are not allowed to manage this request.');
        }
    }
}
