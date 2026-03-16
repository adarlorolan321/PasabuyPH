<template>
    <div class="location-picker space-y-4">
        <div v-if="loadError" class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 text-sm">
            {{ loadError }}
        </div>
        <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pickup</label>
                    <input
                        ref="pickupInputRef"
                        type="text"
                        placeholder="Search pickup location..."
                        class="w-full px-3 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dropoff</label>
                    <input
                        ref="dropoffInputRef"
                        type="text"
                        placeholder="Search dropoff location..."
                        class="w-full px-3 py-3 text-base border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    />
                </div>
            </div>
            <div
                ref="mapRef"
                class="w-full h-64 sm:h-80 min-h-[200px] rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden bg-gray-200 dark:bg-gray-700"
            />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useGoogleMaps } from '@/composables/useGoogleMaps';

const emit = defineEmits(['pickup-selected', 'dropoff-selected']);

const mapRef = ref(null);
const pickupInputRef = ref(null);
const dropoffInputRef = ref(null);
const loadError = ref('');

let map = null;
let pickupMarker = null;
let dropoffMarker = null;
let pickupAutocomplete = null;
let dropoffAutocomplete = null;
// Default to Manila, Philippines
const defaultCenter = { lat: 14.5995, lng: 120.9842 };
const defaultZoom = 10;

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
        scale: 12,
        fillColor: '#22c55e',
        fillOpacity: 1,
        strokeColor: '#fff',
        strokeWeight: 2,
    };
    const dropoffIcon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 12,
        fillColor: '#ef4444',
        fillOpacity: 1,
        strokeColor: '#fff',
        strokeWeight: 2,
    };

    if (pickupInputRef.value) {
        pickupAutocomplete = new google.maps.places.Autocomplete(pickupInputRef.value, {
            fields: ['geometry', 'formatted_address'],
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'ph' },
        });
        pickupAutocomplete.addListener('place_changed', () => {
            const place = pickupAutocomplete.getPlace();
            if (!place.geometry?.location) return;
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            const address = place.formatted_address || `${lat}, ${lng}`;
            if (!pickupMarker) {
                pickupMarker = new google.maps.Marker({
                    map,
                    position: place.geometry.location,
                    icon: pickupIcon,
                    title: 'Pickup',
                });
            } else {
                pickupMarker.setPosition(place.geometry.location);
            }
            updateMapBounds();
            emit('pickup-selected', { address, lat, lng });
        });
    }

    if (dropoffInputRef.value) {
        dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInputRef.value, {
            fields: ['geometry', 'formatted_address'],
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'ph' },
        });
        dropoffAutocomplete.addListener('place_changed', () => {
            const place = dropoffAutocomplete.getPlace();
            if (!place.geometry?.location) return;
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            const address = place.formatted_address || `${lat}, ${lng}`;
            if (!dropoffMarker) {
                dropoffMarker = new google.maps.Marker({
                    map,
                    position: place.geometry.location,
                    icon: dropoffIcon,
                    title: 'Dropoff',
                });
            } else {
                dropoffMarker.setPosition(place.geometry.location);
            }
            updateMapBounds();
            emit('dropoff-selected', { address, lat, lng });
        });
    }
}

function updateMapBounds() {
    if (!map || !pickupMarker || !dropoffMarker) return;
    const bounds = new window.google.maps.LatLngBounds();
    bounds.extend(pickupMarker.getPosition());
    bounds.extend(dropoffMarker.getPosition());
    map.fitBounds(bounds, 60);
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
    map = null;
});
</script>
