<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ProposalCard from '@/components/ProposalCard.vue';

const route = useRoute();
const router = useRouter();

const proposals = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 });
const loading = ref(true);
const error = ref(null);

// Filters
const searchQuery = ref(route.query.search || '');
const minPrice = ref(route.query.min_price || '');
const maxPrice = ref(route.query.max_price || '');
const deliveryTime = ref(route.query.delivery_time || '');
const sellerLevel = ref(route.query.seller_level || '');

const deliveryOptions = [
    { value: '', label: 'Any' },
    { value: 1, label: '24 hours' },
    { value: 3, label: '3 days' },
    { value: 7, label: '7 days' },
    { value: 14, label: '14 days' },
];

const sellerLevelOptions = [
    { value: '', label: 'Any' },
    { value: '1', label: 'Level 1' },
    { value: '2', label: 'Level 2' },
    { value: '3', label: 'Top Rated' },
];

const hasFilters = computed(() => minPrice.value || maxPrice.value || deliveryTime.value || sellerLevel.value);

function buildParams(page = 1) {
    const params = {
        type: 'search',
        search: searchQuery.value,
        per_page: pagination.value.per_page,
        page,
    };
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;
    if (deliveryTime.value) params.delivery_time = deliveryTime.value;
    if (sellerLevel.value) params.seller_level = sellerLevel.value;
    return params;
}

async function fetchProposals(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/proposals', { params: buildParams(page) });
        proposals.value = data.data;
        pagination.value = data.pagination;
        router.replace({ query: { ...route.query, search: searchQuery.value, min_price: minPrice.value, max_price: maxPrice.value, delivery_time: deliveryTime.value, seller_level: sellerLevel.value, page: page.toString() } });
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load search results';
        proposals.value = [];
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    fetchProposals(1);
}

function clearFilters() {
    minPrice.value = '';
    maxPrice.value = '';
    deliveryTime.value = '';
    sellerLevel.value = '';
    fetchProposals(1);
}

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchProposals(page);
    }
}

onMounted(() => {
    searchQuery.value = route.query.search || '';
    minPrice.value = route.query.min_price || '';
    maxPrice.value = route.query.max_price || '';
    deliveryTime.value = route.query.delivery_time || '';
    sellerLevel.value = route.query.seller_level || '';
    fetchProposals(parseInt(route.query.page, 10) || 1);
});

watch(() => route.query.search, (val) => {
    searchQuery.value = val || '';
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Search Services</h1>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow p-4 sticky top-4">
                    <h2 class="font-semibold text-gray-800 mb-4">Filters</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="minPrice"
                                    type="number"
                                    placeholder="Min"
                                    min="0"
                                    step="5"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500"
                                />
                                <input
                                    v-model="maxPrice"
                                    type="number"
                                    placeholder="Max"
                                    min="0"
                                    step="5"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                            <select
                                v-model="deliveryTime"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500"
                            >
                                <option v-for="opt in deliveryOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seller Level</label>
                            <select
                                v-model="sellerLevel"
                                class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500"
                            >
                                <option v-for="opt in sellerLevelOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="applyFilters"
                                class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-700"
                            >
                                Apply
                            </button>
                            <button
                                v-if="hasFilters"
                                @click="clearFilters"
                                class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Results -->
            <main class="flex-1 min-w-0">
                <form @submit.prevent="applyFilters" class="mb-6">
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search services..."
                            class="flex-1 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                        />
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700">
                            Search
                        </button>
                    </div>
                </form>

                <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ error }}
                </div>

                <div v-if="loading" class="flex justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
                </div>

                <template v-else>
                    <p v-if="!searchQuery && proposals.length === 0" class="text-gray-500 py-8">
                        Enter a search term to find services.
                    </p>
                    <p v-else-if="proposals.length === 0" class="text-gray-500 py-8">
                        No services found. Try adjusting your search or filters.
                    </p>
                    <div v-else>
                        <p class="text-sm text-gray-500 mb-4">{{ pagination.total }} results found</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <ProposalCard
                                v-for="proposal in proposals"
                                :key="proposal.proposal_id"
                                :proposal="proposal"
                            />
                        </div>

                        <!-- Pagination -->
                        <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center gap-2">
                            <button
                                :disabled="pagination.current_page <= 1"
                                @click="goToPage(pagination.current_page - 1)"
                                class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                Previous
                            </button>
                            <span class="px-4 py-2 text-gray-600">
                                Page {{ pagination.current_page }} of {{ pagination.last_page }}
                            </span>
                            <button
                                :disabled="pagination.current_page >= pagination.last_page"
                                @click="goToPage(pagination.current_page + 1)"
                                class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </template>
            </main>
        </div>
    </div>
</template>
