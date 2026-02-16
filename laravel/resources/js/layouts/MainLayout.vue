<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const siteSettings = ref({});
const categories = ref([]);
const mobileMenuOpen = ref(false);

const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentUser = computed(() => authStore.user);

onMounted(async () => {
    try {
        const [settingsRes, categoriesRes] = await Promise.all([
            axios.get('/api/v1/settings'),
            axios.get('/api/v1/categories'),
        ]);
        siteSettings.value = settingsRes.data.data;
        categories.value = categoriesRes.data.data;
    } catch (e) {
        console.error('Failed to load site data:', e);
    }
});

const handleLogout = async () => {
    await authStore.logout();
    window.location.href = '/';
};
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Header / Navigation -->
        <header class="bg-white shadow-sm border-b">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <router-link to="/" class="text-xl font-bold text-green-600">
                        {{ siteSettings.site_logo_text || siteSettings.site_name || 'GigZone' }}
                    </router-link>

                    <!-- Search Bar -->
                    <div class="hidden md:flex flex-1 max-w-lg mx-8">
                        <form @submit.prevent="$router.push({ name: 'search', query: { search: searchQuery } })" class="w-full">
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search services..."
                                    class="w-full pl-4 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center space-x-4">
                        <router-link to="/categories" class="text-gray-600 hover:text-green-600 text-sm">
                            Explore
                        </router-link>
                        <router-link to="/buyer_requests" class="text-gray-600 hover:text-green-600 text-sm">
                            Buyer Requests
                        </router-link>

                        <template v-if="isAuthenticated">
                            <router-link to="/inbox" class="text-gray-600 hover:text-green-600 text-sm">
                                Inbox
                            </router-link>
                            <router-link to="/orders/buying" class="text-gray-600 hover:text-green-600 text-sm">
                                Orders
                            </router-link>

                            <!-- User Dropdown -->
                            <div class="relative group">
                                <button class="flex items-center space-x-2 text-sm text-gray-700 hover:text-green-600">
                                    <span>{{ currentUser?.name || currentUser?.username }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                                    <router-link :to="`/${currentUser?.username}`" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profile
                                    </router-link>
                                    <router-link to="/proposals/view" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        My Proposals
                                    </router-link>
                                    <router-link to="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Settings
                                    </router-link>
                                    <hr class="my-1" />
                                    <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <router-link to="/login" class="text-gray-600 hover:text-green-600 text-sm">
                                Sign In
                            </router-link>
                            <router-link to="/register" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-600">
                                Join
                            </router-link>
                        </template>
                    </nav>

                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Category Navigation -->
                <div class="hidden md:flex items-center space-x-6 py-2 border-t overflow-x-auto">
                    <router-link
                        v-for="category in categories"
                        :key="category.id"
                        :to="`/categories/${category.cat_url}`"
                        class="text-sm text-gray-500 hover:text-green-600 whitespace-nowrap"
                    >
                        {{ category.cat_title }}
                    </router-link>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white">
                <div class="px-4 py-4 space-y-2">
                    <router-link to="/categories" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Explore</router-link>
                    <router-link to="/buyer_requests" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Buyer Requests</router-link>
                    <template v-if="isAuthenticated">
                        <router-link to="/inbox" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Inbox</router-link>
                        <router-link to="/orders/buying" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Orders</router-link>
                        <router-link to="/settings" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Settings</router-link>
                        <button @click="handleLogout" class="block text-red-600 py-2">Logout</button>
                    </template>
                    <template v-else>
                        <router-link to="/login" class="block text-gray-600 py-2" @click="mobileMenuOpen = false">Sign In</router-link>
                        <router-link to="/register" class="block bg-green-500 text-white text-center py-2 rounded-lg" @click="mobileMenuOpen = false">Join</router-link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-gray-300 py-8 mt-auto">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-white font-bold mb-4">{{ siteSettings.site_name || 'GigZone' }}</h3>
                        <p class="text-sm">A fast growing freelance marketplace.</p>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3 text-sm">Categories</h4>
                        <ul class="space-y-1">
                            <li v-for="category in categories.slice(0, 5)" :key="category.id">
                                <router-link :to="`/categories/${category.cat_url}`" class="text-sm hover:text-white">
                                    {{ category.cat_title }}
                                </router-link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3 text-sm">About</h4>
                        <ul class="space-y-1">
                            <li><router-link to="/pages/about" class="text-sm hover:text-white">About Us</router-link></li>
                            <li><router-link to="/pages/terms-of-service" class="text-sm hover:text-white">Terms of Service</router-link></li>
                            <li><router-link to="/pages/privacy-policy" class="text-sm hover:text-white">Privacy Policy</router-link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold mb-3 text-sm">Support</h4>
                        <ul class="space-y-1">
                            <li><router-link to="/support" class="text-sm hover:text-white">Help & Support</router-link></li>
                            <li><router-link to="/blog" class="text-sm hover:text-white">Blog</router-link></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-8 pt-4 text-center text-sm">
                    &copy; {{ new Date().getFullYear() }} {{ siteSettings.site_name || 'GigZone' }}. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    data() {
        return {
            searchQuery: '',
        };
    },
};
</script>
