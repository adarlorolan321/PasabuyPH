<template>
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-4">About MotoPeer</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-6">
            Ride together. Deliver together. Post trips, find rides, or send parcels on existing routes.
            Built with Vue 3, Pinia, and Laravel.
        </p>
        <RouterLink
            to="/"
            class="inline-flex items-center px-5 py-3 min-h-[44px] text-base font-medium rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
        >
            ← Back to Home
        </RouterLink>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
