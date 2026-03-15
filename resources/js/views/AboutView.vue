<template>
    <div>
        <h1 class="text-3xl font-bold mb-4">About</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            This project uses Vue 3 (Composition API), Vue Router for routing, Pinia for state management,
            and Axios for API requests. The frontend is built with Vite and integrated with Laravel.
        </p>
        <RouterLink
            to="/"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        >
            Back to Home
        </RouterLink>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
