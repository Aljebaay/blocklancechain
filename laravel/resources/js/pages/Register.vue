<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    seller_user_name: '',
    seller_email: '',
    seller_pass: '',
    seller_name: '',
});
const errors = ref({});
const errorMessage = ref('');

const handleRegister = async () => {
    errors.value = {};
    errorMessage.value = '';

    const result = await authStore.register(form.value);

    if (result.success) {
        router.push('/');
    } else {
        if (result.errors) {
            errors.value = result.errors;
        }
        switch (result.error) {
            case 'username_taken':
                errorMessage.value = 'This username is already taken.';
                break;
            case 'email_taken':
                errorMessage.value = 'This email is already registered.';
                break;
            default:
                errorMessage.value = 'Registration failed. Please try again.';
        }
    }
};
</script>

<template>
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-center mb-6">Create an Account</h2>

            <div class="bg-white rounded-lg shadow p-8">
                <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    {{ errorMessage }}
                </div>

                <form @submit.prevent="handleRegister">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            v-model="form.seller_user_name"
                            type="text"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                        <p v-if="errors.seller_user_name" class="text-red-500 text-sm mt-1">{{ errors.seller_user_name[0] }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input
                            v-model="form.seller_name"
                            type="text"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            v-model="form.seller_email"
                            type="email"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        />
                        <p v-if="errors.seller_email" class="text-red-500 text-sm mt-1">{{ errors.seller_email[0] }}</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            v-model="form.seller_pass"
                            type="password"
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                            minlength="6"
                        />
                        <p v-if="errors.seller_pass" class="text-red-500 text-sm mt-1">{{ errors.seller_pass[0] }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="authStore.loading"
                        class="w-full bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600 disabled:opacity-50"
                    >
                        {{ authStore.loading ? 'Creating account...' : 'Join Now' }}
                    </button>
                </form>

                <div class="text-center mt-6 text-sm">
                    <router-link to="/login" class="text-green-600 hover:underline">
                        Already have an account? Sign in
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
