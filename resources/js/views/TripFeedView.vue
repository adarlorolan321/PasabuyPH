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
                        <p v-if="item.request?.price_offer">
                            💰 Reward: ₱{{ item.request.price_offer }}
                        </p>
                    </div>

                    <div class="mt-2 flex justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500 text-white hover:bg-emerald-600"
                        >
                            Accept Request
                        </button>
                    </div>
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
import { useFeedStore } from '@/stores/feedStore';

const feedStore = useFeedStore();
const { items, loading, error, loadNextPage, reset } = feedStore;

const sentinelRef = ref(null);
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

