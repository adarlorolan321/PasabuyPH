# Google Maps integration

The app uses Google Maps for pickup/dropoff selection with **Places Autocomplete** and **map pins** on the “Post a trip” page.

## Setup

1. **Create an API key** in [Google Cloud Console](https://console.cloud.google.com/apis/credentials).
2. **Enable these APIs** for the project:
   - **Maps JavaScript API**
   - **Places API**
3. **Restrict the key** (recommended): set an HTTP referrer restriction for your domain (e.g. `http://localhost:*`, your production domain).
4. **Add to `.env`**:
   ```env
   VITE_GOOGLE_MAPS_API_KEY=your_api_key_here
   ```
5. Restart the Vite dev server so it picks up the new env var.

## Usage

- **CreateTripView** includes a **LocationPicker** section: two autocomplete fields (Pickup / Dropoff) and a map.
- Choosing a place from autocomplete places a **green pin** (pickup) or **red pin** (dropoff) and fills the Origin/Destination form fields.
- Users can type origin/destination manually or use the map; both stay in sync.

## Components

- **`resources/js/composables/useGoogleMaps.js`** – Loads the Google Maps script (with Places) and returns a promise when ready.
- **`resources/js/components/LocationPicker.vue`** – Map, two autocomplete inputs, two markers; emits `pickup-selected` and `dropoff-selected` with `{ address, lat, lng }`.

If the API key is missing, the picker shows a short message instead of the map.
