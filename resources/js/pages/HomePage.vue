<template>
  <div>
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
      <div class="max-w-7xl mx-auto px-4 py-16 sm:py-24">
        <h1 class="text-4xl sm:text-5xl font-bold mb-4">Find Trusted Artisans</h1>
        <p class="text-xl text-blue-100 mb-8">
          Verified professionals ranked by our dynamic trust scoring system
        </p>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-lg p-4 flex flex-col sm:flex-row gap-3">
          <select
            v-model="searchCategory"
            class="flex-1 px-4 py-3 border border-gray-300 rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.service_category" :value="cat.service_category">
              {{ cat.service_category }} ({{ cat.artisan_count }})
            </option>
          </select>
          <input
            v-model="searchLocation"
            type="text"
            placeholder="Enter location..."
            class="flex-1 px-4 py-3 border border-gray-300 rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <button
            @click="doSearch"
            class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition"
          >
            Search
          </button>
        </div>
      </div>
    </div>

    <!-- Categories Grid -->
    <div class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Service Categories</h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <button
          v-for="cat in categories"
          :key="cat.service_category"
          @click="searchByCategory(cat.service_category)"
          class="bg-white rounded-lg shadow p-6 text-left hover:shadow-md transition border border-gray-100"
        >
          <div class="text-lg font-semibold text-gray-900">{{ cat.service_category }}</div>
          <div class="text-sm text-gray-500 mt-1">{{ cat.artisan_count }} artisans</div>
          <div class="text-xs text-blue-600 mt-2">
            Avg trust: {{ (parseFloat(cat.avg_trust) * 100).toFixed(0) }}%
          </div>
        </button>
      </div>
    </div>

    <!-- Featured Artisans -->
    <div class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Artisans</h2>
      <div v-if="featured.length === 0" class="text-gray-500">No featured artisans yet.</div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <ArtisanCard v-for="artisan in featured" :key="artisan.id" :artisan="artisan" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api.js';
import ArtisanCard from '../components/ArtisanCard.vue';

const router = useRouter();
const categories = ref([]);
const featured = ref([]);
const searchCategory = ref('');
const searchLocation = ref('');

onMounted(async () => {
  try {
    const [catRes, featRes] = await Promise.all([
      api.getCategories(),
      api.getFeaturedArtisans(),
    ]);
    categories.value = catRes.data;
    featured.value = featRes.data;
  } catch (e) {
    console.error('Failed to load homepage data', e);
  }
});

function doSearch() {
  const query = {};
  if (searchCategory.value) query.category = searchCategory.value;
  if (searchLocation.value) query.location = searchLocation.value;
  router.push({ name: 'search', query });
}

function searchByCategory(category) {
  router.push({ name: 'search', query: { category } });
}
</script>
