import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import axios from 'axios';

import '../css/app.css';

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}
axios.defaults.baseURL = window.location.origin;

// Create Vue app
const app = createApp(App);

// Install plugins
app.use(createPinia());
app.use(router);

// Global properties
app.config.globalProperties.$axios = axios;

// Mount
app.mount('#app');
