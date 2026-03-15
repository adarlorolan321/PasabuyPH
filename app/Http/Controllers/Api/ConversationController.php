<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    /**
     * List conversation threads for the authenticated user.
     * Each item includes the other participant and latest message preview.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['userOne', 'userTwo', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (Conversation $conversation) use ($user) {
                $other = $conversation->otherParticipant($user);
                $latest = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $other->id,
                        'name' => $other->name,
                        'email' => $other->email,
                    ],
                    'last_message' => $latest ? [
                        'id' => $latest->id,
                        'body' => \Str::limit($latest->body, 80),
                        'sender_id' => $latest->user_id,
                        'created_at' => $latest->created_at->toIso8601String(),
                    ] : null,
                    'updated_at' => $conversation->updated_at->toIso8601String(),
                ];
            });

        return response()->json(['data' => $conversations]);
    }

    /**
     * Get or create a conversation with another user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $other = User::findOrFail($validated['user_id']);

        if ((int) $other->id === (int) $user->id) {
            return response()->json(['message' => 'Cannot start a conversation with yourself.'], 422);
        }

        $conversation = Conversation::between($user, $other);
        $conversation->load(['userOne', 'userTwo']);

        return response()->json([
            'data' => [
                'id' => $conversation->id,
                'user_one' => [
                    'id' => $conversation->userOne->id,
                    'name' => $conversation->userOne->name,
                    'email' => $conversation->userOne->email,
                ],
                'user_two' => [
                    'id' => $conversation->userTwo->id,
                    'name' => $conversation->userTwo->name,
                    'email' => $conversation->userTwo->email,
                ],
                'created_at' => $conversation->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Show a single conversation (thread) for the authenticated user.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $user = $request->user();
        $conversation->load(['userOne', 'userTwo']);
        $other = $conversation->otherParticipant($user);

        return response()->json([
            'data' => [
                'id' => $conversation->id,
                'other_user' => [
                    'id' => $other->id,
                    'name' => $other->name,
                    'email' => $other->email,
                ],
                'created_at' => $conversation->created_at->toIso8601String(),
                'updated_at' => $conversation->updated_at->toIso8601String(),
            ],
        ]);
    }
}
