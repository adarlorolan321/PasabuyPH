<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Authentication endpoints. Protected routes should use auth:sanctum.
| Throttling is applied to prevent abuse (e.g. 5 login attempts per minute).
|
*/

Route::prefix('auth')->middleware('throttle:60,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->apiResource('trips', TripController::class)->only(['index', 'store', 'update']);
