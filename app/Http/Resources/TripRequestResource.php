<?php

namespace App\Http\Resources;

use App\Services\FareService;
use App\Services\FareCalculator;
use Illuminate\Http\Resources\Json\JsonResource;

class TripRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $fareCalculator = app(FareCalculator::class);
        $fareService = app(FareService::class);

        $distanceKm = null;
        $fareBreakdown = null;

        if (
            $this->pickup_lat !== null &&
            $this->pickup_lng !== null &&
            $this->dropoff_lat !== null &&
            $this->dropoff_lng !== null
        ) {
            $distanceKm = \App\Support\Geo::distanceKm(
                $this->pickup_lat,
                $this->pickup_lng,
                $this->dropoff_lat,
                $this->dropoff_lng
            );

            $typeConfig = config('fare.types')[$this->type] ?? config('fare.types.default');

            $fareBreakdown = $fareService->calculate(
                distanceKm: $distanceKm,
                baseFare: $typeConfig['base_fare'],
                perKmRate: $typeConfig['per_km_rate'],
                minimumFare: $typeConfig['minimum_fare'],
            );
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'pickup_location' => $this->pickup_location,
            'dropoff_location' => $this->dropoff_location,
            'details' => $this->details,
            'price_offer' => $this->price_offer,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'estimated_fare' => $fareCalculator->estimateForRequest($this->resource),
            'fare_breakdown' => $fareBreakdown,
        ];
    }
}

