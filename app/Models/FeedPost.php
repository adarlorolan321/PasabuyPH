<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedPost extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'trip_id',
        'trip_request_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripRequest(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class);
    }
}
