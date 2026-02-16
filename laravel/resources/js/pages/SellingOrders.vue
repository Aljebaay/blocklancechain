<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const orders = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);
const error = ref(null);

const statusTabs = [
    { value: 'active', label: 'Active' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'all', label: 'All' },
];

const currentStatus = computed(() => route.params.status || 'active');

async function fetchOrders(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/orders/selling/${currentStatus.value}`, { params: { page } });
        orders.value = data.data || [];
        pagination.value = data.pagination || { current_page: 1, last_page: 1, total: 0 };
    } catch (e) {
        if (e.response?.status === 403) {
            router.push({ name: 'login', query: { redirect: route.fullPath } });
            return;
        }
        error.value = e.response?.data?.error || 'Failed to load orders';
        orders.value = [];
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) fetchOrders(page);
}

onMounted(() => fetchOrders(1));
watch(currentStatus, () => fetchOrders(1));
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Selling Orders</h1>

        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <router-link
                v-for="tab in statusTabs"
                :key="tab.value"
                :to="`/orders/selling/${tab.value}`"
                :class="[
                    'px-4 py-2 rounded-lg font-medium whitespace-nowrap',
                    currentStatus === tab.value
                        ? 'bg-green-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                ]"
            >
                {{ tab.label }}
            </router-link>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="orders.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500">No orders found.</p>
            </div>
            <div v-else class="space-y-4">
                <router-link
                    v-for="order in orders"
                    :key="order.order_id"
                    :to="`/orders/${order.order_id}`"
                    class="block bg-white rounded-lg shadow p-6 hover:shadow-md transition"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ order.proposal?.proposal_title || 'Order' }}</p>
                            <p class="text-sm text-gray-500 mt-1">Order #{{ order.order_number || order.order_id }}</p>
                            <p class="text-sm text-gray-500">Buyer: {{ order.buyer?.seller_user_name || '-' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span :class="['px-3 py-1 rounded-full text-sm font-medium', statusClass(order.order_status)]">
                                {{ order.order_status }}
                            </span>
                            <span class="font-bold text-gray-900">${{ order.order_amount }}</span>
                            <span class="text-sm text-gray-500">{{ formatDate(order.order_date) }}</span>
                        </div>
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
