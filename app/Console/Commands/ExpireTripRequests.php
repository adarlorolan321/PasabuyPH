<?php

namespace App\Console\Commands;

use App\Models\TripRequest;
use Illuminate\Console\Command;

class ExpireTripRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trip-requests:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending trip requests whose expiry time has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        TripRequest::query()
            ->where('status', TripRequest::STATUS_PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->chunkById(100, function ($chunk) {
                foreach ($chunk as $request) {
                    $request->transitionTo(TripRequest::STATUS_EXPIRED);
                }
            });

        return self::SUCCESS;
    }
}

