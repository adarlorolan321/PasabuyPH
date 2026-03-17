<template>
    <div class="max-w-md mx-auto space-y-4">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Post a request</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Create an open ride, parcel, or food request so drivers can see and accept it.
        </p>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
            {{ error }}
        </div>

        <form class="space-y-3" @submit.prevent="handleSubmit">
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                <select
                    v-model="form.type"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                    required
                >
                    <option disabled value="">Select type</option>
                    <option value="ride">Ride request</option>
                    <option value="parcel">Parcel request</option>
                    <option value="food">Food request</option>
                </select>
            </div>

            <div class="space-y-2">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Set pickup and dropoff on the map so riders can see exactly where to go.
                </p>
                <MapPickupDropoff @pickup-changed="onPickupChanged" @dropoff-changed="onDropoffChanged" />
            </div>

            <div v-if="form.type === 'ride'">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Passengers</label>
                <input
                    v-model.number="extra.passengers"
                    type="number"
                    min="1"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                />
            </div>

            <div v-else-if="form.type === 'parcel'" class="space-y-2">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Item description</label>
                    <textarea
                        v-model="form.details"
                        rows="2"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                    ></textarea>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Length (cm)</label>
                        <input
                            v-model.number="form.parcel_length_cm"
                            type="number"
                            min="0"
                            step="0.1"
                            class="w-full px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs"
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Width (cm)</label>
                        <input
                            v-model.number="form.parcel_width_cm"
                            type="number"
                            min="0"
                            step="0.1"
                            class="w-full px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs"
                        />
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Height (cm)</label>
                        <input
                            v-model.number="form.parcel_height_cm"
                            type="number"
                            min="0"
                            step="0.1"
                            class="w-full px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Weight (kg)</label>
                    <input
                        v-model.number="form.parcel_weight_kg"
                        type="number"
                        min="0"
                        step="0.1"
                        class="w-full px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-xs"
                    />
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Parcel photo</label>
                    <input
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="block w-full text-xs text-slate-500 file:mr-2 file:px-2 file:py-1.5 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                        @change="onParcelPhotoChange"
                    />
                </div>
            </div>

            <div v-else-if="form.type === 'food'">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Restaurant</label>
                <input
                    v-model="extra.restaurant"
                    type="text"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                />
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1 mt-2">Order details</label>
                <textarea
                    v-model="form.details"
                    rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                ></textarea>
            </div>

            <div class="space-y-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Reward (₱)
                        </label>
                        <input
                            v-model.number="form.price_offer"
                            type="number"
                            min="0"
                            step="1"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                        />
                    </div>
                    <details class="text-[11px] text-slate-500 dark:text-slate-400 ml-2 min-w-[120px]">
                        <summary class="cursor-pointer select-none font-medium">
                            How pricing works
                        </summary>
                        <p class="mt-1">
                            We start from a base fare, add a per‑km rate based on distance, then apply a minimum fare
                            and any busy‑time multiplier.
                        </p>
                    </details>
                </div>
                <p v-if="estimatedFare !== null" class="text-[11px] text-slate-500 dark:text-slate-400">
                    Minimum fare for this distance:
                    <span class="font-semibold">₱{{ estimatedFare }}</span>
                </p>
                <p
                    v-if="estimatedFare !== null"
                    class="text-[11px] text-slate-600 dark:text-slate-300"
                >
                    You will likely pay around
                    <span class="font-semibold">₱{{ totalToPay }}</span>
                    (minimum fare + any extra reward you set).
                </p>
                <p
                    v-if="distanceKm !== null && distanceKm > 50"
                    class="text-[11px] text-amber-600 dark:text-amber-400"
                >
                    Trips over 50km may require higher offers to attract riders.
                </p>
                <p
                    v-if="surgeMultiplierNotice"
                    class="text-[11px] text-amber-600 dark:text-amber-400"
                >
                    {{ surgeMultiplierNotice }}
                </p>
            </div>

            <button
                type="submit"
                class="w-full py-2 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 disabled:opacity-50"
                :disabled="loading"
            >
                {{ loading ? 'Posting…' : 'Post request' }}
            </button>
        </form>

        <div
            v-if="form.type && form.pickup_location"
            class="mt-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-4 space-y-2"
        >
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                How this will look in the feed
            </p>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                {{ previewTitle }}
            </p>
            <p class="text-sm text-slate-700 dark:text-slate-200">
                <span class="mr-1">📍</span>
                {{ form.pickup_location }}
                <span v-if="form.dropoff_location">
                    <span class="mx-1">→</span>
                    {{ form.dropoff_location }}
                </span>
            </p>
            <p v-if="form.details" class="text-xs text-slate-600 dark:text-slate-300">
                {{ form.details }}
            </p>
            <p v-if="estimatedFare !== null" class="text-xs text-slate-600 dark:text-slate-300">
                📏 Minimum fare for distance: ₱{{ estimatedFare }}
            </p>
            <p v-if="totalToPay !== null" class="text-xs text-slate-600 dark:text-slate-300">
                💰 Reward shown to riders: ₱{{ totalToPay }}
            </p>
            <p
                v-if="distanceKm !== null"
                class="text-[11px] text-slate-500 dark:text-slate-400"
            >
                Distance: {{ distanceKm.toFixed(1) }} km
            </p>
            <p
                v-if="distanceKm !== null && distanceKm > 50"
                class="text-[11px] text-amber-600 dark:text-amber-400"
            >
                Trips over 50km may require higher offers to attract riders.
            </p>
            <details
                v-if="fareBreakdown"
                class="mt-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 px-3 py-2 text-[11px] text-slate-600 dark:text-slate-300"
            >
                <summary class="cursor-pointer select-none font-semibold text-slate-700 dark:text-slate-200">
                    How pricing works
                </summary>
                <div class="mt-1 space-y-0.5">
                    <p>Base fare: ₱{{ fareBreakdown.base_fare.toFixed(0) }}</p>
                    <p>Per km: ₱{{ fareBreakdown.per_km_rate.toFixed(0) }}</p>
                    <p>Distance: {{ fareBreakdown.distance.toFixed(1) }} km</p>
                    <p>Computed fare: ₱{{ fareBreakdown.computed_fare.toFixed(0) }}</p>
                    <p>Minimum fare: ₱{{ fareBreakdown.minimum_fare.toFixed(0) }}</p>
                    <p v-if="fareBreakdown.multiplier && fareBreakdown.multiplier !== 1">
                        Multiplier: ×{{ fareBreakdown.multiplier.toFixed(2) }}
                        <span v-if="fareBreakdown.adjustment_label">({{ fareBreakdown.adjustment_label }})</span>
                    </p>
                    <p class="font-semibold">
                        Final fare: ₱{{ fareBreakdown.final_fare.toFixed(0) }}
                    </p>
                </div>
            </details>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue';
import api from '@/api/axios';
import { useFeedStore } from '@/stores/feedStore';
import MapPickupDropoff from '@/components/MapPickupDropoff.vue';

const feedStore = useFeedStore();

const loading = ref(false);
const error = ref('');

const form = reactive({
    type: '',
    pickup_location: '',
    pickup_lat: null,
    pickup_lng: null,
    dropoff_location: '',
    dropoff_lat: null,
    dropoff_lng: null,
    details: '',
    price_offer: null,
    parcel_length_cm: null,
    parcel_width_cm: null,
    parcel_height_cm: null,
    parcel_weight_kg: null,
});

const extra = reactive({
    passengers: null,
    restaurant: '',
});

const parcelPhotoFile = ref(null);

const estimatedFare = ref(null);
const fareBreakdown = ref(null);

const totalToPay = computed(() => {
    if (estimatedFare.value == null) return null;
    const reward = form.price_offer ?? 0;
    const total = Math.max(estimatedFare.value, reward);
    return Math.round(total);
});

const distanceKm = computed(() => {
    if (!fareBreakdown.value || fareBreakdown.value.distance == null) return null;
    return Number(fareBreakdown.value.distance);
});

const surgeMultiplierNotice = computed(() => {
    if (!fareBreakdown.value) return '';
    const m = Number(fareBreakdown.value.multiplier ?? 1);
    if (!m || m <= 1) return '';
    const percent = Math.round((m - 1) * 100);
    const label = fareBreakdown.value.adjustment_label || `Busy time (${percent}% increase)`;
    return `Busy time: fares include a ${percent}% peak adjustment (${label}).`;
});

const previewTitle = computed(() => {
    const type = String(form.type || '').toLowerCase();
    switch (type) {
        case 'ride':
            return '🛵 Ride request';
        case 'parcel':
            return '📦 Parcel delivery request';
        case 'food':
            return '🍔 Food pasabay request';
        default:
            return 'Request';
    }
});

function onPickupChanged(payload) {
    form.pickup_location = payload.address;
    form.pickup_lat = payload.lat;
    form.pickup_lng = payload.lng;
}

function onDropoffChanged(payload) {
    form.dropoff_location = payload.address;
    form.dropoff_lat = payload.lat;
    form.dropoff_lng = payload.lng;
}

function onParcelPhotoChange(event) {
    const [file] = event.target.files || [];
    parcelPhotoFile.value = file || null;
}

watch(
    () => [form.type, form.pickup_lat, form.pickup_lng, form.dropoff_lat, form.dropoff_lng],
    async ([type, plat, plng, dlat, dlng]) => {
        // Reset when data is incomplete
        if (!type || plat == null || plng == null || dlat == null || dlng == null) {
            estimatedFare.value = null;
            fareBreakdown.value = null;
            return;
        }

        try {
            const { data } = await api.post('/fare/estimate', {
                type,
                pickup_lat: plat,
                pickup_lng: plng,
                dropoff_lat: dlat,
                dropoff_lng: dlng,
            });
            estimatedFare.value = data.estimated_fare ?? data.fare?.final_fare ?? null;
            fareBreakdown.value = data.fare ?? null;
        } catch (_e) {
            estimatedFare.value = null;
            fareBreakdown.value = null;
        }
    },
);

async function handleSubmit() {
    error.value = '';
    loading.value = true;
    try {
        let details = form.details;
        if (form.type === 'ride' && extra.passengers) {
            details = `Passengers: ${extra.passengers}${details ? ' - ' + details : ''}`;
        }
        if (form.type === 'food' && extra.restaurant) {
            details = `Restaurant: ${extra.restaurant}${details ? ' - ' + details : ''}`;
        }

        const fd = new FormData();
        fd.append('type', form.type);
        fd.append('pickup_location', form.pickup_location);
        if (form.pickup_lat != null) fd.append('pickup_lat', String(form.pickup_lat));
        if (form.pickup_lng != null) fd.append('pickup_lng', String(form.pickup_lng));
        if (form.dropoff_location) fd.append('dropoff_location', form.dropoff_location);
        if (form.dropoff_lat != null) fd.append('dropoff_lat', String(form.dropoff_lat));
        if (form.dropoff_lng != null) fd.append('dropoff_lng', String(form.dropoff_lng));
        if (details) fd.append('details', details);
        if (form.price_offer != null) fd.append('price_offer', String(form.price_offer));

        if (form.type === 'parcel') {
            if (form.parcel_length_cm != null) fd.append('parcel_length_cm', String(form.parcel_length_cm));
            if (form.parcel_width_cm != null) fd.append('parcel_width_cm', String(form.parcel_width_cm));
            if (form.parcel_height_cm != null) fd.append('parcel_height_cm', String(form.parcel_height_cm));
            if (form.parcel_weight_kg != null) fd.append('parcel_weight_kg', String(form.parcel_weight_kg));
            if (parcelPhotoFile.value) fd.append('parcel_photo', parcelPhotoFile.value);
        }

        await api.post('/trip-requests', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        feedStore.reset();
        feedStore.loadNextPage();

        form.type = '';
        form.pickup_location = '';
        form.pickup_lat = null;
        form.pickup_lng = null;
        form.dropoff_location = '';
        form.dropoff_lat = null;
        form.dropoff_lng = null;
        form.details = '';
        form.price_offer = null;
        form.parcel_length_cm = null;
        form.parcel_width_cm = null;
        form.parcel_height_cm = null;
        form.parcel_weight_kg = null;
        extra.passengers = null;
        extra.restaurant = '';
        parcelPhotoFile.value = null;
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not post request.';
    } finally {
        loading.value = false;
    }
}
</script>

