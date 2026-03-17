<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default fare settings per request type
    |--------------------------------------------------------------------------
    */
    'types' => [
        'ride' => [
            'base_fare' => 20.0,
            'per_km_rate' => 10.0,
            'minimum_fare' => 40.0,
        ],
        'parcel' => [
            'base_fare' => 25.0,
            'per_km_rate' => 12.0,
            'minimum_fare' => 50.0,
        ],
        'food' => [
            'base_fare' => 30.0,
            'per_km_rate' => 10.0,
            'minimum_fare' => 60.0,
        ],
        'default' => [
            'base_fare' => 20.0,
            'per_km_rate' => 10.0,
            'minimum_fare' => 40.0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Surge / tiered pricing
    |--------------------------------------------------------------------------
    |
    | You can control a global multiplier via env, and optionally define
    | simple time-of-day rules for peak hours.
    |
    */

    'multiplier' => [
        // Global switch from .env, e.g. 1.0 (no surge), 1.2 (20% increase)
        'default' => (float) env('FARE_MULTIPLIER', 1.0),

        // Basic time-based rules (24h format hours, local app time)
        'time_rules' => [
            [
                'from_hour' => 7,
                'to_hour' => 9,
                'multiplier' => 1.2,
                'label' => 'Morning peak (20% increase)',
            ],
            [
                'from_hour' => 17,
                'to_hour' => 20,
                'multiplier' => 1.2,
                'label' => 'Evening peak (20% increase)',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipping presets
    |--------------------------------------------------------------------------
    */
    'tips' => [
        // Allowed preset amounts in local currency
        'allowed_amounts' => [10, 20, 50],
    ],
];

