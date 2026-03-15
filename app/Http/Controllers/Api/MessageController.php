<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * List messages in a conversation (paginated).
     */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $perPage = (int) $request->get('per_page', 20);
        $perPage = min(max($perPage, 1), 100);

        $messages = $conversation->messages()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $items = $messages->getCollection()->map(fn (Message $m) => [
            'id' => $m->id,
            'body' => $m->body,
            'sender' => [
                'id' => $m->user->id,
                'name' => $m->user->name,
                'email' => $m->user->email,
            ],
            'read_at' => $m->read_at?->toIso8601String(),
            'created_at' => $m->created_at->toIso8601String(),
        ])->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message in a conversation.
     */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $user = $request->user();

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $message->load('user:id,name,email');

        return response()->json([
            'data' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                ],
                'read_at' => $message->read_at?->toIso8601String(),
                'created_at' => $message->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
