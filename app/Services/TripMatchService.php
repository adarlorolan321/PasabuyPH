<?php

namespace App\Services;

use App\Models\DeliveryRequest;
use App\Models\Trip;
use Illuminate\Support\Collection;

class TripMatchService
{
    /**
     * Match delivery requests with trips based on geographic proximity.
     * Finds trips whose origin is within the given radius of the request's pickup point
     * and whose destination is within the given radius of the request's delivery point.
     *
     * @param  float|null  $radiusKm  Max distance in km (uses config if null)
     * @return Collection<int, Trip>
     */
    public function matchTripsForDeliveryRequest(DeliveryRequest $request, ?float $radiusKm = null): Collection
    {
        $radiusKm ??= (float) config('services.delivery.match_radius_km', 10);

        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return $this->matchTripsWithHaversineMysql($request, $radiusKm);
        }

        return $this->matchTripsInPhp($request, $radiusKm);
    }

    /**
     * Match trips using Haversine formula in MySQL/MariaDB.
     */
    protected function matchTripsWithHaversineMysql(DeliveryRequest $request, float $radiusKm): Collection
    {
        $pickupLat = $request->pickup_lat;
        $pickupLng = $request->pickup_lng;
        $deliveryLat = $request->delivery_lat;
        $deliveryLng = $request->delivery_lng;

        $trips = Trip::query()
            ->whereNotNull('origin_lat')
            ->whereNotNull('origin_lng')
            ->whereNotNull('destination_lat')
            ->whereNotNull('destination_lng')
            ->whereRaw(
                '(6371 * acos(least(1, greatest(-1,
                    cos(radians(?)) * cos(radians(origin_lat)) * cos(radians(origin_lng) - radians(?))
                    + sin(radians(?)) * sin(radians(origin_lat))
                )))) <= ?',
                [$pickupLat, $pickupLng, $pickupLat, $radiusKm]
            )
            ->whereRaw(
                '(6371 * acos(least(1, greatest(-1,
                    cos(radians(?)) * cos(radians(destination_lat)) * cos(radians(destination_lng) - radians(?))
                    + sin(radians(?)) * sin(radians(destination_lat))
                )))) <= ?',
                [$deliveryLat, $deliveryLng, $deliveryLat, $radiusKm]
            )
            ->orderBy('departure_time')
            ->get();

        return $trips;
    }

    /**
     * Fallback: match trips by computing distance in PHP (DB-agnostic, e.g. SQLite).
     */
    protected function matchTripsInPhp(DeliveryRequest $request, float $radiusKm): Collection
    {
        $trips = Trip::query()
            ->whereNotNull('origin_lat')
            ->whereNotNull('origin_lng')
            ->whereNotNull('destination_lat')
            ->whereNotNull('destination_lng')
            ->orderBy('departure_time')
            ->get();

        return $trips->filter(function (Trip $trip) use ($request, $radiusKm) {
            $originDistanceKm = $this->haversineDistanceKm(
                (float) $request->pickup_lat,
                (float) $request->pickup_lng,
                (float) $trip->origin_lat,
                (float) $trip->origin_lng
            );
            $destDistanceKm = $this->haversineDistanceKm(
                (float) $request->delivery_lat,
                (float) $request->delivery_lng,
                (float) $trip->destination_lat,
                (float) $trip->destination_lng
            );

            return $originDistanceKm <= $radiusKm && $destDistanceKm <= $radiusKm;
        })->values();
    }

    /**
     * Haversine distance in km between two points.
     */
    public function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Find trips near a single point (e.g. for "trips near me").
     *
     * @param  float  $lat
     * @param  float  $lng
     * @param  float|null  $radiusKm
     * @param  'origin'|'destination'  $point  Which trip point to match
     * @return Collection<int, Trip>
     */
    public function matchTripsNearPoint(float $lat, float $lng, ?float $radiusKm = null, string $point = 'origin'): Collection
    {
        $radiusKm ??= (float) config('services.delivery.match_radius_km', 10);

        $latCol = $point === 'origin' ? 'origin_lat' : 'destination_lat';
        $lngCol = $point === 'origin' ? 'origin_lng' : 'destination_lng';

        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return Trip::query()
                ->whereNotNull($latCol)
                ->whereNotNull($lngCol)
                ->whereRaw(
                    '(6371 * acos(least(1, greatest(-1,
                        cos(radians(?)) * cos(radians(' . $latCol . ')) * cos(radians(' . $lngCol . ') - radians(?))
                        + sin(radians(?)) * sin(radians(' . $latCol . '))
                    )))) <= ?',
                    [$lat, $lng, $lat, $radiusKm]
                )
                ->orderBy('departure_time')
                ->get();
        }

        $trips = Trip::query()
            ->whereNotNull($latCol)
            ->whereNotNull($lngCol)
            ->orderBy('departure_time')
            ->get();

        return $trips->filter(function (Trip $trip) use ($lat, $lng, $radiusKm, $point) {
            $tripLat = $point === 'origin' ? (float) $trip->origin_lat : (float) $trip->destination_lat;
            $tripLng = $point === 'origin' ? (float) $trip->origin_lng : (float) $trip->destination_lng;

            return $this->haversineDistanceKm($lat, $lng, $tripLat, $tripLng) <= $radiusKm;
        })->values();
    }
}
