<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use App\Models\Trip;
use App\Models\TripRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function store(Request $request, Trip $trip): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $allowed = config('fare.tips.allowed_amounts', [10, 20, 50]);
        if (! in_array($validated['amount'], $allowed, true)) {
            return response()->json([
                'message' => 'Invalid tip amount.',
                'allowed_amounts' => $allowed,
            ], 422);
        }

        // Only allow tipping if there is at least one completed request on this trip
        $hasCompletedRequest = TripRequest::query()
            ->where('trip_id', $trip->id)
            ->where('status', 'completed')
            ->exists();

        if (! $hasCompletedRequest) {
            return response()->json([
                'message' => 'You can only tip after the trip is completed.',
            ], 422);
        }

        // Prevent duplicate tips per user per trip
        $tip = Tip::firstOrCreate(
            [
                'trip_id' => $trip->id,
                'user_id' => $user->id,
            ],
            [
                'amount' => $validated['amount'],
            ]
        );

        // If tip already existed, optionally update to new amount
        if ($tip->wasRecentlyCreated === false && $tip->amount !== $validated['amount']) {
            $tip->amount = $validated['amount'];
            $tip->save();
        }

        return response()->json([
            'data' => [
                'id' => $tip->id,
                'trip_id' => $tip->trip_id,
                'user_id' => $tip->user_id,
                'amount' => $tip->amount,
            ],
            'message' => 'Tip sent successfully.',
        ], 201);
    }
}

