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

            <div v-else-if="form.type === 'parcel'">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Item description</label>
                <textarea
                    v-model="form.details"
                    rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                ></textarea>
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

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Reward (₱)</label>
                <input
                    v-model.number="form.price_offer"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                />
            </div>

            <button
                type="submit"
                class="w-full py-2 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 disabled:opacity-50"
                :disabled="loading"
            >
                {{ loading ? 'Posting…' : 'Post request' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
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
});

const extra = reactive({
    passengers: null,
    restaurant: '',
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

async function handleSubmit() {
    error.value = '';
    loading.value = true;
    try {
        if (form.type === 'ride' && extra.passengers) {
            form.details = `Passengers: ${extra.passengers}${form.details ? ' - ' + form.details : ''}`;
        }
        if (form.type === 'food' && extra.restaurant) {
            form.details = `Restaurant: ${extra.restaurant}${form.details ? ' - ' + form.details : ''}`;
        }

        await api.post('/trip-requests', {
            type: form.type,
            pickup_location: form.pickup_location,
            pickup_lat: form.pickup_lat,
            pickup_lng: form.pickup_lng,
            dropoff_location: form.dropoff_location,
            dropoff_lat: form.dropoff_lat,
            dropoff_lng: form.dropoff_lng,
            details: form.details,
            price_offer: form.price_offer,
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
        extra.passengers = null;
        extra.restaurant = '';
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not post request.';
    } finally {
        loading.value = false;
    }
}
</script>

