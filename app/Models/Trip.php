<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'origin',
        'origin_lat',
        'origin_lng',
        'destination',
        'destination_lat',
        'destination_lng',
        'start_date',
        'end_date',
        'departure_time',
        'vehicle_type',
        'notes',
        'services',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origin_lat' => 'float',
            'origin_lng' => 'float',
            'destination_lat' => 'float',
            'destination_lng' => 'float',
            'services' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'departure_time' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the trip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }
}
