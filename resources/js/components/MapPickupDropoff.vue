<template>
    <div class="map-pickup-dropoff space-y-4">
        <div v-if="loadError" class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 text-sm">
            {{ loadError }}
        </div>
        <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pickup</label>
                    <input
                        ref="pickupInputRef"
                        v-model="pickupAddress"
                        type="text"
                        placeholder="Search or drag the green marker..."
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dropoff</label>
                    <input
                        ref="dropoffInputRef"
                        v-model="dropoffAddress"
                        type="text"
                        placeholder="Search or drag the red marker..."
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
            </div>
            <div
                ref="mapRef"
                class="w-full h-72 sm:h-96 min-h-[240px] rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-200 dark:bg-slate-700 shadow-sm"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useGoogleMaps } from '@/composables/useGoogleMaps';

const emit = defineEmits(['pickup-changed', 'dropoff-changed']);

const mapRef = ref(null);
const pickupInputRef = ref(null);
const dropoffInputRef = ref(null);
const pickupAddress = ref('');
const dropoffAddress = ref('');
const loadError = ref('');

let map = null;
let pickupMarker = null;
let dropoffMarker = null;
let pickupAutocomplete = null;
let dropoffAutocomplete = null;
let geocoder = null;
const defaultCenter = { lat: 14.5995, lng: 120.9842 };
const defaultZoom = 11;

function emitPickup(lat, lng, address) {
    emit('pickup-changed', { lat, lng, address: address || `${lat.toFixed(5)}, ${lng.toFixed(5)}` });
}

function emitDropoff(lat, lng, address) {
    emit('dropoff-changed', { lat, lng, address: address || `${lat.toFixed(5)}, ${lng.toFixed(5)}` });
}

function reverseGeocode(google, lat, lng, callback) {
    if (!geocoder) geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
        if (status === 'OK' && results?.[0]) {
            callback(results[0].formatted_address);
        } else {
            callback(null);
        }
    });
}

function updateMapBounds(google) {
    if (!map || !pickupMarker || !dropoffMarker) return;
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(pickupMarker.getPosition());
    bounds.extend(dropoffMarker.getPosition());
    map.fitBounds(bounds, 80);
}

function initMap(google) {
    if (!mapRef.value) return;
    map = new google.maps.Map(mapRef.value, {
        center: defaultCenter,
        zoom: defaultZoom,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
        zoomControl: true,
    });

    const pickupIcon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 14,
        fillColor: '#22c55e',
        fillOpacity: 1,
        strokeColor: '#fff',
        strokeWeight: 3,
    };
    const dropoffIcon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 14,
        fillColor: '#ef4444',
        fillOpacity: 1,
        strokeColor: '#fff',
        strokeWeight: 3,
    };

    pickupMarker = new google.maps.Marker({
        map,
        position: defaultCenter,
        icon: pickupIcon,
        title: 'Pickup',
        draggable: true,
    });
    dropoffMarker = new google.maps.Marker({
        map,
        position: { lat: defaultCenter.lat + 0.02, lng: defaultCenter.lng + 0.02 },
        icon: dropoffIcon,
        title: 'Dropoff',
        draggable: true,
    });

    pickupMarker.addListener('dragend', () => {
        const pos = pickupMarker.getPosition();
        const lat = pos.lat();
        const lng = pos.lng();
        reverseGeocode(google, lat, lng, (addr) => {
            pickupAddress.value = addr || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            emitPickup(lat, lng, pickupAddress.value);
        });
    });

    dropoffMarker.addListener('dragend', () => {
        const pos = dropoffMarker.getPosition();
        const lat = pos.lat();
        const lng = pos.lng();
        reverseGeocode(google, lat, lng, (addr) => {
            dropoffAddress.value = addr || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            emitDropoff(lat, lng, dropoffAddress.value);
        });
    });

    if (pickupInputRef.value) {
        pickupAutocomplete = new google.maps.places.Autocomplete(pickupInputRef.value, {
            fields: ['geometry', 'formatted_address'],
            types: ['establishment', 'geocode'],
        });
        pickupAutocomplete.addListener('place_changed', () => {
            const place = pickupAutocomplete.getPlace();
            if (!place.geometry?.location) return;
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            const address = place.formatted_address || `${lat}, ${lng}`;
            pickupAddress.value = address;
            pickupMarker.setPosition(place.geometry.location);
            updateMapBounds(google);
            emitPickup(lat, lng, address);
        });
    }

    if (dropoffInputRef.value) {
        dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInputRef.value, {
            fields: ['geometry', 'formatted_address'],
            types: ['establishment', 'geocode'],
        });
        dropoffAutocomplete.addListener('place_changed', () => {
            const place = dropoffAutocomplete.getPlace();
            if (!place.geometry?.location) return;
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            const address = place.formatted_address || `${lat}, ${lng}`;
            dropoffAddress.value = address;
            dropoffMarker.setPosition(place.geometry.location);
            updateMapBounds(google);
            emitDropoff(lat, lng, address);
        });
    }

    emitPickup(defaultCenter.lat, defaultCenter.lng, '');
    emitDropoff(defaultCenter.lat + 0.02, defaultCenter.lng + 0.02, '');
}

onMounted(async () => {
    try {
        const google = await useGoogleMaps();
        initMap(google);
    } catch (e) {
        loadError.value = e.message || 'Google Maps could not be loaded. Add VITE_GOOGLE_MAPS_API_KEY to .env.';
    }
});

onBeforeUnmount(() => {
    if (pickupMarker) pickupMarker.setMap(null);
    if (dropoffMarker) dropoffMarker.setMap(null);
    pickupAutocomplete = null;
    dropoffAutocomplete = null;
    geocoder = null;
    map = null;
});
</script>
