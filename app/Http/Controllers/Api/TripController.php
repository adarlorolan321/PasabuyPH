<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    /**
     * List trips for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $trips = $request->user()
            ->trips()
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $trips]);
    }

    /**
     * Create a trip for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $trip = $request->user()->trips()->create($validated);

        return response()->json(['data' => $trip], 201);
    }

    /**
     * Update the given trip (must belong to the authenticated user).
     */
    public function update(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->user_id !== $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $trip->update($validated);

        return response()->json(['data' => $trip->fresh()]);
    }
}
