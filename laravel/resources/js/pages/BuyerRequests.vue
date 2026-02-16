<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const requests = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);
const error = ref(null);

async function fetchRequests(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/buyer-requests', { params: { page, per_page: 10 } });
        requests.value = data.data || [];
        pagination.value = data.pagination || { current_page: 1, last_page: 1, total: 0 };
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load buyer requests';
        requests.value = [];
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) fetchRequests(page);
}

onMounted(() => fetchRequests(1));
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Buyer Requests</h1>
        <p class="text-gray-600 mb-8">Browse requests from buyers looking for services</p>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="requests.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500">No buyer requests at the moment.</p>
            </div>
            <div v-else class="space-y-4">
                <div
                    v-for="req in requests"
                    :key="req.id"
                    class="bg-white rounded-lg shadow p-6 hover:shadow-md transition"
                >
                    <h3 class="font-semibold text-gray-900">{{ req.request_title }}</h3>
                    <p v-if="req.request_desc" class="text-gray-600 mt-2 text-sm line-clamp-2">{{ req.request_desc }}</p>
                    <div class="flex flex-wrap gap-4 mt-4 text-sm text-gray-500">
                        <span v-if="req.request_budget">Budget: ${{ req.request_budget }}</span>
                        <span v-if="req.request_delivery_time">Delivery: {{ req.request_delivery_time }} days</span>
                        <span v-if="req.category">Category: {{ req.category?.cat_title || '-' }}</span>
                        <span>{{ formatDate(req.request_date) }}</span>
                        <span v-if="req.buyer">By: {{ req.buyer?.seller_user_name || '-' }}</span>
                    </div>
                </div>
            </div>

            <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center gap-2">
                <button
                    :disabled="pagination.current_page <= 1"
                    @click="goToPage(pagination.current_page - 1)"
                    class="px-4 py-2 border rounded-lg disabled:opacity-50"
                >
                    Previous
                </button>
                <span class="px-4 py-2 text-gray-600">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="goToPage(pagination.current_page + 1)"
                    class="px-4 py-2 border rounded-lg disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </template>
    </div>
</template>
