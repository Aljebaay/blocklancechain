<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const conversations = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);
const error = ref(null);

async function fetchConversations(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/conversations', { params: { page } });
        conversations.value = data.data || [];
        pagination.value = data.pagination || { current_page: 1, last_page: 1, total: 0 };
    } catch (e) {
        if (e.response?.status === 403) {
            router.push({ name: 'login', query: { redirect: '/inbox' } });
            return;
        }
        error.value = e.response?.data?.error || 'Failed to load conversations';
        conversations.value = [];
    } finally {
        loading.value = false;
    }
}

function otherParticipant(conv) {
    const authStore = null; // Would use useAuthStore to get current user
    const sender = conv.sender;
    const receiver = conv.receiver;
    return receiver || sender;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    const diff = now - d;
    if (diff < 86400000) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    if (diff < 604800000) return d.toLocaleDateString([], { weekday: 'short' });
    return d.toLocaleDateString();
}

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) fetchConversations(page);
}

onMounted(() => fetchConversations(1));
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Inbox</h1>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="conversations.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500">No conversations yet.</p>
            </div>
            <div v-else class="bg-white rounded-lg shadow divide-y">
                <router-link
                    v-for="conv in conversations"
                    :key="conv.id"
                    :to="`/inbox/${conv.id}`"
                    class="flex items-center gap-4 p-4 hover:bg-gray-50 transition"
                >
                    <div class="flex-shrink-0">
                        <img
                            v-if="(conv.receiver || conv.sender)?.seller_image"
                            :src="(conv.receiver || conv.sender)?.seller_image"
                            :alt="(conv.receiver || conv.sender)?.seller_user_name"
                            class="w-12 h-12 rounded-full object-cover"
                        />
                        <div v-else class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">
                            {{ ((conv.receiver || conv.sender)?.seller_user_name || '?')[0].toUpperCase() }}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900">{{ (conv.receiver || conv.sender)?.seller_user_name || 'Unknown' }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ conv.subject || 'No subject' }}</p>
                    </div>
                    <div class="text-sm text-gray-500 flex-shrink-0">
                        {{ formatDate(conv.last_activity || conv.date) }}
                    </div>
                </router-link>
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
