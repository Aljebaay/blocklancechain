<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const proposal = ref(null);
const loading = ref(true);
const error = ref(null);

const username = computed(() => route.params.username);
const proposalUrl = computed(() => route.params.proposalUrl);

const seller = computed(() => proposal.value?.seller || {});
const reviews = computed(() => proposal.value?.buyer_reviews || []);
const packages = computed(() => proposal.value?.packages || []);
const faqs = computed(() => proposal.value?.faqs || []);
const gallery = computed(() => proposal.value?.gallery || []);

const averageRating = computed(() => {
    const revs = reviews.value;
    if (!revs.length) return 0;
    const sum = revs.reduce((acc, r) => acc + (r.buyer_rating || 0), 0);
    return (sum / revs.length).toFixed(1);
});

async function fetchProposal() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/proposals/by-slug/${username.value}/${proposalUrl.value}`);
        proposal.value = data.data;
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

onMounted(fetchProposal);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-2xl">
            {{ error }}
        </div>

        <template v-else-if="proposal">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Main content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Gallery -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="aspect-video bg-gray-200">
                                <img
                                    v-if="proposal.proposal_image"
                                    :src="proposal.proposal_image"
                                    :alt="proposal.proposal_title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div v-if="gallery.length > 0" class="flex gap-2 p-4 overflow-x-auto">
                                <img
                                    v-for="(img, i) in gallery"
                                    :key="i"
                                    :src="img.image || img.gallery_image"
                                    :alt="`Gallery ${i + 1}`"
                                    class="w-20 h-20 object-cover rounded border"
                                />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">About This Service</h2>
                            <div class="prose max-w-none text-gray-700" v-html="proposal.proposal_desc"></div>
                        </div>

                        <!-- FAQs -->
                        <div v-if="faqs.length > 0" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">FAQ</h2>
                            <div class="space-y-4">
                                <div v-for="(faq, i) in faqs" :key="i" class="border-b border-gray-100 pb-4 last:border-0">
                                    <h3 class="font-medium text-gray-800">{{ faq.faq_question || faq.question }}</h3>
                                    <p class="text-gray-600 mt-1">{{ faq.faq_answer || faq.answer }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Reviews ({{ reviews.length }})</h2>
                            <div v-if="reviews.length === 0" class="text-gray-500">No reviews yet.</div>
                            <div v-else class="space-y-4">
                                <div v-for="(r, i) in reviews" :key="i" class="border-b border-gray-100 pb-4 last:border-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-medium">{{ r.buyer?.seller_user_name || 'Buyer' }}</span>
                                        <span class="text-yellow-500">★ {{ r.buyer_rating }}</span>
                                    </div>
                                    <p class="text-gray-600 text-sm">{{ r.buyer_review || r.review }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ proposal.proposal_title }}</h1>

                            <!-- Seller info -->
                            <router-link :to="`/${seller.seller_user_name}`" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 mb-4">
                                <img
                                    v-if="seller.seller_image"
                                    :src="seller.seller_image"
                                    :alt="seller.seller_user_name"
                                    class="w-12 h-12 rounded-full"
                                />
                                <div v-else class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">
                                    {{ (seller.seller_user_name || '?')[0].toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ seller.seller_user_name }}</p>
                                    <p v-if="seller.seller_headline" class="text-sm text-gray-500">{{ seller.seller_headline }}</p>
                                </div>
                            </router-link>

                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-yellow-500 font-medium">★ {{ averageRating }}</span>
                                <span class="text-gray-500 text-sm">({{ reviews.length }} reviews)</span>
                            </div>

                            <!-- Packages -->
                            <div v-if="packages.length > 0" class="space-y-3 mb-6">
                                <h3 class="font-semibold text-gray-800">Packages</h3>
                                <div
                                    v-for="(pkg, i) in packages"
                                    :key="i"
                                    class="border rounded-lg p-4 hover:border-green-500 transition"
                                >
                                    <p class="font-medium">{{ pkg.package_title || pkg.title }}</p>
                                    <p class="text-2xl font-bold text-green-600 mt-1">${{ pkg.package_price ?? pkg.price ?? proposal.proposal_price }}</p>
                                    <p v-if="pkg.package_desc || pkg.description" class="text-sm text-gray-500 mt-1">{{ pkg.package_desc || pkg.description }}</p>
                                </div>
                            </div>
                            <div v-else class="mb-6">
                                <p class="text-2xl font-bold text-green-600">Starting at ${{ proposal.proposal_price }}</p>
                            </div>

                            <button class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
