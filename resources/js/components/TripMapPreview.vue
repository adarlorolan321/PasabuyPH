<template>
    <div v-if="hasCoords" class="space-y-2 mt-3">
        <div class="text-xs text-slate-500 dark:text-slate-400">
            <p>
                <span class="font-semibold">Origin:</span>
                {{ trip.origin }} ({{ trip.origin_lat }}, {{ trip.origin_lng }})
            </p>
            <p>
                <span class="font-semibold">Destination:</span>
                {{ trip.destination }} ({{ trip.destination_lat }}, {{ trip.destination_lng }})
            </p>
        </div>
        <div
            ref="mapRef"
            class="w-full h-56 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-200 dark:bg-slate-700 shadow-sm"
        />
    </div>
    <p v-else class="text-xs text-slate-500 dark:text-slate-400 mt-2">No coordinates available for this trip.</p>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useGoogleMaps } from '@/composables/useGoogleMaps';

const props = defineProps({
    trip: {
        type: Object,
        required: true,
    },
});

const mapRef = ref(null);
let map = null;
let originMarker = null;
let destMarker = null;
let directionsService = null;
let directionsRenderer = null;

const hasCoords = computed(
    () =>
        props.trip &&
        props.trip.origin_lat != null &&
        props.trip.origin_lng != null &&
        props.trip.destination_lat != null &&
        props.trip.destination_lng != null,
);

async function init() {
    if (!hasCoords.value || !mapRef.value) return;

    const google = await useGoogleMaps();

    const origin = { lat: props.trip.origin_lat, lng: props.trip.origin_lng };
    const dest = { lat: props.trip.destination_lat, lng: props.trip.destination_lng };

    map = new google.maps.Map(mapRef.value, {
        center: origin,
        zoom: 12,
        streetViewControl: false,
        fullscreenControl: true,
        mapTypeControl: false,
        zoomControl: true,
    });

    // Icon logic based on vehicle_type (pickup icon)
    const type = (props.trip.vehicle_type || '').toLowerCase();
    let originIcon;
    if (type === 'motorcycle') {
        originIcon = {
            url: 'https://maps.google.com/mapfiles/kml/shapes/motorcycling.png',
            scaledSize: new google.maps.Size(32, 32),
        };
    } else if (type === 'car') {
        originIcon = {
            url: 'https://maps.google.com/mapfiles/kml/shapes/cabs.png',
            scaledSize: new google.maps.Size(32, 32),
        };
    } else {
        originIcon = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#22c55e',
            fillOpacity: 1,
            strokeColor: '#fff',
            strokeWeight: 2,
        };
    }

    // Pickup marker
    originMarker = new google.maps.Marker({
        map,
        position: origin,
        title: 'Origin',
        icon: originIcon,
    });

    // Dropoff marker: red location pin for all types
    destMarker = new google.maps.Marker({
        map,
        position: dest,
        title: 'Destination',
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
        },
    });

    const bounds = new google.maps.LatLngBounds();
    bounds.extend(origin);
    bounds.extend(dest);
    map.fitBounds(bounds, 80);

    // Draw driving route between origin and destination (if Directions API enabled)
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map,
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: '#0ea5e9',
            strokeOpacity: 0.9,
            strokeWeight: 5,
        },
    });

    directionsService.route(
        {
            origin,
            destination: dest,
            travelMode: google.maps.TravelMode.DRIVING,
        },
        (result, status) => {
            if (status === 'OK' && result) {
                directionsRenderer.setDirections(result);
            } else {
                // Fallback: keep just the straight markers / bounds
                directionsRenderer.setMap(null);
                directionsRenderer = null;
            }
        },
    );
}

onMounted(() => {
    init();
});

watch(
    () => props.trip,
    () => {
        if (originMarker) originMarker.setMap(null);
        if (destMarker) destMarker.setMap(null);
        if (directionsRenderer) directionsRenderer.setMap(null);
        map = null;
        originMarker = null;
        destMarker = null;
        directionsRenderer = null;
        init();
    },
);

onBeforeUnmount(() => {
    if (originMarker) originMarker.setMap(null);
    if (destMarker) destMarker.setMap(null);
    if (directionsRenderer) directionsRenderer.setMap(null);
    map = null;
});
</script>

