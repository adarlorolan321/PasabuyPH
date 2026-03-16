<?php

namespace App\Providers;

use App\Models\Trip;
use App\Models\TripRequest;
use App\Observers\TripObserver;
use App\Observers\TripRequestObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Trip::observe(TripObserver::class);
        TripRequest::observe(TripRequestObserver::class);
    }
}
