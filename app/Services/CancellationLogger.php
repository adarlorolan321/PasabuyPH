<?php

namespace App\Services;

use App\Models\TripRequest;
use App\Models\TripRequestCancellation;

class CancellationLogger
{
    public function logCancellation(TripRequest $request, string $by, ?string $reason = null): void
    {
        $userId = null;
        if ($by === 'customer') {
            $userId = $request->requester_id;
        } elseif ($by === 'driver') {
            $userId = optional($request->trip)->user_id;
        }

        if (! $userId) {
            return;
        }

        $late = false;
        if ($request->trip && $request->trip->departure_time) {
            $minutesDiff = $request->trip->departure_time->diffInMinutes(now(), false);
            // Late if cancelled within 10 minutes of departure time or after.
            $late = $minutesDiff >= -10;
        }

        TripRequestCancellation::create([
            'trip_request_id' => $request->id,
            'user_id' => $userId,
            'cancelled_by' => $by,
            'reason' => $reason,
            'late' => $late,
        ]);

        app(SoftStrikeService::class)->maybeApplyStrike($userId);
    }
}

