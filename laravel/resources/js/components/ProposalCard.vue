<script setup>
import { computed } from 'vue';

const props = defineProps({
    proposal: {
        type: Object,
        required: true,
    },
});

const seller = computed(() => props.proposal.seller || {});
const reviewCount = computed(() => props.proposal.buyer_reviews?.length || 0);
const averageRating = computed(() => {
    const reviews = props.proposal.buyer_reviews || [];
    if (reviews.length === 0) return 0;
    const sum = reviews.reduce((acc, r) => acc + (r.buyer_rating || 0), 0);
    return (sum / reviews.length).toFixed(1);
});

const proposalUrl = computed(() => {
    return `/proposals/${seller.value.seller_user_name}/${props.proposal.proposal_url}`;
});
</script>

<template>
    <div class="bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
        <!-- Proposal Image -->
        <router-link :to="proposalUrl">
            <div class="aspect-video bg-gray-200 overflow-hidden">
                <img
                    v-if="proposal.proposal_image"
                    :src="proposal.proposal_image"
                    :alt="proposal.proposal_title"
                    class="w-full h-full object-cover"
                    loading="lazy"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </router-link>

        <div class="p-4">
            <!-- Seller Info -->
            <div class="flex items-center mb-2">
                <img
                    v-if="seller.seller_image"
                    :src="seller.seller_image"
                    :alt="seller.seller_user_name"
                    class="w-8 h-8 rounded-full mr-2"
                />
                <div v-else class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-2 text-sm font-bold">
                    {{ (seller.seller_user_name || '?')[0].toUpperCase() }}
                </div>
                <router-link :to="`/${seller.seller_user_name}`" class="text-sm font-medium text-gray-700 hover:text-green-600">
                    {{ seller.seller_user_name }}
                </router-link>
            </div>

            <!-- Proposal Title -->
            <router-link :to="proposalUrl" class="block text-sm text-gray-800 hover:text-green-600 line-clamp-2 mb-2">
                {{ proposal.proposal_title }}
            </router-link>

            <!-- Rating -->
            <div class="flex items-center mb-2">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-sm text-yellow-500 font-medium ml-1">{{ averageRating }}</span>
                <span class="text-xs text-gray-400 ml-1">({{ reviewCount }})</span>
            </div>

            <!-- Price -->
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Starting at</span>
                <span class="text-lg font-bold text-gray-900">${{ proposal.proposal_price }}</span>
            </div>
        </div>
    </div>
</template>
