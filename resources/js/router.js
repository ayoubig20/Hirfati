import { createRouter, createWebHistory } from 'vue-router';
import HomePage from './pages/HomePage.vue';
import SearchPage from './pages/SearchPage.vue';
import ArtisanProfile from './pages/ArtisanProfile.vue';
import AdminDashboard from './pages/AdminDashboard.vue';

const routes = [
    { path: '/', name: 'home', component: HomePage },
    { path: '/search', name: 'search', component: SearchPage },
    { path: '/artisan/:id', name: 'artisan', component: ArtisanProfile, props: true },
    { path: '/admin', name: 'admin', component: AdminDashboard },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
