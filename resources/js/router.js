import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from './composables/useAuth.js';
import HomePage from './pages/HomePage.vue';
import SearchPage from './pages/SearchPage.vue';
import ArtisanProfile from './pages/ArtisanProfile.vue';
import AdminDashboard from './pages/AdminDashboard.vue';
import LoginPage from './pages/LoginPage.vue';
import ArtisanRegister from './pages/ArtisanRegister.vue';

const routes = [
    { path: '/', name: 'home', component: HomePage },
    { path: '/search', name: 'search', component: SearchPage },
    { path: '/artisan/:id', name: 'artisan', component: ArtisanProfile, props: true },
    { path: '/register-artisan', name: 'register-artisan', component: ArtisanRegister },
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
        meta: { guest: true, hideLayout: true },
    },
    {
        path: '/admin',
        name: 'admin',
        component: AdminDashboard,
        meta: { requiresAdmin: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        if (to.hash) return { el: to.hash, behavior: 'smooth' };
        return { top: 0 };
    },
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuth();

    // Check auth state on first navigation
    await auth.checkAuth();

    // Admin routes require authentication + admin role
    if (to.meta.requiresAdmin) {
        if (!auth.isAdmin()) {
            return next({ name: 'login', query: { redirect: to.fullPath } });
        }
    }

    // Guest-only routes (login page) redirect admins to dashboard
    if (to.meta.guest && auth.isAdmin()) {
        return next({ name: 'admin' });
    }

    next();
});

export default router;
