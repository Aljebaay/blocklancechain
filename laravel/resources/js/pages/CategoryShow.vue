<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ProposalCard from '@/components/ProposalCard.vue';

const route = useRoute();
const router = useRouter();

const category = ref(null);
const childCategory = ref(null);
const childCategories = ref([]);
const proposals = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 });
const loading = ref(true);
const error = ref(null);

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

const catUrl = computed(() => route.params.catUrl);
const catChildUrl = computed(() => route.params.catChildUrl);

function buildParams(page = 1) {
    const params = {
        type: 'category',
        category_id: category.value?.id,
        per_page: 12,
        page,
    };
    if (childCategory.value) params.sub_category_id = childCategory.value.id;
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;
    if (deliveryTime.value) params.delivery_time = deliveryTime.value;
    if (sellerLevel.value) params.seller_level = sellerLevel.value;
    return params;
}

async function fetchCategoryAndProposals() {
    loading.value = true;
    error.value = null;
    try {
        const { data: catData } = await axios.get('/api/v1/categories');
        const categories = catData.data || [];
        const found = categories.find((c) => c.cat_url === catUrl.value);
        if (!found) {
            error.value = 'Category not found';
            category.value = null;
            childCategories.value = [];
            childCategory.value = null;
            proposals.value = [];
            return;
        }
        category.value = found;
        childCategories.value = found.children || [];
        childCategory.value = catChildUrl.value
            ? childCategories.value.find((c) => c.child_cat_url === catChildUrl.value) || null
            : null;

        const { data: propData } = await axios.get('/api/v1/proposals', { params: buildParams(1) });
        proposals.value = propData.data;
        pagination.value = propData.pagination;
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load category';
        proposals.value = [];
    } finally {
        loading.value = false;
    }
}

async function fetchProposals(page = 1) {
    if (!category.value) return;
    loading.value = true;
    try {
        const { data } = await axios.get('/api/v1/proposals', { params: buildParams(page) });
        proposals.value = data.data;
        pagination.value = data.pagination;
        router.replace({ query: { ...route.query, page }, params: route.params });
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load proposals';
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
    if (page >= 1 && page <= pagination.value.last_page) fetchProposals(page);
}

function childCategoryUrl(child) {
    return `/categories/${catUrl.value}/${child.child_cat_url}`;
}

onMounted(fetchCategoryAndProposals);

watch([catUrl, catChildUrl], () => {
    fetchCategoryAndProposals();
});
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="error && !category" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
        </div>

        <template v-else-if="category">
            <nav class="text-sm text-gray-500 mb-4">
                <router-link to="/categories" class="hover:text-green-600">Categories</router-link>
                <span class="mx-2">/</span>
                <span class="text-gray-800">{{ category.cat_title }}</span>
                <span v-if="childCategory" class="mx-2">/</span>
                <span v-if="childCategory" class="text-gray-800">{{ childCategory.child_cat_title }}</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ childCategory ? childCategory.child_cat_title : category.cat_title }}</h1>
            <p v-if="(childCategory ? childCategory.child_cat_desc : category.cat_desc)" class="text-gray-600 mb-8">
                {{ childCategory ? childCategory.child_cat_desc : category.cat_desc }}
            </p>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar: Sub-categories & Filters -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="space-y-6">
                        <div v-if="childCategories.length > 0" class="bg-white rounded-lg shadow p-4">
                            <h2 class="font-semibold text-gray-800 mb-3">Sub-categories</h2>
                            <ul class="space-y-1">
                                <li>
                                    <router-link
                                        :to="`/categories/${catUrl}`"
                                        :class="[
                                            'block py-2 px-3 rounded',
                                            !catChildUrl ? 'bg-green-50 text-green-700 font-medium' : 'hover:bg-gray-50 text-gray-700',
                                        ]"
                                    >
                                        All
                                    </router-link>
                                </li>
                                <li v-for="child in childCategories" :key="child.id">
                                    <router-link
                                        :to="childCategoryUrl(child)"
                                        :class="[
                                            'block py-2 px-3 rounded',
                                            childCategory?.id === child.id ? 'bg-green-50 text-green-700 font-medium' : 'hover:bg-gray-50 text-gray-700',
                                        ]"
                                    >
                                        {{ child.child_cat_title }}
                                    </router-link>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-white rounded-lg shadow p-4">
                            <h2 class="font-semibold text-gray-800 mb-4">Filters</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Range</label>
                                    <div class="flex gap-2">
                                        <input v-model="minPrice" type="number" placeholder="Min" min="0" class="w-full px-3 py-2 border rounded-lg text-sm" />
                                        <input v-model="maxPrice" type="number" placeholder="Max" min="0" class="w-full px-3 py-2 border rounded-lg text-sm" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                                    <select v-model="deliveryTime" class="w-full px-3 py-2 border rounded-lg text-sm">
                                        <option v-for="opt in deliveryOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seller Level</label>
                                    <select v-model="sellerLevel" class="w-full px-3 py-2 border rounded-lg text-sm">
                                        <option v-for="opt in sellerLevelOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="applyFilters" class="flex-1 bg-green-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-green-700">Apply</button>
                                    <button @click="clearFilters" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Proposals Grid -->
                <main class="flex-1 min-w-0">
                    <div v-if="loading" class="flex justify-center py-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
                    </div>
                    <template v-else>
                        <p v-if="error" class="text-red-600 mb-4">{{ error }}</p>
                        <p class="text-sm text-gray-500 mb-4">{{ pagination.total }} services</p>
                        <div v-if="proposals.length === 0" class="text-gray-500 py-12 text-center">No services in this category yet.</div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <ProposalCard v-for="proposal in proposals" :key="proposal.proposal_id" :proposal="proposal" />
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
                </main>
            </div>
        </template>
    </div>
</template>
