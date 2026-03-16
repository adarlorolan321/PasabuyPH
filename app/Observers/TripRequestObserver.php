<?php

namespace App\Observers;

use App\Models\FeedPost;
use App\Models\TripRequest;

class TripRequestObserver
{
    /**
     * Handle the TripRequest "created" event.
     */
    public function created(TripRequest $tripRequest): void
    {
        $map = [
            'ride' => 'ride_request',
            'parcel' => 'parcel_request',
            'food' => 'food_request',
        ];

        FeedPost::create([
            'user_id' => $tripRequest->requester_id,
            'type' => $map[$tripRequest->type] ?? 'request',
            'trip_id' => $tripRequest->trip_id,
            'trip_request_id' => $tripRequest->id,
        ]);
    }

    /**
     * Handle the TripRequest "updated" event.
     */
    public function updated(TripRequest $tripRequest): void
    {
        //
    }

    /**
     * Handle the TripRequest "deleted" event.
     */
    public function deleted(TripRequest $tripRequest): void
    {
        //
    }

    /**
     * Handle the TripRequest "restored" event.
     */
    public function restored(TripRequest $tripRequest): void
    {
        //
    }

    /**
     * Handle the TripRequest "force deleted" event.
     */
    public function forceDeleted(TripRequest $tripRequest): void
    {
        //
    }
}
