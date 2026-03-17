<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Services\FareCalculator;
use App\Services\FareService;
use App\Support\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FareController extends Controller
{
    /**
     * Estimate minimum fare for a potential trip request without saving it.
     */
    public function estimate(Request $request, FareCalculator $fareCalculator, FareService $fareService): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:ride,parcel,food'],
            'pickup_lat' => ['required', 'numeric'],
            'pickup_lng' => ['required', 'numeric'],
            'dropoff_lat' => ['required', 'numeric'],
            'dropoff_lng' => ['required', 'numeric'],
        ]);

        // Build an in-memory TripRequest instance (not persisted)
        $fakeRequest = new TripRequest($validated);

        $estimated = $fareCalculator->estimateForRequest($fakeRequest);

        $distanceKm = Geo::distanceKm(
            $validated['pickup_lat'],
            $validated['pickup_lng'],
            $validated['dropoff_lat'],
            $validated['dropoff_lng'],
        );

        $typeConfig = $this->configForType($validated['type']);

        $breakdown = $fareService->calculate(
            distanceKm: $distanceKm,
            baseFare: $typeConfig['base_fare'],
            perKmRate: $typeConfig['per_km_rate'],
            minimumFare: $typeConfig['minimum_fare'],
        );

        return response()->json([
            'estimated_fare' => $estimated,
            'fare' => $breakdown,
        ]);
    }

    protected function configForType(string $type): array
    {
        $all = config('fare.types');
        return $all[$type] ?? $all['default'];
    }
}

