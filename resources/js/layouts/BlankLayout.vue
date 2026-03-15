<template>
    <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
        <header class="shrink-0 h-14 flex items-center px-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 safe-area-top">
            <RouterLink to="/" class="text-emerald-600 dark:text-emerald-400 font-semibold">← MotoPeer</RouterLink>
        </header>
        <main class="flex-1 min-h-0 flex flex-col">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
