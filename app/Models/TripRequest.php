<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripRequest extends Model
{
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
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
