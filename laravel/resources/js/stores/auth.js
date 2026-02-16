import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isAuthenticated: false,
        loading: false,
    }),

    getters: {
        username: (state) => state.user?.username || '',
        displayName: (state) => state.user?.name || state.user?.username || '',
    },

    actions: {
        async checkAuth() {
            try {
                const response = await axios.get('/api/v1/auth/status');
                this.isAuthenticated = response.data.authenticated;
                this.user = response.data.user;
            } catch {
                this.isAuthenticated = false;
                this.user = null;
            }
        },

        async login(usernameOrEmail, password) {
            this.loading = true;
            try {
                const response = await axios.post('/login', {
                    seller_user_name: usernameOrEmail,
                    seller_pass: password,
                });

                if (response.data.success) {
                    await this.checkAuth();
                    return { success: true, redirect: response.data.redirect };
                }

                return { success: false, error: response.data.error };
            } catch (error) {
                const errorData = error.response?.data;
                return {
                    success: false,
                    error: errorData?.error || 'login_failed',
                    errors: errorData?.errors || {},
                };
            } finally {
                this.loading = false;
            }
        },

        async register(data) {
            this.loading = true;
            try {
                const response = await axios.post('/register', data);

                if (response.data.success) {
                    await this.checkAuth();
                    return { success: true, redirect: response.data.redirect };
                }

                return { success: false, error: response.data.error };
            } catch (error) {
                const errorData = error.response?.data;
                return {
                    success: false,
                    error: errorData?.error || 'register_failed',
                    errors: errorData?.errors || {},
                };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await axios.get('/logout');
            } finally {
                this.user = null;
                this.isAuthenticated = false;
            }
        },
    },
});
