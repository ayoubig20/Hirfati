<template>
  <router-link
    :to="`/artisan/${artisan.id}`"
    class="bg-white rounded-lg shadow hover:shadow-md transition border border-gray-100 block"
  >
    <div class="p-5">
      <div class="flex items-start justify-between mb-3">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">{{ artisan.name }}</h3>
          <p class="text-sm text-gray-500">{{ artisan.service_category }}</p>
        </div>
        <TrustBadge :score="parseFloat(artisan.trust_score)" />
      </div>

      <p class="text-sm text-gray-600 mb-3">{{ artisan.location }}</p>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1">
          <StarRating :rating="Math.round(parseFloat(artisan.avg_rating))" :small="true" />
          <span class="text-sm text-gray-600 ml-1">{{ parseFloat(artisan.avg_rating).toFixed(1) }}</span>
        </div>
        <div v-if="artisan.hourly_rate" class="text-sm font-semibold text-gray-900">
          {{ artisan.hourly_rate }} MAD/hr
        </div>
      </div>

      <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
        <span>{{ artisan.jobs_completed }} jobs</span>
        <span>Trust: {{ (parseFloat(artisan.trust_score) * 100).toFixed(0) }}%</span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
import TrustBadge from './TrustBadge.vue';
import StarRating from './StarRating.vue';

defineProps({
  artisan: { type: Object, required: true },
});
</script>
