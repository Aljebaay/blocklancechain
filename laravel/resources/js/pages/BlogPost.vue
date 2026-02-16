<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const post = ref(null);
const loading = ref(true);
const error = ref(null);

const postId = computed(() => route.params.id);

async function fetchPost() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(`/api/v1/blog/${postId.value}`);
        post.value = data.data;
    } catch (e) {
        if (e.response?.status === 404) {
            error.value = 'Post not found';
        } else {
            error.value = e.response?.data?.error || 'Failed to load post';
        }
        post.value = null;
    } finally {
        loading.value = false;
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

onMounted(fetchPost);
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg max-w-2xl">
            {{ error }}
        </div>

        <article v-else-if="post" class="max-w-3xl mx-auto">
            <nav class="text-sm text-gray-500 mb-4">
                <router-link to="/blog" class="hover:text-green-600">Blog</router-link>
                <span class="mx-2">/</span>
                <span class="text-gray-800">{{ post.blog_title }}</span>
            </nav>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ post.blog_title }}</h1>
            <p class="text-gray-500 mb-6">
                {{ formatDate(post.blog_date) }}
                <span v-if="post.blog_author"> · {{ post.blog_author }}</span>
            </p>

            <div v-if="post.blog_image" class="mb-8 rounded-lg overflow-hidden">
                <img :src="post.blog_image" :alt="post.blog_title" class="w-full h-auto" />
            </div>

            <div class="prose prose-lg max-w-none text-gray-700" v-html="post.blog_content"></div>
        </article>
    </div>
</template>
