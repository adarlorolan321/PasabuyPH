<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Find trips</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Browse upcoming trips you can join or send deliveries with.
            </p>
        </div>

        <section class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                    Upcoming trips
                </h2>
                <button
                    type="button"
                    class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline"
                    :disabled="loading"
                    @click="loadTrips"
                >
                    {{ loading ? 'Refreshing…' : 'Refresh' }}
                </button>
            </div>

            <p v-if="error" class="text-sm text-red-600 dark:text-red-400">
                {{ error }}
            </p>

            <div v-else-if="trips.length" class="grid gap-3">
                <article
                    v-for="trip in trips"
                    :key="trip.id"
                    class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer"
                    :class="selectedTrip && selectedTrip.id === trip.id ? 'ring-2 ring-emerald-500' : ''"
                    @click="selectTrip(trip)"
                >
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-2">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-medium">
                            {{ trip.vehicle_type || 'Trip' }}
                        </span>
                        <span v-if="trip.user" class="truncate max-w-[120px]">
                            {{ trip.user.name }}
                        </span>
                        <span v-if="trip.departure_time">
                            {{ formatTime(trip.departure_time) }}
                        </span>
                    </div>
                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ municipality(trip.origin) }} → {{ municipality(trip.destination) }}
                    </p>

                    <div v-if="trip.services && trip.services.length" class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                        <span class="font-medium">Available:</span>
                        <span
                            v-for="service in trip.services"
                            :key="service"
                            class="inline-flex items-center gap-1 mr-3"
                        >
                            {{ serviceLabel(service) }}
                        </span>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-if="canMessage(trip)"
                            type="button"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500 text-white hover:bg-emerald-600 min-h-[32px]"
                            @click.stop="messageDriver(trip)"
                        >
                            Message driver
                        </button>
                    </div>

                    <!-- Map preview directly under the selected trip -->
                    <TripMapPreview v-if="selectedTrip && selectedTrip.id === trip.id" :trip="trip" />
                </article>
            </div>
            <p v-else-if="!loading" class="text-sm text-slate-500 dark:text-slate-400">
                No upcoming trips yet. Be the first to post one.
            </p>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios';
import TripMapPreview from '@/components/TripMapPreview.vue';
import { useAuthStore } from '@/stores/authStore';
import { useChatApi } from '@/composables/useChatApi';

const router = useRouter();
const authStore = useAuthStore();
const { getOrCreateConversation } = useChatApi();

const trips = ref([]);
const loading = ref(false);
const error = ref('');
const selectedTrip = ref(null);

function formatTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function municipality(text) {
    if (!text) return '';
    return String(text).split(',')[0].trim();
}

function serviceLabel(service) {
    const key = String(service || '').toLowerCase();
    switch (key) {
        case 'ride':
            return '🏍 Ride';
        case 'parcel':
            return '📦 Parcel';
        case 'food':
            return '🍔 Food';
        default:
            return service;
    }
}

async function loadTrips() {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await api.get('/trips/upcoming');
        const list = data.data || [];
        trips.value = [...list].sort((a, b) => {
            const da = a.departure_time ? new Date(a.departure_time).getTime() : 0;
            const db = b.departure_time ? new Date(b.departure_time).getTime() : 0;
            return da - db; // oldest (earliest) first
        });
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not load trips. Try again.';
        trips.value = [];
    } finally {
        loading.value = false;
    }
}

function selectTrip(trip) {
    selectedTrip.value = trip;
}

function canMessage(trip) {
    const current = authStore.user;
    return !!(current && trip.user && trip.user.id && trip.user.id !== current.id);
}

async function messageDriver(trip) {
    if (!canMessage(trip)) return;
    try {
        const conv = await getOrCreateConversation(trip.user.id);
        router.push({ name: 'chat', params: { conversationId: String(conv.id) } });
    } catch (e) {
        console.error('Failed to start conversation', e);
        // Keep UX simple here; list still works even if messaging fails.
    }
}

onMounted(() => {
    loadTrips();
});
</script>
