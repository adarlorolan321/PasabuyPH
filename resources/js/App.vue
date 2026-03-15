<template>
    <div id="app" class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <AppLayout>
            <RouterView v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </RouterView>
        </AppLayout>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();

onMounted(() => {
    if (authStore.isAuthenticated) {
        authStore.fetchUser();
    }
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
