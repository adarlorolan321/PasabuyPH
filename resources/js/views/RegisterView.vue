<template>
    <div class="max-w-md mx-auto">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Create account</h1>
            <form
                class="space-y-4"
                @submit.prevent="handleSubmit"
            >
                <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
                    {{ error }}
                </div>
                <div>
                    <label for="register-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Name
                    </label>
                    <input
                        id="register-name"
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.name }}</p>
                </div>
                <div>
                    <label for="register-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Email
                    </label>
                    <input
                        id="register-email"
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
                    <label for="register-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Password
                    </label>
                    <input
                        id="register-password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password }}</p>
                </div>
                <div>
                    <label for="register-password-confirm" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Confirm password
                    </label>
                    <input
                        id="register-password-confirm"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="w-full px-4 py-3 text-base border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        :disabled="loading"
                    />
                    <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password_confirmation }}</p>
                </div>
                <button
                    type="submit"
                    class="w-full py-3 px-4 text-base bg-emerald-500 text-white font-semibold rounded-xl hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    :disabled="loading"
                >
                    {{ loading ? 'Creating account…' : 'Create account' }}
                </button>
            </form>
            <p class="mt-6 text-sm text-slate-600 dark:text-slate-400">
                Already have an account?
                <RouterLink to="/login" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline py-2 inline-block">
                    Log in
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
