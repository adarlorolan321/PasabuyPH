<?php

namespace App\Services;

use App\Models\TripRequest;
use App\Support\Geo;

class FareCalculator
{
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

        $config = $this->configForType($request->type);

        $raw = $config['base'] + $distanceKm * $config['per_km'];
        $fare = max($config['min'], $raw);

        // Round up to nearest 5 pesos
        return ceil($fare / 5) * 5;
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

