<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Find trips</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Set pickup and dropoff on the map or search, then find matching rides.
            </p>
        </div>

        <MapPickupDropoff
            @pickup-changed="pickup = $event"
            @dropoff-changed="dropoff = $event"
        />

        <div class="flex flex-col sm:flex-row gap-3">
            <button
                type="button"
                class="px-5 py-3 rounded-xl bg-emerald-500 text-white font-semibold hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed min-h-[44px]"
                :disabled="searching || !hasValidCoordinates"
                @click="searchTrips"
            >
                {{ searching ? 'Searching…' : 'Search trips' }}
            </button>
            <p v-if="!hasValidCoordinates" class="text-sm text-slate-500 dark:text-slate-400 self-center">
                Set both pickup and dropoff on the map or via search.
            </p>
        </div>

        <section v-if="searched" class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                {{ searchError ? 'Search failed' : results.length ? 'Matching trips' : 'No trips found' }}
            </h2>
            <p v-if="searchError" class="text-sm text-red-600 dark:text-red-400">{{ searchError }}</p>
            <div v-else-if="results.length" class="grid gap-3">
                <article
                    v-for="trip in results"
                    :key="trip.id"
                    class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ trip.vehicle_type }}</span>
                        <span v-if="trip.user">{{ trip.user.name }}</span>
                        <span v-if="trip.departure_time">{{ formatDate(trip.departure_time) }}</span>
                    </div>
                    <p class="font-medium text-slate-800 dark:text-slate-100">
                        {{ trip.origin }} → {{ trip.destination }}
                    </p>
                </article>
            </div>
            <p v-else class="text-sm text-slate-500 dark:text-slate-400">
                No trips match this route in the selected radius. Try a larger area or post a trip.
            </p>
        </section>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import MapPickupDropoff from '@/components/MapPickupDropoff.vue';
import api from '@/api/axios';

const pickup = ref({ lat: 0, lng: 0, address: '' });
const dropoff = ref({ lat: 0, lng: 0, address: '' });
const searching = ref(false);
const searched = ref(false);
const results = ref([]);
const searchError = ref('');

const hasValidCoordinates = computed(() => {
    const p = pickup.value;
    const d = dropoff.value;
    return p && d && typeof p.lat === 'number' && typeof p.lng === 'number' && typeof d.lat === 'number' && typeof d.lng === 'number';
});

function formatDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString([], { dateStyle: 'short' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

async function searchTrips() {
    if (!hasValidCoordinates.value) return;
    searching.value = true;
    searchError.value = '';
    searched.value = true;
    try {
        const { data } = await api.get('/trips/search', {
            params: {
                pickup_lat: pickup.value.lat,
                pickup_lng: pickup.value.lng,
                dropoff_lat: dropoff.value.lat,
                dropoff_lng: dropoff.value.lng,
                radius_km: 25,
            },
        });
        results.value = data.data || [];
    } catch (e) {
        searchError.value = e.response?.data?.message || 'Search failed. Try again.';
        results.value = [];
    } finally {
        searching.value = false;
    }
}
</script>
