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

const tag = computed(() => route.params.tag);

async function fetchProposals(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/proposals', {
            params: { type: 'tag', tag: tag.value, per_page: 12, page },
        });
        proposals.value = data.data || [];
        pagination.value = data.pagination || { current_page: 1, last_page: 1, total: 0, per_page: 12 };
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load proposals';
        proposals.value = [];
    } finally {
        loading.value = false;
    }
}

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) fetchProposals(page);
}

onMounted(() => fetchProposals(parseInt(route.query.page, 10) || 1));
watch(tag, () => fetchProposals(1));
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Tag: {{ tag }}</h1>
        <p class="text-gray-600 mb-8">Services tagged with "{{ tag }}"</p>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="proposals.length === 0" class="text-gray-500 py-12 text-center">
                No services found for this tag.
            </div>
            <div v-else>
                <p class="text-sm text-gray-500 mb-4">{{ pagination.total }} results</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
            </div>
        </template>
    </div>
</template>
