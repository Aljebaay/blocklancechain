<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
    seller_user_name: '',
    seller_pass: '',
});
const errors = ref({});
const errorMessage = ref('');

const handleLogin = async () => {
    errors.value = {};
    errorMessage.value = '';

    const result = await authStore.login(form.value.seller_user_name, form.value.seller_pass);

    if (result.success) {
        const redirect = route.query.redirect || '/';
        router.push(redirect);
    } else {
        if (result.errors) {
            errors.value = result.errors;
        }
        switch (result.error) {
            case 'incorrect_login':
                errorMessage.value = 'Incorrect username or password.';
                break;
            case 'blocked':
                errorMessage.value = 'Your account has been blocked.';
                break;
            case 'deactivated':
                errorMessage.value = 'Your account has been deactivated.';
                break;
            default:
                errorMessage.value = 'Login failed. Please try again.';
        }
    }
};
</script>

<template>
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-center mb-6">Sign In</h2>

            <div class="bg-white rounded-lg shadow p-8">
                <div class="text-center mb-6">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <!-- Error Messages -->
                <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="handleLogin">
                    <div class="mb-4">
                        <input
                            v-model="form.seller_user_name"
                            type="text"
                            placeholder="Username or Email"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                        <p v-if="errors.seller_user_name" class="text-red-500 text-sm mt-1">{{ errors.seller_user_name[0] }}</p>
                    </div>

                    <div class="mb-4">
                        <input
                            v-model="form.seller_pass"
                            type="password"
                            placeholder="Password"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                        <p v-if="errors.seller_pass" class="text-red-500 text-sm mt-1">{{ errors.seller_pass[0] }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="authStore.loading"
                        class="w-full bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600 disabled:opacity-50"
                    >
                        {{ authStore.loading ? 'Signing in...' : 'Sign In' }}
                    </button>
                </form>

                <div class="text-center mt-6 text-sm">
                    <router-link to="/register" class="text-green-600 hover:underline">
                        Not registered? Create an account
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
