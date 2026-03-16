<template>
    <div class="max-w-md mx-auto">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-slate-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-2">Post a trip</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Share your route. Others can join or send parcels.</p>
            </div>
            <form
                class="p-6 sm:p-8 space-y-4"
                @submit.prevent="handleSubmit"
            >
                <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
                    {{ error }}
                </div>
                <div class="pb-4 border-b border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pick on map (optional)</p>
                    <LocationPicker
                        @pickup-selected="onPickupSelected"
                        @dropoff-selected="onDropoffSelected"
                    />
                </div>
                <div>
                    <label for="trip-origin" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Origin
                    </label>
                    <input
                        id="trip-origin"
                        v-model="form.origin"
                        type="text"
                        required
                        placeholder="e.g. New York"
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.origin" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.origin }}</p>
                </div>
                <div>
                    <label for="trip-destination" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Destination
                    </label>
                    <input
                        id="trip-destination"
                        v-model="form.destination"
                        type="text"
                        required
                        placeholder="e.g. Boston"
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.destination" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.destination }}</p>
                </div>
                <div>
                    <label for="trip-departure-time" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Departure time
                    </label>
                    <input
                        id="trip-departure-time"
                        v-model="form.departure_time"
                        type="datetime-local"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.departure_time" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.departure_time }}</p>
                </div>
                <div>
                    <label for="trip-vehicle-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Vehicle type
                    </label>
                    <select
                        id="trip-vehicle-type"
                        v-model="form.vehicle_type"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    >
                        <option value="" disabled>Select vehicle type</option>
                        <option value="Car">Car</option>
                        <option value="Motorcycle">Motorcycle</option>
                        <option value="Van">Van</option>
                        <option value="Bus">Bus</option>
                        <option value="Bicycle">Bicycle</option>
                        <option value="Other">Other</option>
                    </select>
                    <p v-if="errors.vehicle_type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.vehicle_type }}</p>
                </div>
                <button
                    type="submit"
                    class="w-full py-3 px-4 text-base bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    :disabled="loading"
                >
                    {{ loading ? 'Posting…' : 'Post trip' }}
                </button>
            </form>
            <p class="px-6 sm:px-8 pb-6 text-sm text-slate-600 dark:text-slate-400">
                <RouterLink to="/" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline py-2 inline-block">
                    ← Back to home
                </RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { RouterLink } from 'vue-router';
import api from '@/api/axios';
import LocationPicker from '@/components/LocationPicker.vue';

const router = useRouter();

const loading = ref(false);
const error = ref('');
const errors = reactive({
    origin: '',
    destination: '',
    departure_time: '',
    vehicle_type: '',
});

const form = reactive({
    origin: '',
    origin_lat: null,
    origin_lng: null,
    destination: '',
    destination_lat: null,
    destination_lng: null,
    departure_time: '',
    vehicle_type: '',
});

function onPickupSelected(payload) {
    form.origin = payload.address;
    form.origin_lat = payload.lat;
    form.origin_lng = payload.lng;
}

function onDropoffSelected(payload) {
    form.destination = payload.address;
    form.destination_lat = payload.lat;
    form.destination_lng = payload.lng;
}

function setErrors(errs) {
    errors.origin = '';
    errors.destination = '';
    errors.departure_time = '';
    errors.vehicle_type = '';
    if (errs) {
        if (Array.isArray(errs.origin)) errors.origin = errs.origin[0];
        if (Array.isArray(errs.destination)) errors.destination = errs.destination[0];
        if (Array.isArray(errs.departure_time)) errors.departure_time = errs.departure_time[0];
        if (Array.isArray(errs.vehicle_type)) errors.vehicle_type = errs.vehicle_type[0];
    }
}

async function handleSubmit() {
    error.value = '';
    setErrors(null);
    loading.value = true;
    try {
        await api.post('/trips', {
            origin: form.origin,
            origin_lat: form.origin_lat,
            origin_lng: form.origin_lng,
            destination: form.destination,
            destination_lat: form.destination_lat,
            destination_lng: form.destination_lng,
            departure_time: form.departure_time,
            vehicle_type: form.vehicle_type,
        });
        router.push('/');
    } catch (e) {
        const res = e.response?.data;
        if (res?.errors) setErrors(res.errors);
        else if (res?.message) error.value = res.message;
        else error.value = 'Could not post trip. Please try again.';
    } finally {
        loading.value = false;
    }
}
</script>
