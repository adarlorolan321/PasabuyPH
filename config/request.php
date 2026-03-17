<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trip request expiry (minutes)
    |--------------------------------------------------------------------------
    */
    'expiry_minutes' => env('REQUEST_EXPIRY_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Max active pending requests per user
    |--------------------------------------------------------------------------
    */
    'max_active_pending' => env('REQUEST_MAX_ACTIVE_PENDING', 3),

    /*
    |--------------------------------------------------------------------------
    | Soft strike threshold within rolling window
    |--------------------------------------------------------------------------
    */
    'soft_strike_threshold' => env('REQUEST_SOFT_STRIKE_THRESHOLD', 5),
];

