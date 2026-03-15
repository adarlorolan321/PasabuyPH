<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * User can view the conversation if they are a participant.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return (int) $conversation->user_one_id === (int) $user->id
            || (int) $conversation->user_two_id === (int) $user->id;
    }
}
