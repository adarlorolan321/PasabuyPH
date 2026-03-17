<?php

namespace App\Services;

use App\Models\TripRequest;
use App\Support\Geo;

class FareCalculator
{
    /**
     * @var \App\Services\FareService
     */
    protected $fareService;

    public function __construct(FareService $fareService)
    {
        $this->fareService = $fareService;
    }

    public function estimateForRequest(TripRequest $request): ?float
    {
        if (
            $request->pickup_lat === null ||
            $request->pickup_lng === null ||
            $request->dropoff_lat === null ||
            $request->dropoff_lng === null
        ) {
            return null;
        }

        $distanceKm = Geo::distanceKm(
            $request->pickup_lat,
            $request->pickup_lng,
            $request->dropoff_lat,
            $request->dropoff_lng
        );

        $typeConfig = $this->configForType($request->type);

        $breakdown = $this->fareService->calculate(
            distanceKm: $distanceKm,
            baseFare: $typeConfig['base'],
            perKmRate: $typeConfig['per_km'],
            minimumFare: $typeConfig['min'],
        );

        // Preserve existing behaviour: just return rounded final fare.
        return (float) $breakdown['final_fare'];
    }

    protected function configForType(string $type): array
    {
        switch ($type) {
            case 'ride':
                return ['base' => 20.0, 'per_km' => 10.0, 'min' => 40.0];
            case 'parcel':
                return ['base' => 25.0, 'per_km' => 12.0, 'min' => 50.0];
            case 'food':
                return ['base' => 30.0, 'per_km' => 10.0, 'min' => 60.0];
            default:
                return ['base' => 20.0, 'per_km' => 10.0, 'min' => 40.0];
        }
    }
}

