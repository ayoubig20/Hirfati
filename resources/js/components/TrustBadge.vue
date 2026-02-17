<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold',
      badgeClass
    ]"
  >
    {{ tier }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  score: { type: Number, required: true },
});

const tier = computed(() => {
  if (props.score >= 0.85) return 'Gold';
  if (props.score >= 0.70) return 'Silver';
  if (props.score >= 0.50) return 'Bronze';
  return 'New';
});

const badgeClass = computed(() => {
  if (props.score >= 0.85) return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
  if (props.score >= 0.70) return 'bg-gray-100 text-gray-700 border border-gray-300';
  if (props.score >= 0.50) return 'bg-orange-100 text-orange-800 border border-orange-300';
  return 'bg-blue-50 text-blue-600 border border-blue-200';
});
</script>
