<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-col sm:flex-row gap-3 items-end">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select v-model="filters.category" @change="search" class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-700">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.service_category" :value="cat.service_category">
            {{ cat.service_category }}
          </option>
        </select>
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
        <input v-model="filters.location" @keyup.enter="search" type="text" placeholder="Location..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-700" />
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Min Rating</label>
        <select v-model="filters.min_rating" @change="search" class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-700">
          <option value="">Any</option>
          <option value="4">4+</option>
          <option value="3">3+</option>
          <option value="2">2+</option>
        </select>
      </div>
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
        <select v-model="filters.sort_by" @change="search" class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-700">
          <option value="trust_score">Trust Score</option>
          <option value="ranking">Trust Ranking</option>
          <option value="rating">Rating</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
        </select>
      </div>
      <button @click="search" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
        Search
      </button>
    </div>

    <!-- Results -->
    <div class="mb-4 text-sm text-gray-500">
      {{ total }} artisans found
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ArtisanCard v-for="artisan in artisans" :key="artisan.id" :artisan="artisan" />
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-8">
      <button
        v-for="page in lastPage"
        :key="page"
        @click="goToPage(page)"
        :class="[
          'px-3 py-1 rounded text-sm',
          page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50'
        ]"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api.js';
import ArtisanCard from '../components/ArtisanCard.vue';

const route = useRoute();
const router = useRouter();

const artisans = ref([]);
const categories = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  category: route.query.category || '',
  location: route.query.location || '',
  min_rating: route.query.min_rating || '',
  sort_by: route.query.sort_by || 'trust_score',
});

onMounted(async () => {
  try {
    const res = await api.getCategories();
    categories.value = res.data;
  } catch (e) { /* ignore */ }
  search();
});

watch(() => route.query, (q) => {
  if (q.category) filters.value.category = q.category;
  if (q.location) filters.value.location = q.location;
  search();
});

async function search(page = 1) {
  loading.value = true;
  try {
    const params = { page, per_page: 12 };
    if (filters.value.category) params.category = filters.value.category;
    if (filters.value.location) params.location = filters.value.location;
    if (filters.value.min_rating) params.min_rating = filters.value.min_rating;
    if (filters.value.sort_by) params.sort_by = filters.value.sort_by;

    const res = await api.searchArtisans(params);
    artisans.value = res.data.data;
    total.value = res.data.total;
    currentPage.value = res.data.current_page;
    lastPage.value = res.data.last_page;
  } catch (e) {
    console.error('Search failed', e);
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  search(page);
}
</script>
