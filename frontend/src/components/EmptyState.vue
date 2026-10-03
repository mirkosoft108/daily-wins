<script setup>
defineProps({ filtered: { type: Boolean, default: false } })
defineEmits(['clear'])
</script>

<template>
  <div class="state-panel" role="status">
    <span class="state-symbol" aria-hidden="true">✓</span>
    <h3>{{ filtered ? 'No wins match your filters' : 'Every little win belongs here' }}</h3>
    <p>{{ filtered ? 'Try a different search or category.' : 'Your daily victories will appear here.' }}</p>
    <button v-if="filtered" class="secondary-button" type="button" @click="$emit('clear')">Clear filters</button>
  </div>
</template>
