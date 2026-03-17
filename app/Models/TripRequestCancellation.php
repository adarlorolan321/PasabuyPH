<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripRequestCancellation extends Model
{
    protected $fillable = [
        'trip_request_id',
        'user_id',
        'cancelled_by',
        'reason',
        'late',
    ];

    public function tripRequest(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

