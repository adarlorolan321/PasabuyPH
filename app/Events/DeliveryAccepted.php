<?php

namespace App\Events;

use App\Models\DeliveryRequest;
use App\Models\Trip;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DeliveryRequest $deliveryRequest,
        public Trip $trip
    ) {}

    /**
     * Notify the user who created the delivery request.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->deliveryRequest->user_id),
        ];
    }

    /**
     * Event name for the client.
     */
    public function broadcastAs(): string
    {
        return 'delivery.accepted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'delivery_request_id' => $this->deliveryRequest->id,
            'trip_id' => $this->trip->id,
            'message' => 'Your delivery request has been accepted.',
            'pickup_address' => $this->deliveryRequest->pickup_address,
            'delivery_address' => $this->deliveryRequest->delivery_address,
        ];
    }
}
