<template>
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-6">Register</h1>
        <form
            class="space-y-4"
            @submit.prevent="handleSubmit"
        >
            <div v-if="error" class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
                {{ error }}
            </div>
            <div>
                <label for="register-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Name
                </label>
                <input
                    id="register-name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    :disabled="loading"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
            </div>
            <div>
                <label for="register-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email
                </label>
                <input
                    id="register-email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    :disabled="loading"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.email }}</p>
            </div>
            <div>
                <label for="register-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Password
                </label>
                <input
                    id="register-password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    :disabled="loading"
                />
                <p v-if="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password }}</p>
            </div>
            <div>
                <label for="register-password-confirm" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Confirm password
                </label>
                <input
                    id="register-password-confirm"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    :disabled="loading"
                />
                <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password_confirmation }}</p>
            </div>
            <button
                type="submit"
                class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                :disabled="loading"
            >
                {{ loading ? 'Creating account…' : 'Create account' }}
            </button>
        </form>
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Already have an account?
            <RouterLink to="/login" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                Log in
            </RouterLink>
        </p>
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
const errors = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function setErrors(errs) {
    errors.name = '';
    errors.email = '';
    errors.password = '';
    errors.password_confirmation = '';
    if (errs) {
        if (Array.isArray(errs.name)) errors.name = errs.name[0];
        if (Array.isArray(errs.email)) errors.email = errs.email[0];
        if (Array.isArray(errs.password)) errors.password = errs.password[0];
        if (Array.isArray(errs.password_confirmation)) errors.password_confirmation = errs.password_confirmation[0];
    }
}

async function handleSubmit() {
    error.value = '';
    setErrors(null);
    loading.value = true;
    try {
        await authStore.register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });
        router.replace('/');
    } catch (e) {
        const res = e.response?.data;
        if (res?.errors) setErrors(res.errors);
        else if (res?.message) error.value = res.message;
        else error.value = 'Registration failed. Please try again.';
    } finally {
        loading.value = false;
    }
}
</script>
