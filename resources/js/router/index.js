import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const routes = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/HomeView.vue'),
        meta: { title: 'Home' },
    },
    {
        path: '/about',
        name: 'about',
        component: () => import('@/views/AboutView.vue'),
        meta: { title: 'About' },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { title: 'Log in', guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/RegisterView.vue'),
        meta: { title: 'Register', guest: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, _from, next) => {
    document.title = to.meta.title ? `${to.meta.title} — Rolan` : 'Rolan';
    const authStore = useAuthStore();
    if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'home' });
        return;
    }
    next();
});

export default router;
