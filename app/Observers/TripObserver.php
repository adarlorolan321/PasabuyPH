<?php

namespace App\Observers;

use App\Models\FeedPost;
use App\Models\Trip;

class TripObserver
{
    /**
     * Handle the Trip "created" event.
     */
    public function created(Trip $trip): void
    {
        FeedPost::create([
            'user_id' => $trip->user_id,
            'type' => 'trip_posted',
            'trip_id' => $trip->id,
        ]);
    }

    /**
     * Handle the Trip "updated" event.
     */
    public function updated(Trip $trip): void
    {
        //
    }

    /**
     * Handle the Trip "deleted" event.
     */
    public function deleted(Trip $trip): void
    {
        //
    }

    /**
     * Handle the Trip "restored" event.
     */
    public function restored(Trip $trip): void
    {
        //
    }

    /**
     * Handle the Trip "force deleted" event.
     */
    public function forceDeleted(Trip $trip): void
    {
        //
    }
}
