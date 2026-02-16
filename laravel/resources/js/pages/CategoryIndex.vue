<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const categories = ref([]);
const loading = ref(true);
const error = ref(null);

async function fetchCategories() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/categories');
        categories.value = data.data || [];
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load categories';
        categories.value = [];
    } finally {
        loading.value = false;
    }
}

function categoryUrl(cat) {
    return `/categories/${cat.cat_url}`;
}

onMounted(fetchCategories);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Categories</h1>
        <p class="text-gray-600 mb-8">Explore services by category</p>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <router-link
                v-for="category in categories"
                :key="category.id"
                :to="categoryUrl(category)"
                class="bg-white border rounded-lg p-6 text-center hover:shadow-lg hover:border-green-500 transition group"
            >
                <div v-if="category.cat_icon" class="text-4xl mb-3 text-gray-600 group-hover:text-green-600" v-html="category.cat_icon"></div>
                <div v-else class="w-16 h-16 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="font-medium text-gray-800 group-hover:text-green-600">{{ category.cat_title }}</h3>
                <p v-if="category.cat_desc" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ category.cat_desc }}</p>
            </router-link>
        </div>

        <p v-if="!loading && categories.length === 0" class="text-gray-500 text-center py-12">
            No categories available.
        </p>
    </div>
</template>
