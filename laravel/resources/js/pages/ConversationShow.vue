<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const route = useRoute();
const authStore = useAuthStore();

const conversation = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(true);
const sending = ref(false);
const error = ref(null);
const messagesEnd = ref(null);

const conversationId = () => route.params.conversationId;
const currentUserId = () => authStore.user?.id ?? authStore.user?.seller_id;

function otherParticipant() {
    const c = conversation.value;
    if (!c) return null;
    const isSender = c.sender_id === currentUserId();
    return isSender ? c.receiver : c.sender;
}

function formatTime(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) return 'Today';
    return d.toLocaleDateString();
}

async function fetchConversation() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/conversations/${conversationId()}`);
        conversation.value = data.data;
        messages.value = data.data?.messages || [];
        scrollToBottom();
    } catch (e) {
        if (e.response?.status === 403) {
            error.value = 'Access denied';
        } else if (e.response?.status === 404) {
            error.value = 'Conversation not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load conversation';
        }
        conversation.value = null;
        messages.value = [];
    } finally {
        loading.value = false;
    }
}

async function sendMessage() {
    const msg = newMessage.value?.trim();
    if (!msg || sending.value) return;

    sending.value = true;
    error.value = null;
    try {
        const { data } = await axios.post(`/api/v1/conversations/${conversationId()}/message`, { message: msg });
        messages.value.push(data.data);
        newMessage.value = '';
        nextTick(scrollToBottom);
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to send message';
    } finally {
        sending.value = false;
    }
}

function scrollToBottom() {
    messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
}

onMounted(fetchConversation);
watch(conversationId, fetchConversation);
</script>

<template>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
        </div>

        <template v-else-if="conversation">
            <div class="flex items-center gap-4 mb-6">
                <router-link to="/inbox" class="text-gray-500 hover:text-green-600">← Back to Inbox</router-link>
                <div class="flex-1"></div>
                <div class="flex items-center gap-3">
                    <img
                        v-if="otherParticipant()?.seller_image"
                        :src="otherParticipant()?.seller_image"
                        :alt="otherParticipant()?.seller_user_name"
                        class="w-10 h-10 rounded-full"
                    />
                    <div v-else class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">
                        {{ (otherParticipant()?.seller_user_name || '?')[0].toUpperCase() }}
                    </div>
                    <span class="font-medium">{{ otherParticipant()?.seller_user_name || 'Unknown' }}</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow flex flex-col" style="height: 500px;">
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div
                        v-for="(msg, i) in messages"
                        :key="i"
                        :class="[
                            'flex',
                            msg.sender_id === currentUserId() ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[70%] rounded-lg px-4 py-2',
                                msg.sender_id === currentUserId()
                                    ? 'bg-green-600 text-white'
                                    : 'bg-gray-100 text-gray-800',
                            ]"
                        >
                            <p class="text-sm">{{ msg.message }}</p>
                            <p class="text-xs opacity-75 mt-1">{{ formatTime(msg.date) }}</p>
                        </div>
                    </div>
                    <div ref="messagesEnd"></div>
                </div>

                <form @submit.prevent="sendMessage" class="p-4 border-t">
                    <div class="flex gap-2">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Type a message..."
                            class="flex-1 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                        />
                        <button
                            type="submit"
                            :disabled="sending || !newMessage?.trim()"
                            class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</template>
