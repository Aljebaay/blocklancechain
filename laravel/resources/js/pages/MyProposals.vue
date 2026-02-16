<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import ProposalCard from '@/components/ProposalCard.vue';

const router = useRouter();
const proposals = ref([]);
const loading = ref(true);
const error = ref(null);

async function fetchProposals() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/proposals/my');
        proposals.value = data.data || [];
    } catch (e) {
        if (e.response?.status === 403) {
            router.push({ name: 'login', query: { redirect: '/proposals/view' } });
            return;
        }
        error.value = e.response?.data?.error || 'Failed to load proposals';
        proposals.value = [];
    } finally {
        loading.value = false;
    }
}

function editUrl(proposal) {
    return `/proposals/edit/${proposal.proposal_id}`;
}

onMounted(fetchProposals);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900">My Proposals</h1>
            <router-link
                to="/proposals/create"
                class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700"
            >
                Create Proposal
            </router-link>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="proposals.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500 mb-4">You haven't created any proposals yet.</p>
                <router-link
                    to="/proposals/create"
                    class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700"
                >
                    Create Your First Proposal
                </router-link>
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="proposal in proposals" :key="proposal.proposal_id" class="relative group">
                    <ProposalCard :proposal="proposal" />
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        <router-link
                            :to="editUrl(proposal)"
                            class="bg-white/90 px-3 py-1 rounded text-sm font-medium text-gray-700 hover:bg-white shadow"
                        >
                            Edit
                        </router-link>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
