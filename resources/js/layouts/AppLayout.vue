<template>
    <div class="min-h-screen flex flex-col pb-20 md:pb-0">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 safe-area-top shadow-sm">
            <nav class="container mx-auto px-4 py-3 flex items-center justify-between">
                <RouterLink to="/" class="flex items-center gap-2 shrink-0">
                    <h1 class="text-xl font-bold text-emerald-600 dark:text-emerald-400">MotoPeer |
                        <span class="text-sm text-emerald-500">Ride & delivery</span>
                    </h1>
                </RouterLink>
                <div class="hidden md:flex items-center gap-2">
                    <template v-if="authStore.isAuthenticated">
                        <RouterLink to="/" class="px-4 py-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition">Home</RouterLink>
                        <RouterLink to="/chat" class="px-4 py-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition">Messages</RouterLink>
                        <RouterLink to="/trips/create" class="px-4 py-2 rounded-full bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 transition">Post trip</RouterLink>
                        <span class="text-slate-500 dark:text-slate-400 text-sm truncate max-w-[120px]">{{ authStore.user?.name ?? authStore.user?.email }}</span>
                        <button type="button" class="px-4 py-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition" @click="handleLogout">Log out</button>
                    </template>
                    <template v-else>
                        <RouterLink to="/login" class="px-4 py-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-medium transition">Log in</RouterLink>
                        <RouterLink to="/register" class="px-4 py-2 rounded-full bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 transition">Sign up</RouterLink>
                    </template>
                </div>
                <button
                    type="button"
                    class="md:hidden p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] min-w-[44px] flex items-center justify-center"
                    aria-label="Menu"
                    :aria-expanded="mobileMenuOpen"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>
            <!-- Mobile dropdown menu -->
            <div
                v-show="mobileMenuOpen"
                class="md:hidden absolute top-full left-0 right-0 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 shadow-lg"
            >
                <div class="flex flex-col px-4 py-3 gap-1">
                    <template v-if="authStore.isAuthenticated">
                        <RouterLink to="/" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium flex items-center gap-3" @click="mobileMenuOpen = false">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            Home
                        </RouterLink>
                        <RouterLink to="/trips/create" class="py-3 px-3 rounded-xl text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-3" @click="mobileMenuOpen = false">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Post trip
                        </RouterLink>
                        <RouterLink to="/chat" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium flex items-center gap-3" @click="mobileMenuOpen = false">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            Messages
                        </RouterLink>
                        <RouterLink to="/about" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium flex items-center gap-3" @click="mobileMenuOpen = false">About</RouterLink>
                        <button type="button" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium flex items-center gap-3 text-left w-full" @click="handleLogout">Log out</button>
                    </template>
                    <template v-else>
                        <RouterLink to="/" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium" @click="mobileMenuOpen = false">Home</RouterLink>
                        <RouterLink to="/about" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium" @click="mobileMenuOpen = false">About</RouterLink>
                        <RouterLink to="/login" class="py-3 px-3 rounded-xl text-slate-700 dark:text-slate-200 font-medium" @click="mobileMenuOpen = false">Log in</RouterLink>
                        <RouterLink to="/register" class="py-3 px-3 rounded-xl bg-emerald-500 text-white font-semibold text-center" @click="mobileMenuOpen = false">Sign up</RouterLink>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-1 container mx-auto px-4 py-6 w-full max-w-lg safe-area-bottom">
            <slot />
        </main>

        <!-- Bottom nav (mobile, when logged in) -->
        <nav v-if="authStore.isAuthenticated" class="md:hidden fixed bottom-0 left-0 right-0 z-20 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 safe-area-bottom">
            <div class="flex items-center justify-around py-2">
                <RouterLink to="/" class="flex flex-col items-center gap-1 py-2 px-6 rounded-xl min-h-[44px] justify-center text-slate-500 dark:text-slate-400 hover:text-emerald-500 transition" :class="{ '!text-emerald-500 font-semibold': route.name === 'home' }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    <span class="text-xs">Home</span>
                </RouterLink>
                <RouterLink to="/trips/create" class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl min-h-[44px] justify-center text-slate-500 dark:text-slate-400 hover:text-emerald-500 transition" :class="{ '!text-emerald-500 font-semibold': route.name === 'trips.create' }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="text-xs">Post</span>
                </RouterLink>
                <RouterLink to="/chat" class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl min-h-[44px] justify-center text-slate-500 dark:text-slate-400 hover:text-emerald-500 transition" :class="{ '!text-emerald-500 font-semibold': route.name === 'chat' }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    <span class="text-xs">Chat</span>
                </RouterLink>
                <RouterLink to="/about" class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl min-h-[44px] justify-center text-slate-500 dark:text-slate-400 hover:text-emerald-500 transition" :class="{ '!text-emerald-500 font-semibold': route.name === 'about' }">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    <span class="text-xs">About</span>
                </RouterLink>
            </div>
        </nav>

        <footer class="hidden md:block border-t border-slate-200 dark:border-slate-700 py-3 text-center text-xs text-slate-400 dark:text-slate-500">
            MotoPeer — Ride & delivery
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const mobileMenuOpen = ref(false);

async function handleLogout() {
    mobileMenuOpen.value = false;
    await authStore.logout();
    router.push({ name: 'login' });
}
</script>
