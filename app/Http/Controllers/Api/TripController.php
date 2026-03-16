<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Services\TripMatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * List upcoming trips (public) for the Find Trips page.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $now = now();

        $trips = Trip::query()
            ->whereNotNull('departure_time')
            ->where('departure_time', '>=', $now)
            ->with('user:id,name,email')
            ->orderBy('departure_time')
            ->limit(100)
            ->get();

        $data = $trips->map(fn (Trip $trip) => [
            'id' => $trip->id,
            'origin' => $trip->origin,
            'origin_lat' => $trip->origin_lat,
            'origin_lng' => $trip->origin_lng,
            'destination' => $trip->destination,
            'destination_lat' => $trip->destination_lat,
            'destination_lng' => $trip->destination_lng,
            'departure_time' => $trip->departure_time?->toIso8601String(),
            'vehicle_type' => $trip->vehicle_type,
            'user' => $trip->relationLoaded('user') ? [
                'id' => $trip->user->id,
                'name' => $trip->user->name,
                'email' => $trip->user->email,
            ] : null,
        ])->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Search trips by pickup and dropoff coordinates (public, for Find Trips).
     */
    public function search(Request $request, TripMatchService $tripMatch): JsonResponse
    {
        $validated = $request->validate([
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:500'],
        ]);

        $radiusKm = isset($validated['radius_km']) ? (float) $validated['radius_km'] : null;
        $trips = $tripMatch->findTripsByCoordinates(
            (float) $validated['pickup_lat'],
            (float) $validated['pickup_lng'],
            (float) $validated['dropoff_lat'],
            (float) $validated['dropoff_lng'],
            $radiusKm
        );

        $data = $trips->map(fn (Trip $trip) => [
            'id' => $trip->id,
            'origin' => $trip->origin,
            'origin_lat' => $trip->origin_lat,
            'origin_lng' => $trip->origin_lng,
            'destination' => $trip->destination,
            'destination_lat' => $trip->destination_lat,
            'destination_lng' => $trip->destination_lng,
            'departure_time' => $trip->departure_time?->toIso8601String(),
            'vehicle_type' => $trip->vehicle_type,
            'user' => $trip->relationLoaded('user') ? [
                'id' => $trip->user->id,
                'name' => $trip->user->name,
            ] : null,
        ])->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Find trips within a radius (km) of a single point (e.g. \"trips near me\").
     */
    public function near(Request $request, TripMatchService $tripMatch): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'point' => ['nullable', 'in:origin,destination'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radiusKm = isset($validated['radius_km']) ? (float) $validated['radius_km'] : null;
        $point = $validated['point'] ?? 'origin';

        $trips = $tripMatch->matchTripsNearPoint($lat, $lng, $radiusKm, $point);

        $data = $trips->map(fn (Trip $trip) => [
            'id' => $trip->id,
            'origin' => $trip->origin,
            'origin_lat' => $trip->origin_lat,
            'origin_lng' => $trip->origin_lng,
            'destination' => $trip->destination,
            'destination_lat' => $trip->destination_lat,
            'destination_lng' => $trip->destination_lng,
            'departure_time' => $trip->departure_time?->toIso8601String(),
            'vehicle_type' => $trip->vehicle_type,
            'user' => $trip->relationLoaded('user') ? [
                'id' => $trip->user->id,
                'name' => $trip->user->name,
            ] : null,
        ])->values();

        return response()->json(['data' => $data]);
    }

    /**
     * List trips for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $trips = $request->user()
            ->trips()
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $trips]);
    }

    /**
     * Create a trip for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'origin_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'departure_time' => ['required', 'date'],
            'vehicle_type' => ['required', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        if (empty($validated['title'])) {
            $validated['title'] = $validated['origin'] . ' → ' . $validated['destination'];
        }

        $trip = $request->user()->trips()->create($validated);

        return response()->json(['data' => $trip], 201);
    }

    /**
     * Update the given trip (must belong to the authenticated user).
     */
    public function update(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'origin' => ['sometimes', 'string', 'max:255'],
            'destination' => ['sometimes', 'string', 'max:255'],
            'origin_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'destination_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'departure_time' => ['sometimes', 'date'],
            'vehicle_type' => ['sometimes', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $trip->update($validated);

        return response()->json(['data' => $trip->fresh()]);
    }
}
