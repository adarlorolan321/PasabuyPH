<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vue SPA routes
|--------------------------------------------------------------------------
| Single entry point for the Vue 3 app. Vue Router handles client-side routes.
| API is under /api (see routes/api.php).
*/

Route::get('/', fn () => view('app'))->name('home');
Route::get('/{any}', fn () => view('app'))
    ->where('any', '^(?!api$)(?!api/).*')
    ->name('spa.fallback');
