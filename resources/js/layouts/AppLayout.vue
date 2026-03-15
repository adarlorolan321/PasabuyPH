<template>
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <nav class="container mx-auto px-4 py-3 flex items-center justify-between">
                <RouterLink to="/" class="text-xl font-semibold text-indigo-600 dark:text-indigo-400">
                    Rolan
                </RouterLink>
                <div class="flex items-center gap-4">
                    <template v-if="authStore.isAuthenticated">
                        <RouterLink
                            to="/"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                            active-class="text-indigo-600 dark:text-indigo-400 font-medium"
                        >
                            Home
                        </RouterLink>
                        <RouterLink
                            to="/about"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                            active-class="text-indigo-600 dark:text-indigo-400 font-medium"
                        >
                            About
                        </RouterLink>
                        <span class="text-gray-500 dark:text-gray-400 text-sm">
                            {{ authStore.user?.name ?? authStore.user?.email }}
                        </span>
                        <button
                            type="button"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition text-sm font-medium"
                            @click="handleLogout"
                        >
                            Log out
                        </button>
                    </template>
                    <template v-else>
                        <RouterLink
                            to="/"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                            active-class="text-indigo-600 dark:text-indigo-400 font-medium"
                        >
                            Home
                        </RouterLink>
                        <RouterLink
                            to="/about"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                            active-class="text-indigo-600 dark:text-indigo-400 font-medium"
                        >
                            About
                        </RouterLink>
                        <RouterLink
                            to="/login"
                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                            active-class="text-indigo-600 dark:text-indigo-400 font-medium"
                        >
                            Log in
                        </RouterLink>
                        <RouterLink
                            to="/register"
                            class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition"
                        >
                            Register
                        </RouterLink>
                    </template>
                </div>
            </nav>
        </header>
        <main class="flex-1 container mx-auto px-4 py-8">
            <slot />
        </main>
        <footer class="border-t border-gray-200 dark:border-gray-700 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            Laravel + Vue 3 + Vite
        </footer>
    </div>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

async function handleLogout() {
    await authStore.logout();
    router.push({ name: 'login' });
}
</script>
