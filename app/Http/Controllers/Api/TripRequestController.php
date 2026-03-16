<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    /**
     * Create a new trip request.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'type' => ['required', 'string', 'in:ride,parcel,food'],
            'pickup_location' => ['required', 'string', 'max:255'],
            'dropoff_location' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'price_offer' => ['nullable', 'numeric', 'min:0'],
        ]);

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

        $requestModel = TripRequest::create([
            'trip_id' => $trip->id,
            'requester_id' => $user->id,
            'type' => $validated['type'],
            'pickup_location' => $validated['pickup_location'],
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'details' => $validated['details'] ?? null,
            'price_offer' => $validated['price_offer'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['data' => $requestModel->fresh()], 201);
    }

    public function accept(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        if ($tripRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be accepted.'], 422);
        }
        $tripRequest->update(['status' => 'accepted']);
        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function reject(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        if ($tripRequest->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be rejected.'], 422);
        }
        $tripRequest->update(['status' => 'rejected']);
        return response()->json(['data' => $tripRequest->fresh()]);
    }

    public function complete(Request $request, TripRequest $tripRequest): JsonResponse
    {
        $this->authorizeOwner($request, $tripRequest);
        if (! in_array($tripRequest->status, ['accepted'], true)) {
            return response()->json(['message' => 'Only accepted requests can be completed.'], 422);
        }
        $tripRequest->update(['status' => 'completed']);
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
