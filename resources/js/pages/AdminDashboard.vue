<template>
  <div class="max-w-7xl mx-auto px-4 pt-24 pb-8">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
      <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm font-semibold rounded-full">Admin Panel</span>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Artisans</div>
        <div class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalArtisans }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Avg Trust Score</div>
        <div class="text-3xl font-bold text-blue-600 mt-1">{{ (stats.avgTrust * 100).toFixed(1) }}%</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Gold Tier</div>
        <div class="text-3xl font-bold text-yellow-500 mt-1">{{ stats.goldCount }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Fraud Alerts</div>
        <div class="text-3xl font-bold text-red-600 mt-1">{{ fraudAlerts.length }}</div>
      </div>
    </div>

    <!-- Trust Distribution -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Trust Score Distribution</h2>
      <div class="flex items-end gap-1 h-40">
        <div
          v-for="(count, bucket) in distribution"
          :key="bucket"
          class="flex-1 bg-blue-500 rounded-t transition-all hover:bg-blue-600"
          :style="{ height: (count / maxDistribution * 100) + '%' }"
          :title="`${bucket}: ${count} artisans`"
        ></div>
      </div>
      <div class="flex gap-1 mt-1">
        <div v-for="(_, bucket) in distribution" :key="bucket" class="flex-1 text-xs text-gray-500 text-center">
          {{ bucket }}
        </div>
      </div>
    </div>

    <!-- Fraud Alerts -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Fraud Detection Alerts</h2>
      <div v-if="fraudAlerts.length === 0" class="text-gray-500">No fraud alerts.</div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-gray-500 uppercase bg-gray-50">
            <tr>
              <th class="px-4 py-3">Artisan</th>
              <th class="px-4 py-3">Category</th>
              <th class="px-4 py-3">Rating Component</th>
              <th class="px-4 py-3">Completion Component</th>
              <th class="px-4 py-3">Penalty</th>
              <th class="px-4 py-3">Trust Score</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="alert in fraudAlerts" :key="alert.id" class="border-b hover:bg-gray-50">
              <td class="px-4 py-3 font-medium">
                <router-link :to="`/artisan/${alert.id}`" class="text-blue-600 hover:underline">
                  {{ alert.name }}
                </router-link>
              </td>
              <td class="px-4 py-3">{{ alert.service_category }}</td>
              <td class="px-4 py-3">{{ (parseFloat(alert.rating_component) * 100).toFixed(1) }}%</td>
              <td class="px-4 py-3">{{ (parseFloat(alert.completion_component) * 100).toFixed(1) }}%</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">
                  {{ (parseFloat(alert.penalty) * 100).toFixed(1) }}%
                </span>
              </td>
              <td class="px-4 py-3">{{ (parseFloat(alert.trust_score) * 100).toFixed(1) }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../api.js';

const fraudAlerts = ref([]);
const allArtisans = ref([]);
const stats = ref({ totalArtisans: 0, avgTrust: 0, goldCount: 0 });

const distribution = ref({
  '0-10': 0, '10-20': 0, '20-30': 0, '30-40': 0, '40-50': 0,
  '50-60': 0, '60-70': 0, '70-80': 0, '80-90': 0, '90-100': 0,
});

const maxDistribution = computed(() => Math.max(1, ...Object.values(distribution.value)));

onMounted(async () => {
  try {
    const [searchRes, alertsRes] = await Promise.all([
      api.searchArtisans({ per_page: 50 }),
      api.getFraudAlerts(),
    ]);

    allArtisans.value = searchRes.data.data;
    fraudAlerts.value = alertsRes.data;

    // Calculate stats
    const artisans = allArtisans.value;
    stats.value.totalArtisans = searchRes.data.total;

    if (artisans.length) {
      stats.value.avgTrust = artisans.reduce((sum, a) => sum + parseFloat(a.trust_score), 0) / artisans.length;
      stats.value.goldCount = artisans.filter(a => parseFloat(a.trust_score) >= 0.85).length;
    }

    // Build distribution
    const dist = { ...distribution.value };
    artisans.forEach(a => {
      const pct = parseFloat(a.trust_score) * 100;
      const bucket = Math.min(Math.floor(pct / 10), 9);
      const keys = Object.keys(dist);
      dist[keys[bucket]]++;
    });
    distribution.value = dist;
  } catch (e) {
    console.error('Failed to load admin data', e);
  }
});
</script>
