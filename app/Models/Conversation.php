<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['user_one_id', 'user_two_id'];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Get the other participant in the conversation from the current user's perspective.
     */
    public function otherParticipant(User $user): User
    {
        return (int) $this->user_one_id === (int) $user->id
            ? $this->userTwo
            : $this->userOne;
    }

    /**
     * Find or create a conversation between two users. Uses canonical ordering (user_one_id < user_two_id).
     */
    public static function between(User $userOne, User $userTwo): self
    {
        $id1 = (int) $userOne->id;
        $id2 = (int) $userTwo->id;

        if ($id1 === $id2) {
            throw new \InvalidArgumentException('Cannot create a conversation between the same user.');
        }

        $first = min($id1, $id2);
        $second = max($id1, $id2);

        return self::firstOrCreate(
            ['user_one_id' => $first, 'user_two_id' => $second],
            ['user_one_id' => $first, 'user_two_id' => $second]
        );
    }

    /**
     * Scope: conversations where the given user is a participant.
     */
    public function scopeForUser($query, User $user): void
    {
        $query->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
    }
}
