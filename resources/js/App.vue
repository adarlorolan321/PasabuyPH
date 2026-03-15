<template>
    <div id="app" class="min-h-screen bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
        <component :is="layoutComponent">
            <RouterView v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </RouterView>
        </component>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/layouts/AppLayout.vue';
import BlankLayout from '@/layouts/BlankLayout.vue';
import { useAuthStore } from '@/stores/authStore';

const route = useRoute();
const layoutComponent = computed(() => (route.meta.blankLayout ? BlankLayout : AppLayout));

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
