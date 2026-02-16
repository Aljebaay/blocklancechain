<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import ProposalCard from '@/components/ProposalCard.vue';

const route = useRoute();
const user = ref(null);
const loading = ref(true);
const error = ref(null);

const username = computed(() => route.params.username);
const proposals = computed(() => user.value?.proposals || []);
const averageRating = computed(() => {
    const u = user.value;
    if (!u?.buyer_reviews?.length) return 0;
    const sum = u.buyer_reviews.reduce((acc, r) => acc + (r.buyer_rating || 0), 0);
    return (sum / u.buyer_reviews.length).toFixed(1);
});

async function fetchProfile() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/users/${username.value}`);
        user.value = data.data;
    } catch (e) {
        if (e.response?.status === 404) {
            error.value = 'User not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load profile';
        }
        user.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchProfile);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-2xl">
            {{ error }}
        </div>

        <template v-else-if="user">
            <!-- Profile header -->
            <div class="bg-white rounded-lg shadow p-8 mb-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <img
                        v-if="user.seller_image"
                        :src="user.seller_image"
                        :alt="user.seller_user_name"
                        class="w-24 h-24 rounded-full object-cover"
                    />
                    <div v-else class="w-24 h-24 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl font-bold">
                        {{ (user.seller_user_name || '?')[0].toUpperCase() }}
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900">{{ user.seller_user_name }}</h1>
                        <p v-if="user.seller_headline" class="text-gray-600 mt-1">{{ user.seller_headline }}</p>
                        <p v-if="user.seller_about" class="text-gray-500 mt-2 text-sm">{{ user.seller_about }}</p>
                        <div class="flex items-center gap-4 mt-3">
                            <span v-if="user.seller_country" class="text-sm text-gray-500">{{ user.seller_country }}</span>
                            <span v-if="user.buyer_reviews?.length" class="text-sm">
                                ★ {{ averageRating }} ({{ user.buyer_reviews.length }} reviews)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proposals -->
            <h2 class="text-xl font-bold text-gray-900 mb-6">Services</h2>
            <div v-if="proposals.length === 0" class="text-gray-500 py-12 text-center bg-white rounded-lg shadow">
                No services yet.
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <ProposalCard
                    v-for="proposal in proposals"
                    :key="proposal.proposal_id"
                    :proposal="proposal"
                />
            </div>
        </template>
    </div>
</template>
