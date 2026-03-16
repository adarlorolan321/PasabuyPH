<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_time' => $this->departure_time?->toIso8601String(),
            'vehicle_type' => $this->vehicle_type,
            'services' => $this->services ?? [],
        ];
    }
}

