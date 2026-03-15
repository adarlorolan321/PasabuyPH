<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'pickup_lat',
        'pickup_lng',
        'delivery_lat',
        'delivery_lng',
        'pickup_address',
        'delivery_address',
        'status',
    ];

    /**
     * Get the user that owns the delivery request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
