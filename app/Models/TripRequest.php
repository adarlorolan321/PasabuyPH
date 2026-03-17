<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED_BY_CUSTOMER = 'cancelled_by_customer';
    public const STATUS_CANCELLED_BY_DRIVER = 'cancelled_by_driver';
    public const STATUS_EXPIRED = 'expired';

    public const ALLOWED_TRANSITIONS = [
        self::STATUS_PENDING => [
            self::STATUS_ACCEPTED,
            self::STATUS_EXPIRED,
            self::STATUS_CANCELLED_BY_CUSTOMER,
        ],
        self::STATUS_ACCEPTED => [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED_BY_CUSTOMER,
            self::STATUS_CANCELLED_BY_DRIVER,
        ],
        self::STATUS_REJECTED => [],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED_BY_CUSTOMER => [],
        self::STATUS_CANCELLED_BY_DRIVER => [],
        self::STATUS_EXPIRED => [],
    ];

    protected $fillable = [
        'trip_id',
        'requester_id',
        'type',
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'dropoff_location',
        'dropoff_lat',
        'dropoff_lng',
        'details',
        'price_offer',
        'status',
        'expires_at',
        'parcel_length_cm',
        'parcel_width_cm',
        'parcel_height_cm',
        'parcel_weight_kg',
        'parcel_photo_path',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'parcel_length_cm' => 'float',
        'parcel_width_cm' => 'float',
        'parcel_height_cm' => 'float',
        'parcel_weight_kg' => 'float',
        'expires_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function canTransitionTo(string $next): bool
    {
        $current = $this->status ?? self::STATUS_PENDING;

        return in_array($next, self::ALLOWED_TRANSITIONS[$current] ?? [], true);
    }

    public function transitionTo(string $next): void
    {
        if (! $this->canTransitionTo($next)) {
            abort(422, 'Invalid status transition.');
        }

        $this->status = $next;
        $this->save();
    }
}
