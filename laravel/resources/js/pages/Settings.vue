<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const user = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(false);

const form = ref({
    seller_name: '',
    seller_headline: '',
    seller_about: '',
    seller_country: '',
    seller_phone: '',
    seller_language: '',
});

async function fetchSettings() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/settings/user');
        user.value = data.data;
        form.value = {
            seller_name: data.data?.seller_name || '',
            seller_headline: data.data?.seller_headline || '',
            seller_about: data.data?.seller_about || '',
            seller_country: data.data?.seller_country || '',
            seller_phone: data.data?.seller_phone || '',
            seller_language: data.data?.seller_language || '',
        };
    } catch (e) {
        if (e.response?.status === 403) {
            router.push({ name: 'login', query: { redirect: '/settings' } });
            return;
        }
        error.value = e.response?.data?.error || 'Failed to load settings';
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    error.value = null;
    success.value = false;
    try {
        await axios.post('/api/v1/settings/user', form.value);
        success.value = true;
    } catch (e) {
        error.value = e.response?.data?.error || e.response?.data?.message || 'Failed to save settings';
    } finally {
        saving.value = false;
    }
}

onMounted(fetchSettings);
</script>

<template>
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Settings</h1>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <form v-else @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ error }}
            </div>
            <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                Settings saved successfully.
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
                <input
                    v-model="form.seller_name"
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Your display name"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Headline</label>
                <input
                    v-model="form.seller_headline"
                    type="text"
                    maxlength="500"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Short tagline for your profile"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">About</label>
                <textarea
                    v-model="form.seller_about"
                    rows="4"
                    maxlength="5000"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Tell buyers about yourself..."
                ></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                <input
                    v-model="form.seller_country"
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Your country"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input
                    v-model="form.seller_phone"
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Phone number"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                <input
                    v-model="form.seller_language"
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Preferred language"
                />
            </div>

            <button
                type="submit"
                :disabled="saving"
                class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50"
            >
                {{ saving ? 'Saving...' : 'Save Settings' }}
            </button>
        </form>
    </div>
</template>
