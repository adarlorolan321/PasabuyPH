<template>
    <div class="space-y-8">
        <!-- Hero -->
        <section class="text-center pt-4 pb-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-slate-100 mb-2">
                Where to?
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-base">
                Post a trip or find rides & deliveries near you.
            </p>
        </section>

        <!-- Action cards -->
        <section class="grid gap-4">
            <RouterLink
                v-if="authStore.isAuthenticated"
                to="/trips/create"
                class="block p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition text-left min-h-[44px] flex items-center gap-4"
            >
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </span>
                <div class="min-w-0">
                    <span class="font-semibold text-slate-800 dark:text-slate-100 block">Post a trip</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Share your route and time. Others can tag along or send parcels.</span>
                </div>
                <span class="shrink-0 text-slate-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </span>
            </RouterLink>
            <template v-else>
                <RouterLink
                    to="/register"
                    class="block p-5 rounded-2xl bg-emerald-500 text-white shadow-md hover:bg-emerald-600 hover:shadow-lg transition text-left min-h-[44px] flex items-center gap-4"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </span>
                    <div class="min-w-0">
                        <span class="font-semibold block">Post a trip</span>
                        <span class="text-sm text-white/90">Sign up to share your route and earn or save.</span>
                    </div>
                    <span class="shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </RouterLink>
            </template>

            <RouterLink
                to="/trips/find"
                class="block p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-emerald-200 dark:hover:border-emerald-800 transition text-left min-h-[44px] flex items-center gap-4"
            >
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                </span>
                <div class="min-w-0">
                    <span class="font-semibold text-slate-800 dark:text-slate-100 block">Find trips</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">Set pickup & dropoff on the map and find matching rides.</span>
                </div>
                <span class="shrink-0 text-slate-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </span>
            </RouterLink>
        </section>

        <!-- Promo strip -->
        <section class="rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white p-5 text-center">
            <p class="font-semibold text-lg mb-1">Ride together. Deliver together.</p>
            <p class="text-sm text-white/90">Match your trip with others or send parcels on existing routes.</p>
        </section>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
</script>
