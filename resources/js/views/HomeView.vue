<template>
    <div>
        <h1 class="text-3xl font-bold mb-4">Welcome</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            This is a Vue 3 application with Vue Router, Pinia, and Axios, powered by Laravel and Vite.
        </p>
        <div class="flex flex-wrap gap-4">
            <RouterLink
                to="/about"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
            >
                Go to About
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
