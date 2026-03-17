<?php

namespace App\Http\Resources;

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
        ];
    }
}

