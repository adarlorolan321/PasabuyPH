<template>
    <div class="space-y-4">
        <header class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Trip feed</h1>
            <button
                type="button"
                class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline"
                :disabled="loading"
                @click="refresh"
            >
                Refresh
            </button>
        </header>

        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">
            {{ error }}
        </p>

        <div v-if="!loading && !items.length && !error" class="text-sm text-slate-500 dark:text-slate-400">
            No activity yet.
        </div>

        <div class="space-y-3">
            <article
                v-for="item in items"
                :key="itemKey(item)"
                class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm p-4 space-y-2"
            >
                <!-- Trip posted -->
                <template v-if="item.type === 'trip_posted'">
                    <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                        <span>{{ vehicleIcon(item.trip?.vehicle_type) }}</span>
                        <span>
                            <strong>{{ item.user?.name || 'Someone' }}</strong> posted a trip
                        </span>
                    </div>
                    <p class="font-semibold text-slate-800 dark:text-slate-100">
                        {{ municipality(item.trip.origin) }} → {{ municipality(item.trip.destination) }}
                    </p>
                    <p v-if="item.trip.departure_time" class="text-xs text-slate-500 dark:text-slate-400">
                        {{ formatTimeOnly(item.trip.departure_time) }}
                    </p>
                    <p v-if="item.trip.services?.length" class="text-xs text-slate-600 dark:text-slate-300">
                        <span
                            v-for="s in item.trip.services"
                            :key="s"
                            class="block"
                        >
                            {{ serviceLabel(s) }}
                        </span>
                    </p>
                    <div class="flex justify-end items-center mt-2">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500 text-white hover:bg-emerald-600"
                        >
                            View Trip
                        </button>
                    </div>
                </template>

                <!-- Requests -->
                <template v-else>
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">
                            {{ requestTitle(item.type) }}
                        </p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                            {{ formatRelative(item.created_at) }}
                        </p>
                    </div>
                    <p class="text-sm text-slate-700 dark:text-slate-200">
                        <span class="mr-1">📍</span>
                        {{ item.request?.pickup_location }}
                        <span v-if="item.request?.dropoff_location">
                            <span class="mx-1">→</span>
                            {{ item.request.dropoff_location }}
                        </span>
                    </p>
                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-0.5">
                        <p v-if="item.request?.details">
                            {{ detailsLabel(item.type) }}: {{ item.request.details }}
                        </p>
                        <p v-if="item.request?.estimated_fare">
                            📏 Minimum fare for distance: ₱{{ item.request.estimated_fare }}
                        </p>
                        <p v-if="item.request && (item.request.price_offer || item.request.estimated_fare)">
                            💰 Reward shown to riders: ₱{{ rewardAmount(item.request) }}
                        </p>
                        <p
                            v-if="fareDistanceKm(item.request) !== null"
                            class="text-[11px]"
                        >
                            📍 Distance: {{ fareDistanceKm(item.request)?.toFixed(1) }} km
                        </p>
                        <p
                            v-if="
                                item.request &&
                                item.request.estimated_fare &&
                                item.request.price_offer &&
                                item.request.price_offer < item.request.estimated_fare
                            "
                            class="text-[11px] text-amber-600 dark:text-amber-400"
                        >
                            💡 Customer offer was ₱{{ item.request.price_offer }}, raised to minimum ₱{{
                                item.request.estimated_fare
                            }}.
                        </p>
                        <p
                            v-if="fareDistanceKm(item.request) !== null && fareDistanceKm(item.request) > 50"
                            class="text-[11px] text-amber-600 dark:text-amber-400"
                        >
                            Trips over 50km may require higher offers to attract riders.
                        </p>
                        <p
                            v-if="fareMultiplier(item.request) > 1"
                            class="text-[11px] text-amber-600 dark:text-amber-400"
                        >
                            Busy time: fares include a {{ surgePercent(item.request) }}% peak adjustment
                            <span v-if="fareAdjustmentLabel(item.request)">
                                ({{ fareAdjustmentLabel(item.request) }})
                            </span>
                        </p>
                        <p v-if="item.type === 'parcel_request' && hasParcelDimensions(item.request)">
                            📏 Size:
                            {{ item.request.parcel_length_cm }} ×
                            {{ item.request.parcel_width_cm }} ×
                            {{ item.request.parcel_height_cm }} cm
                        </p>
                        <p v-if="item.type === 'parcel_request' && item.request?.parcel_weight_kg">
                            ⚖️ Weight: {{ item.request.parcel_weight_kg }} kg
                        </p>
                    </div>

                    <div
                        v-if="item.type === 'parcel_request' && item.request?.parcel_photo_path"
                        class="mt-2"
                    >
                        <img
                            :src="parcelPhotoUrl(item.request.parcel_photo_path)"
                            alt="Parcel photo"
                            class="h-20 w-20 rounded-lg object-cover border border-slate-200 dark:border-slate-700"
                        />
                    </div>

                    <div class="mt-2 flex justify-between items-center gap-2">
                        <div
                            v-if="item.request?.status === 'completed' && item.request?.trip_id"
                            class="flex items-center gap-1"
                        >
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mr-1">
                                Tip your rider:
                            </span>
                            <button
                                v-for="amount in tipOptions"
                                :key="amount"
                                type="button"
                                class="px-2 py-1 rounded-full border border-emerald-500 text-[11px] font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/40 hover:bg-emerald-100"
                                :disabled="sendingTip"
                                @click="sendTip(item.request.trip_id, amount)"
                            >
                                ₱{{ amount }}
                            </button>
                        </div>
                        <div class="flex justify-end flex-1">
                            <button
                                type="button"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500 text-white hover:bg-emerald-600"
                            >
                                {{ item.request?.status === 'completed' ? 'Completed' : 'Accept Request' }}
                            </button>
                        </div>
                    </div>

                    <details
                        v-if="item.request?.fare_breakdown"
                        class="mt-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 px-3 py-2 text-[11px] text-slate-600 dark:text-slate-300"
                    >
                        <summary class="cursor-pointer select-none font-semibold text-slate-700 dark:text-slate-200">
                            How pricing works
                        </summary>
                        <div class="mt-1 space-y-0.5">
                            <p>Base fare: ₱{{ item.request.fare_breakdown.base_fare.toFixed(0) }}</p>
                            <p>Per km: ₱{{ item.request.fare_breakdown.per_km_rate.toFixed(0) }}</p>
                            <p>Distance: {{ item.request.fare_breakdown.distance.toFixed(1) }} km</p>
                            <p>Computed fare: ₱{{ item.request.fare_breakdown.computed_fare.toFixed(0) }}</p>
                            <p>Minimum fare: ₱{{ item.request.fare_breakdown.minimum_fare.toFixed(0) }}</p>
                            <p v-if="item.request.fare_breakdown.multiplier && item.request.fare_breakdown.multiplier !== 1">
                                Multiplier: ×{{ item.request.fare_breakdown.multiplier.toFixed(2) }}
                                <span v-if="item.request.fare_breakdown.adjustment_label">
                                    ({{ item.request.fare_breakdown.adjustment_label }})
                                </span>
                            </p>
                            <p class="font-semibold">
                                Final fare: ₱{{ item.request.fare_breakdown.final_fare.toFixed(0) }}
                            </p>
                        </div>
                    </details>
                </template>
            </article>
        </div>

        <!-- Infinite scroll sentinel -->
        <div ref="sentinelRef" class="h-8"></div>

        <div v-if="loading" class="text-xs text-slate-500 dark:text-slate-400 text-center py-2">
            Loading…
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import api from '@/api/axios';
import { useFeedStore } from '@/stores/feedStore';

const feedStore = useFeedStore();
const { items, loading, error, loadNextPage, reset } = feedStore;

const sentinelRef = ref(null);
const sendingTip = ref(false);
const tipOptions = [10, 20, 50];
let observer = null;

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

function vehicleIcon(type) {
    const key = String(type || '').toLowerCase();
    if (key === 'motorcycle') return '🛵';
    if (key === 'car' || key === 'van' || key === 'bus') return '🚗';
    return '🛣️';
}

function requestTitle(type) {
    switch (type) {
        case 'ride_request':
            return '🛵 Ride request';
        case 'parcel_request':
            return '📦 Parcel delivery request';
        case 'food_request':
            return '🍔 Food pasabay request';
        default:
            return 'Request';
    }
}

function detailsLabel(type) {
    switch (type) {
        case 'ride_request':
            return '👥 Passengers / notes';
        case 'parcel_request':
            return '📦 Item';
        case 'food_request':
            return '🍽 Order';
        default:
            return 'Details';
    }
}

function hasParcelDimensions(req) {
    return (
        req &&
        req.parcel_length_cm != null &&
        req.parcel_width_cm != null &&
        req.parcel_height_cm != null
    );
}

function parcelPhotoUrl(path) {
    return `/storage/${path}`;
}

function rewardAmount(req) {
    if (!req) return '';
    const offer = req.price_offer ?? 0;
    const est = req.estimated_fare ?? 0;
    const value = Math.max(offer, est);
    return value.toFixed(0);
}

function fareDistanceKm(req) {
    if (!req || !req.fare_breakdown || req.fare_breakdown.distance == null) return null;
    return Number(req.fare_breakdown.distance);
}

function fareMultiplier(req) {
    if (!req || !req.fare_breakdown) return 1;
    const m = Number(req.fare_breakdown.multiplier ?? 1);
    return m || 1;
}

function surgePercent(req) {
    const m = fareMultiplier(req);
    if (m <= 1) return 0;
    return Math.round((m - 1) * 100);
}

function fareAdjustmentLabel(req) {
    if (!req || !req.fare_breakdown) return '';
    return req.fare_breakdown.adjustment_label || '';
}

function formatDateTime(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return (
        d.toLocaleDateString([], { month: 'short', day: 'numeric' }) +
        ' ' +
        d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    );
}

function formatTimeOnly(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatRelative(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const diffMs = Date.now() - d.getTime();
    const diffMin = Math.round(diffMs / 60000);
    if (diffMin < 1) return 'just now';
    if (diffMin < 60) return `${diffMin} min ago`;
    const diffH = Math.round(diffMin / 60);
    if (diffH < 24) return `${diffH}h ago`;
    const diffD = Math.round(diffH / 24);
    return `${diffD}d ago`;
}

function itemKey(item) {
    const base = item.type + '|' + (item.created_at || '');
    if (item.request?.id) return base + '|req:' + item.request.id;
    if (item.trip?.id) return base + '|trip:' + item.trip.id;
    return base;
}

function refresh() {
    reset();
    loadNextPage();
}

async function sendTip(tripId, amount) {
    if (!tripId || sendingTip.value) return;
    sendingTip.value = true;
    try {
        await api.post(`/trips/${tripId}/tip`, { amount });
        // Lightweight confirmation; feed will reflect indirectly if needed
        alert('Tip sent successfully');
    } catch (e) {
        const msg = e.response?.data?.message || 'Could not send tip.';
        alert(msg);
    } finally {
        sendingTip.value = false;
    }
}

onMounted(() => {
    if (!items.length) {
        loadNextPage();
    }
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadNextPage();
                }
            });
        },
        { root: null, threshold: 0.5 },
    );
    if (sentinelRef.value) {
        observer.observe(sentinelRef.value);
    }
});

onBeforeUnmount(() => {
    if (observer && sentinelRef.value) {
        observer.unobserve(sentinelRef.value);
    }
});
</script>

