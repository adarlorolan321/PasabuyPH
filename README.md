# MotoPeer

**Ride together. Deliver together.**

MotoPeer is a ride- and delivery-matching app. Users can post trips, find trips by pickup and dropoff on a map, chat with each other, and (on the backend) match delivery requests to trips by location.

---

## Tech stack

| Layer      | Stack |
|-----------|--------|
| **Backend** | Laravel 12, PHP 8.2+, Sanctum (API auth), Reverb (WebSockets) |
| **Frontend** | Vue 3, Vue Router, Pinia, Vite, Tailwind CSS v4 |
| **PWA**   | vite-plugin-pwa (offline, installable) |
| **Maps**  | Google Maps JavaScript API + Places (autocomplete, draggable markers) |

---

## Features

- **Auth** – Register, login, logout via API (Sanctum tokens).
- **Trips** – Post a trip (origin, destination, departure time, vehicle type). Optional map picker for coordinates.
- **Find trips** – Map with draggable pickup/dropoff markers and Places autocomplete. Search returns trips whose route matches the selected points (Haversine, configurable radius).
- **Messaging** – Conversation threads and chat; list conversations, send messages, poll for new messages. Optional real-time via Reverb/Pusher.
- **Delivery matching** – Backend service matches delivery requests to trips by proximity (origin/destination within radius).
- **PWA** – Installable, offline caching, service worker.
- **Mobile-friendly** – Touch targets, safe areas, responsive layout, bottom nav.

---

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL/MariaDB or SQLite (for DB)
- (Optional) Google Maps API key for map and Find Trips features

---

## Installation

1. **Clone and install PHP dependencies**

   ```bash
   git clone <repo-url> rolan && cd rolan
   composer install
   ```

2. **Environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edit `.env`: set `APP_NAME`, `APP_URL`, and database (`DB_*`). For SQLite:

   ```env
   DB_CONNECTION=sqlite
   # leave DB_DATABASE or set to database/database.sqlite
   ```

   Create the SQLite file if needed:

   ```bash
   touch database/database.sqlite
   ```

3. **Migrations and seed**

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

   The seeder creates a sample user (see `DatabaseSeeder`; e.g. `sample@example.com` / `password`).

4. **Frontend**

   ```bash
   npm install
   npm run build
   ```

   For development:

   ```bash
   npm run dev
   ```

5. **Serve the app**

   ```bash
   php artisan serve
   ```

   Open `http://localhost:8000`. Log in with the seeded user or register a new one.

---

## Environment variables

| Variable | Description |
|---------|-------------|
| `APP_NAME`, `APP_URL`, `APP_DEBUG` | Standard Laravel app config. |
| `DB_*` | Database connection (SQLite or MySQL). |
| `VITE_GOOGLE_MAPS_API_KEY` | Google Maps JS + Places API key. Required for map picker and Find Trips map. |
| `DELIVERY_MATCH_RADIUS_KM` | Default radius (km) for matching delivery requests to trips (default: 10). |
| `BROADCAST_CONNECTION` | `log`, `reverb`, or `pusher` for real-time (e.g. chat/delivery notifications). |
| `REVERB_*` | Used when `BROADCAST_CONNECTION=reverb` for WebSockets. |

---

## Running in development

- **Laravel:** `php artisan serve`
- **Vite:** `npm run dev` (in another terminal)
- **Reverb (optional):** `php artisan reverb:start` for WebSocket broadcasting
- **Queue (optional):** `php artisan queue:work` if using queued jobs

Or use the Composer `dev` script if configured (e.g. server + queue + vite together).

---

## Project structure (overview)

```
app/
  Http/Controllers/Api/   # Auth, Trip, Conversation, Message controllers
  Models/                 # User, Trip, Conversation, Message, DeliveryRequest
  Services/               # TripMatchService (Haversine matching)
  Events/                 # MessageSent, DeliveryAccepted (broadcasting)
  Policies/               # ConversationPolicy
database/migrations/      # users, trips, conversations, messages, delivery_requests
resources/
  js/
    api/                  # Axios instance (Bearer, 401 handling)
    components/           # LocationPicker, MapPickupDropoff
    composables/          # useGoogleMaps, useChatApi
    layouts/              # AppLayout, BlankLayout
    router/               # Vue Router + auth guards
    stores/               # Pinia auth store
    views/                # Home, Login, Register, CreateTrip, FindTrips, Chat, About
routes/
  api.php                 # Auth, trips (index/store/update + search), conversations, messages
  channels.php            # Broadcasting channel auth (user, conversation)
```

---

## API (summary)

- **Auth:** `POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/auth/user` (Sanctum).
- **Trips:** `GET /api/trips` (my trips), `POST /api/trips`, `PUT/PATCH /api/trips/{id}` (auth). `GET /api/trips/search?pickup_lat=&pickup_lng=&dropoff_lat=&dropoff_lng=&radius_km=` (public).
- **Messaging:** `GET/POST /api/conversations`, `GET /api/conversations/{id}`, `GET/POST /api/conversations/{id}/messages` (auth).

---

## License

MIT.
