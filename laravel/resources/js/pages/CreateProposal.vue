<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const loading = ref(false);
const error = ref(null);

const form = ref({
    proposal_title: '',
    proposal_desc: '',
    proposal_price: '',
    proposal_delivery_time: '',
    proposal_tags: '',
});

async function submitForm() {
    loading.value = true;
    error.value = null;
    try {
        // Stub: API endpoint for creating proposals would go here
        // await axios.post('/api/v1/proposals', form.value);
        console.log('Create proposal (stub):', form.value);
        router.push({ name: 'proposals.my' });
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to create proposal';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {});
</script>

<template>
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Create Proposal</h1>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <form @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input
                    v-model="form.proposal_title"
                    type="text"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="e.g. I will design a professional logo"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    v-model="form.proposal_desc"
                    rows="6"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="Describe your service in detail..."
                ></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price ($)</label>
                <input
                    v-model="form.proposal_price"
                    type="number"
                    required
                    min="5"
                    step="0.01"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Time (days)</label>
                <input
                    v-model="form.proposal_delivery_time"
                    type="number"
                    required
                    min="1"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags (comma-separated)</label>
                <input
                    v-model="form.proposal_tags"
                    type="text"
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                    placeholder="logo, design, branding"
                />
            </div>

            <div class="flex gap-4">
                <button
                    type="submit"
                    :disabled="loading"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50"
                >
                    {{ loading ? 'Creating...' : 'Create Proposal' }}
                </button>
                <router-link
                    to="/proposals/view"
                    class="px-6 py-3 border rounded-lg text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </router-link>
            </div>
        </form>
    </div>
</template>
