<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>
    <div v-else-if="artisan">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-3xl font-bold text-gray-900">{{ artisan.name }}</h1>
              <TrustBadge :score="parseFloat(artisan.trust_score)" />
            </div>
            <p class="text-gray-600">{{ artisan.service_category }} &middot; {{ artisan.specialty }}</p>
            <p class="text-gray-500 text-sm mt-1">{{ artisan.location }}</p>
          </div>
          <div class="mt-4 md:mt-0 text-right">
            <div v-if="artisan.hourly_rate" class="text-2xl font-bold text-gray-900">
              {{ artisan.hourly_rate }} MAD/hr
            </div>
            <div class="text-sm text-gray-500 mt-1">
              {{ artisan.jobs_completed }} jobs completed
            </div>
          </div>
        </div>

        <!-- Trust Score Bar -->
        <div class="mt-6">
          <div class="flex justify-between text-sm mb-1">
            <span class="text-gray-600">Trust Score</span>
            <span class="font-semibold">{{ (parseFloat(artisan.trust_score) * 100).toFixed(1) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div
              class="h-3 rounded-full transition-all"
              :class="trustBarColor"
              :style="{ width: (parseFloat(artisan.trust_score) * 100) + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Trust Breakdown -->
      <div v-if="trustStats" class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Trust Breakdown</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="text-2xl font-bold text-blue-700">{{ (trustStats.rating_component * 100).toFixed(1) }}%</div>
            <div class="text-sm text-gray-600 mt-1">Rating Score</div>
          </div>
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="text-2xl font-bold text-green-700">{{ (trustStats.completion_component * 100).toFixed(1) }}%</div>
            <div class="text-sm text-gray-600 mt-1">Completion Rate</div>
          </div>
          <div class="text-center p-4 bg-red-50 rounded-lg">
            <div class="text-2xl font-bold text-red-700">{{ (trustStats.penalty * 100).toFixed(1) }}%</div>
            <div class="text-sm text-gray-600 mt-1">Consistency Penalty</div>
          </div>
          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <div class="text-2xl font-bold text-purple-700">{{ trustStats.review_count }}</div>
            <div class="text-sm text-gray-600 mt-1">Total Reviews</div>
          </div>
        </div>
      </div>

      <!-- Services -->
      <div v-if="artisan.services && artisan.services.length" class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Services Offered</h2>
        <div class="space-y-3">
          <div v-for="svc in artisan.services" :key="svc.id" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <div>
              <div class="font-medium text-gray-900">{{ svc.name }}</div>
              <div v-if="svc.description" class="text-sm text-gray-500">{{ svc.description }}</div>
            </div>
            <div class="text-right">
              <div v-if="svc.base_price" class="font-semibold text-gray-900">{{ svc.base_price }} MAD</div>
              <div v-if="svc.duration_estimate" class="text-xs text-gray-500">~{{ svc.duration_estimate }} min</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Reviews</h2>
        <div v-if="reviews.length === 0" class="text-gray-500">No reviews yet.</div>
        <div class="space-y-4">
          <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 pb-4 last:border-0">
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <StarRating :rating="review.rating" />
                <span class="text-sm text-gray-600">{{ review.customer?.name || 'Customer' }}</span>
              </div>
              <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
            </div>
            <p v-if="review.comment" class="text-gray-700 text-sm">{{ review.comment }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api.js';
import TrustBadge from '../components/TrustBadge.vue';
import StarRating from '../components/StarRating.vue';

const props = defineProps({ id: [String, Number] });

const artisan = ref(null);
const trustStats = ref(null);
const reviews = ref([]);
const loading = ref(true);

const trustBarColor = computed(() => {
  const score = parseFloat(artisan.value?.trust_score || 0);
  if (score >= 0.85) return 'bg-yellow-400';
  if (score >= 0.70) return 'bg-gray-400';
  if (score >= 0.50) return 'bg-orange-500';
  return 'bg-red-400';
});

onMounted(async () => {
  try {
    const [artRes, trustRes, revRes] = await Promise.all([
      api.getArtisan(props.id),
      api.getTrustStats(props.id).catch(() => null),
      api.getArtisanReviews(props.id),
    ]);
    artisan.value = artRes.data;
    trustStats.value = trustRes?.data || null;
    reviews.value = revRes.data.data || [];
  } catch (e) {
    console.error('Failed to load artisan profile', e);
  } finally {
    loading.value = false;
  }
});

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
