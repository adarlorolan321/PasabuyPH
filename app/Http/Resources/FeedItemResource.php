<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'type' => $this['type'],
            'trip' => $this['trip'] ? new TripResource($this['trip']) : null,
            'request' => $this['request'] ? new TripRequestResource($this['request']) : null,
            'user' => $this['user'] ? [
                'id' => $this['user']->id,
                'name' => $this['user']->name,
            ] : null,
            'created_at' => $this['created_at']?->toIso8601String(),
        ];
    }
}

