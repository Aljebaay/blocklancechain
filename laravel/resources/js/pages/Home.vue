<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import ProposalCard from '@/components/ProposalCard.vue';

const authStore = useAuthStore();
const isAuthenticated = computed(() => authStore.isAuthenticated);

const categories = ref([]);
const featuredProposals = ref([]);
const loading = ref(true);
const heroSearch = ref('');

onMounted(async () => {
    try {
        const [catRes, proposalRes] = await Promise.all([
            axios.get('/api/v1/categories'),
            axios.get('/api/v1/proposals', { params: { type: 'featured', per_page: 8 } }),
        ]);
        categories.value = catRes.data.data;
        featuredProposals.value = proposalRes.data.data;
    } catch (e) {
        console.error('Failed to load home data:', e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <!-- Hero Section -->
        <section v-if="!isAuthenticated" class="bg-gradient-to-r from-green-600 to-green-800 text-white py-20">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Find the perfect freelance services for your business
                </h1>
                <p class="text-lg mb-8 opacity-90">
                    A fast growing freelance marketplace where sellers provide their services at affordable prices.
                </p>
                <div class="max-w-xl mx-auto">
                    <form @submit.prevent="$router.push({ name: 'search', query: { search: heroSearch } })">
                        <div class="flex">
                            <input
                                v-model="heroSearch"
                                type="text"
                                placeholder="Try 'Logo Design'"
                                class="flex-1 px-6 py-4 rounded-l-lg text-gray-800 focus:outline-none"
                            />
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-4 rounded-r-lg font-semibold">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- User Dashboard (for authenticated users) -->
        <section v-if="isAuthenticated" class="py-8 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-6">Welcome back, {{ authStore.displayName }}!</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <router-link to="/orders/buying" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-700">Buying Orders</h3>
                        <p class="text-sm text-gray-500 mt-1">View your purchases</p>
                    </router-link>
                    <router-link to="/orders/selling" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-700">Selling Orders</h3>
                        <p class="text-sm text-gray-500 mt-1">Manage your orders</p>
                    </router-link>
                    <router-link to="/proposals/view" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-700">My Proposals</h3>
                        <p class="text-sm text-gray-500 mt-1">Manage your services</p>
                    </router-link>
                    <router-link to="/inbox" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-700">Inbox</h3>
                        <p class="text-sm text-gray-500 mt-1">View messages</p>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-8 text-center">Popular Categories</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <router-link
                        v-for="category in categories"
                        :key="category.id"
                        :to="`/categories/${category.cat_url}`"
                        class="bg-white border rounded-lg p-4 text-center hover:shadow-md transition"
                    >
                        <div v-if="category.cat_icon" class="text-3xl mb-2" v-html="category.cat_icon"></div>
                        <h3 class="text-sm font-medium text-gray-700">{{ category.cat_title }}</h3>
                    </router-link>
                </div>
            </div>
        </section>

        <!-- Featured Proposals Section -->
        <section v-if="featuredProposals.length > 0" class="py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold mb-8 text-center">Featured Services</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <ProposalCard
                        v-for="proposal in featuredProposals"
                        :key="proposal.proposal_id"
                        :proposal="proposal"
                    />
                </div>
            </div>
        </section>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>
    </div>
</template>
