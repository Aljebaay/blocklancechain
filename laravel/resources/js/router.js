import { createRouter, createWebHistory } from 'vue-router';

// Lazy-loaded page components
const Home = () => import('@/pages/Home.vue');
const Login = () => import('@/pages/Login.vue');
const Register = () => import('@/pages/Register.vue');
const Search = () => import('@/pages/Search.vue');
const CategoryIndex = () => import('@/pages/CategoryIndex.vue');
const CategoryShow = () => import('@/pages/CategoryShow.vue');
const ProposalShow = () => import('@/pages/ProposalShow.vue');
const UserProfile = () => import('@/pages/UserProfile.vue');
const BlogIndex = () => import('@/pages/BlogIndex.vue');
const BlogPost = () => import('@/pages/BlogPost.vue');
const TagShow = () => import('@/pages/TagShow.vue');
const PageShow = () => import('@/pages/PageShow.vue');

// Authenticated pages
const MyProposals = () => import('@/pages/MyProposals.vue');
const CreateProposal = () => import('@/pages/CreateProposal.vue');
const EditProposal = () => import('@/pages/EditProposal.vue');
const Inbox = () => import('@/pages/Inbox.vue');
const ConversationShow = () => import('@/pages/ConversationShow.vue');
const BuyingOrders = () => import('@/pages/BuyingOrders.vue');
const SellingOrders = () => import('@/pages/SellingOrders.vue');
const OrderShow = () => import('@/pages/OrderShow.vue');
const Settings = () => import('@/pages/Settings.vue');
const BuyerRequests = () => import('@/pages/BuyerRequests.vue');

const routes = [
    // Public routes
    { path: '/', name: 'home', component: Home },
    { path: '/login', name: 'login', component: Login },
    { path: '/register', name: 'register', component: Register },
    { path: '/search', name: 'search', component: Search },
    { path: '/categories', name: 'categories.index', component: CategoryIndex },
    { path: '/categories/:catUrl/:catChildUrl?', name: 'categories.show', component: CategoryShow },
    { path: '/proposals/:username/:proposalUrl', name: 'proposals.show', component: ProposalShow },
    { path: '/blog', name: 'blog.index', component: BlogIndex },
    { path: '/blog/:id/:slug?', name: 'blog.show', component: BlogPost },
    { path: '/tags/:tag', name: 'tags.show', component: TagShow },
    { path: '/pages/:slug', name: 'pages.show', component: PageShow },
    { path: '/buyer_requests', name: 'requests.index', component: BuyerRequests },

    // Authenticated routes
    { path: '/proposals/view', name: 'proposals.my', component: MyProposals, meta: { requiresAuth: true } },
    { path: '/proposals/create', name: 'proposals.create', component: CreateProposal, meta: { requiresAuth: true } },
    { path: '/proposals/edit/:proposalId', name: 'proposals.edit', component: EditProposal, meta: { requiresAuth: true } },
    { path: '/inbox', name: 'inbox', component: Inbox, meta: { requiresAuth: true } },
    { path: '/inbox/:conversationId', name: 'conversation.show', component: ConversationShow, meta: { requiresAuth: true } },
    { path: '/orders/buying/:status?', name: 'orders.buying', component: BuyingOrders, meta: { requiresAuth: true } },
    { path: '/orders/selling/:status?', name: 'orders.selling', component: SellingOrders, meta: { requiresAuth: true } },
    { path: '/orders/:orderId', name: 'orders.show', component: OrderShow, meta: { requiresAuth: true } },
    { path: '/settings', name: 'settings', component: Settings, meta: { requiresAuth: true } },

    // User profile (catch-all for /{username} - must be last)
    { path: '/:username', name: 'user.profile', component: UserProfile },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }
        return { top: 0 };
    },
});

// Navigation guard for authenticated routes
router.beforeEach(async (to, from, next) => {
    if (to.meta.requiresAuth) {
        const { useAuthStore } = await import('@/stores/auth');
        const authStore = useAuthStore();
        if (!authStore.isAuthenticated) {
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }
    }
    next();
});

export default router;
