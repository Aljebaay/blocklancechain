<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';

const authStore = useAuthStore();

onMounted(async () => {
    await authStore.checkAuth();
});
</script>

<template>
    <MainLayout>
        <router-view />
    </MainLayout>
</template>
