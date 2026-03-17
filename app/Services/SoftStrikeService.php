<?php

namespace App\Services;

use App\Models\TripRequestCancellation;
use App\Models\User;

class SoftStrikeService
{
    public function maybeApplyStrike(int $userId): void
    {
        $threshold = (int) config('request.soft_strike_threshold', 5);

        if ($threshold <= 0) {
            return;
        }

        $since = now()->subDays(30);

        $count = TripRequestCancellation::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $since)
            ->count();

        if ($count >= $threshold) {
            User::where('id', $userId)->update(['soft_strikes' => $count]);
        }
    }
}

