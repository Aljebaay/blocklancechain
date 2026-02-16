<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const page = ref(null);
const loading = ref(true);
const error = ref(null);

const slug = computed(() => route.params.slug);

async function fetchPage() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/pages/${slug.value}`);
        page.value = data.data;
    } catch (e) {
        if (e.response?.status === 404) {
            error.value = 'Page not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load page';
        }
        page.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchPage);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-2xl">
            {{ error }}
        </div>

        <article v-else-if="page" class="max-w-3xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <router-link to="/" class="hover:text-green-600">Home</router-link>
                <span class="mx-2">/</span>
                <span class="text-gray-800">{{ page.page_title }}</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ page.page_title }}</h1>

            <div class="prose prose-lg max-w-none text-gray-700" v-html="page.page_content"></div>
        </article>
    </div>
</template>
