import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Attach Bearer token from localStorage when present
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// On 401, clear token and redirect to login (router set after app mount)
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            const router = window.__ROLAN_ROUTER__;
            if (router && router.currentRoute.value?.name !== 'login') {
                router.replace({ name: 'login' });
            }
        }
        return Promise.reject(error);
    }
);

export default api;
