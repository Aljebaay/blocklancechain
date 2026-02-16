<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const posts = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 10 });
const loading = ref(true);
const error = ref(null);

async function fetchPosts(page = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get('/api/v1/blog', { params: { page, per_page: 10 } });
        posts.value = data.data || [];
        pagination.value = data.pagination || { current_page: 1, last_page: 1, total: 0, per_page: 10 };
    } catch (e) {
        error.value = e.response?.data?.error || 'Failed to load blog posts';
        posts.value = [];
    } finally {
        loading.value = false;
    }
}

function postUrl(post) {
    return `/blog/${post.id}/${post.blog_slug || ''}`;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function goToPage(page) {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchPosts(page);
    }
}

onMounted(() => fetchPosts(1));
</script>

<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Blog</h1>
        <p class="text-gray-600 mb-8">Latest articles and updates</p>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-if="loading" class="flex justify-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>

        <template v-else>
            <div v-if="posts.length === 0" class="text-gray-500 py-12 text-center">
                No blog posts yet.
            </div>
            <div v-else class="space-y-8">
                <article
                    v-for="post in posts"
                    :key="post.id"
                    class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition"
                >
                    <router-link :to="postUrl(post)" class="block">
                        <div v-if="post.blog_image" class="aspect-video bg-gray-200">
                            <img :src="post.blog_image" :alt="post.blog_title" class="w-full h-full object-cover" />
                        </div>
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900 hover:text-green-600">{{ post.blog_title }}</h2>
                            <p class="text-sm text-gray-500 mt-2">
                                {{ formatDate(post.blog_date) }}
                                <span v-if="post.blog_author"> · {{ post.blog_author }}</span>
                            </p>
                            <p v-if="post.blog_content" class="text-gray-600 mt-3 line-clamp-3" v-html="post.blog_content"></p>
                        </div>
                    </router-link>
                </article>
            </div>

            <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center gap-2">
                <button
                    :disabled="pagination.current_page <= 1"
                    @click="goToPage(pagination.current_page - 1)"
                    class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Previous
                </button>
                <span class="px-4 py-2 text-gray-600">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="goToPage(pagination.current_page + 1)"
                    class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </template>
    </div>
</template>
