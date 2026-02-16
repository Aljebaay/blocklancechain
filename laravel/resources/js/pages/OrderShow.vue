<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const order = ref(null);
const loading = ref(true);
const error = ref(null);

const orderId = computed(() => route.params.orderId);

async function fetchOrder() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/orders/${orderId.value}`);
        order.value = data.data;
    } catch (e) {
        if (e.response?.status === 403) {
            router.push({ name: 'login', query: { redirect: route.fullPath } });
            return;
        }
        if (e.response?.status === 404) {
            error.value = 'Order not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load order';
        }
        order.value = null;
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function statusClass(status) {
    const map = {
        active: 'bg-blue-100 text-blue-800',
        delivered: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
}

onMounted(fetchOrder);
</script>

<template>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <router-link to="/orders/buying" class="text-gray-500 hover:text-green-600 mb-6 inline-block">← Back to Orders</router-link>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
        </div>

        <template v-else-if="order">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Order #{{ order.order_number || order.order_id }}</h1>
                            <p class="text-gray-500 mt-1">{{ formatDate(order.order_date) }}</p>
                        </div>
                        <span :class="['px-4 py-2 rounded-lg font-medium', statusClass(order.order_status)]">
                            {{ order.order_status }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h2 class="font-semibold text-gray-800 mb-2">Service</h2>
                        <p class="text-gray-900">{{ order.proposal?.proposal_title || 'N/A' }}</p>
                        <p class="text-sm text-gray-500 mt-1">Seller: {{ order.seller?.seller_user_name || '-' }}</p>
                    </div>

                    <div>
                        <h2 class="font-semibold text-gray-800 mb-2">Order Details</h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Amount</dt>
                                <dd class="font-medium">${{ order.order_amount }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Quantity</dt>
                                <dd class="font-medium">{{ order.order_quantity || 1 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Delivery Date</dt>
                                <dd class="font-medium">{{ formatDate(order.order_delivery_date) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Buyer</dt>
                                <dd class="font-medium">{{ order.buyer?.seller_user_name || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="order.order_note" class="border-t pt-6">
                        <h2 class="font-semibold text-gray-800 mb-2">Order Note</h2>
                        <p class="text-gray-600">{{ order.order_note }}</p>
                    </div>

                    <div v-if="order.messages?.length" class="border-t pt-6">
                        <h2 class="font-semibold text-gray-800 mb-4">Messages</h2>
                        <div class="space-y-4">
                            <div
                                v-for="(msg, i) in order.messages"
                                :key="i"
                                class="bg-gray-50 rounded-lg p-4"
                            >
                                <p class="text-sm text-gray-500">{{ msg.sender?.seller_user_name || 'Unknown' }} · {{ formatDate(msg.date || msg.order_message_date) }}</p>
                                <p class="mt-2 text-gray-800">{{ msg.message || msg.order_message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
