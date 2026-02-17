import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export default {
    // Artisans
    searchArtisans(params) {
        return api.get('/artisans/search', { params });
    },
    getFeaturedArtisans() {
        return api.get('/artisans/featured');
    },
    getCategories() {
        return api.get('/artisans/categories');
    },
    getArtisan(id) {
        return api.get(`/artisans/${id}`);
    },
    createArtisan(data) {
        return api.post('/artisans', data);
    },
    updateArtisan(id, data) {
        return api.put(`/artisans/${id}`, data);
    },

    // Orders
    createOrder(data) {
        return api.post('/orders', data);
    },
    getOrder(id) {
        return api.get(`/orders/${id}`);
    },
    updateOrderStatus(id, status) {
        return api.put(`/orders/${id}/status`, { status });
    },
    getArtisanOrders(artisanId, params) {
        return api.get(`/artisans/${artisanId}/orders`, { params });
    },

    // Reviews
    submitReview(data) {
        return api.post('/reviews', data);
    },
    getArtisanReviews(artisanId, params) {
        return api.get(`/artisans/${artisanId}/reviews`, { params });
    },

    // Trust
    calculateTrust(artisanId) {
        return api.post(`/trust/calculate/${artisanId}`);
    },
    getTrustStats(artisanId) {
        return api.get(`/trust/stats/${artisanId}`);
    },
    getFraudAlerts() {
        return api.get('/trust/fraud-alerts');
    },
};
