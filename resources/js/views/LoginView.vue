<template>
    <div class="max-w-md mx-auto">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Log in</h1>
            <form
                class="space-y-4"
                @submit.prevent="handleSubmit"
            >
                <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
                    {{ error }}
                </div>
                <div>
                    <label for="login-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Email
                    </label>
                    <input
                        id="login-email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.email }}</p>
                </div>
                <div>
                    <label for="login-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Password
                    </label>
                    <input
                        id="login-password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password }}</p>
                </div>
                <button
                    type="submit"
                    class="w-full py-3 px-4 text-base bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    :disabled="loading"
                >
                    {{ loading ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>
            <p class="mt-6 text-sm text-slate-600 dark:text-slate-400">
                Don't have an account?
                <RouterLink to="/register" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline py-2 inline-block">
                    Register
                </RouterLink>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref('');
const errors = reactive({ email: '', password: '' });

const form = reactive({
    email: '',
    password: '',
});

function setErrors(errs) {
    errors.email = '';
    errors.password = '';
    if (errs) {
        if (Array.isArray(errs.email)) errors.email = errs.email[0];
        if (Array.isArray(errs.password)) errors.password = errs.password[0];
    }
}

async function handleSubmit() {
    error.value = '';
    setErrors(null);
    loading.value = true;
    try {
        await authStore.login(form.email, form.password);
        router.replace('/');
    } catch (e) {
        const res = e.response?.data;
        if (res?.errors) setErrors(res.errors);
        else if (res?.message) error.value = res.message;
        else error.value = 'Sign in failed. Please try again.';
    } finally {
        loading.value = false;
    }
}
</script>
