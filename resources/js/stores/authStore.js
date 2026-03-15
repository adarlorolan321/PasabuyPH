import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/api/axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('token') || null);

    const isAuthenticated = computed(() => !!token.value);

    function setAuth(userData, authToken) {
        user.value = userData;
        token.value = authToken;
        if (authToken) {
            localStorage.setItem('token', authToken);
        } else {
            localStorage.removeItem('token');
        }
    }

    async function fetchUser() {
        if (!token.value) return;
        try {
            const { data } = await api.get('/auth/user');
            user.value = data.user;
            return data.user;
        } catch {
            clearAuth();
        }
    }

    async function login(email, password) {
        const { data } = await api.post('/auth/login', { email, password });
        setAuth(data.user, data.token);
        return data;
    }

    async function register(payload) {
        const { data } = await api.post('/auth/register', payload);
        setAuth(data.user, data.token);
        return data;
    }

    async function logout() {
        try {
            await api.post('/auth/logout');
        } finally {
            clearAuth();
        }
    }

    function clearAuth() {
        user.value = null;
        token.value = null;
        localStorage.removeItem('token');
    }

    return {
        user,
        token,
        isAuthenticated,
        setAuth,
        fetchUser,
        login,
        register,
        logout,
        clearAuth,
    };
});
