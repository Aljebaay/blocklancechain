<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const proposal = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref(null);

const proposalId = computed(() => route.params.proposalId);

const form = ref({
    proposal_title: '',
    proposal_desc: '',
    proposal_price: '',
    proposal_delivery_time: '',
    proposal_tags: '',
});

async function fetchProposal() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/proposals/${proposalId.value}`);
        proposal.value = data.data;
        form.value = {
            proposal_title: data.data.proposal_title,
            proposal_desc: data.data.proposal_desc,
            proposal_price: data.data.proposal_price,
            proposal_delivery_time: data.data.proposal_delivery_time,
            proposal_tags: data.data.proposal_tags || '',
        };
    } catch (e) {
        if (e.response?.status === 404) {
            error.value = 'Proposal not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load proposal';
        }
        proposal.value = null;
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    saving.value = true;
    error.value = null;
    try {
        // Stub: API endpoint for updating proposals would go here
        // await axios.put(`/api/v1/proposals/${proposalId.value}`, form.value);
        console.log('Update proposal (stub):', form.value);
        router.push({ name: 'proposals.my' });
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to update proposal';
    } finally {
        saving.value = false;
    }
}

onMounted(fetchProposal);
</script>

<template>
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Edit Proposal</h1>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error && !proposal" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
        </div>

        <form v-else-if="proposal" @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ error }}
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input
                    v-model="form.proposal_title"
                    type="text"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    v-model="form.proposal_desc"
                    rows="6"
                    required
                    class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
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
                />
            </div>

            <div class="flex gap-4">
                <button
                    type="submit"
                    :disabled="saving"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50"
                >
                    {{ saving ? 'Saving...' : 'Save Changes' }}
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
