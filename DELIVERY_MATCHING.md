# Delivery request ↔ trip matching

Trips can be matched to delivery requests by **geographic proximity** using latitude/longitude and a **configurable radius** (in km).

## Config

- **Config key:** `config('services.delivery.match_radius_km')` (default: `10`)
- **Env:** `DELIVERY_MATCH_RADIUS_KM=10` in `.env`

## Data

- **Trips** must have `origin_lat`, `origin_lng`, `destination_lat`, `destination_lng` (nullable). When creating/updating a trip via the API you can send these so matching can use them.
- **Delivery requests** have `pickup_lat`, `pickup_lng`, `delivery_lat`, `delivery_lng` (and optional address strings).

## Usage

```php
use App\Models\DeliveryRequest;
use App\Services\TripMatchService;

$service = app(TripMatchService::class);

// Match trips for a delivery request (pickup/delivery within radius)
$request = DeliveryRequest::find(1);
$trips = $service->matchTripsForDeliveryRequest($request);

// Custom radius (km)
$trips = $service->matchTripsForDeliveryRequest($request, 25.0);

// Trips whose origin is within radius of a point
$trips = $service->matchTripsNearPoint(40.7128, -74.0060, 15.0, 'origin');

// Trips whose destination is within radius of a point
$trips = $service->matchTripsNearPoint(40.7128, -74.0060, 10.0, 'destination');
```

## Logic

- **matchTripsForDeliveryRequest:** Returns trips where:
  - Trip origin is within `radius_km` of the delivery request’s **pickup** (Haversine).
  - Trip destination is within `radius_km` of the delivery request’s **delivery** (Haversine).
- **matchTripsNearPoint:** Returns trips whose **origin** or **destination** (chosen by the last argument) is within `radius_km` of the given `(lat, lng)`.

## Implementation

- **MySQL/MariaDB:** Haversine distance is done in SQL for efficiency.
- **SQLite (and others):** Distance is computed in PHP after loading candidates (no RADIANS in default SQLite).

The Haversine formula uses Earth radius 6371 km.
