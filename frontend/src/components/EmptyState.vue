<script setup>
defineProps({
  filtered: { type: Boolean, default: false },
  addDisabled: { type: Boolean, default: false },
})
defineEmits(['clear', 'add'])
</script>

<template>
  <div class="state-panel" role="status">
    <span class="state-symbol" aria-hidden="true">✓</span>
    <h3>{{ filtered ? 'No wins match your filters' : 'Every little win belongs here' }}</h3>
    <p>{{ filtered ? 'Try a different search or category.' : 'Start with one small thing you did today.' }}</p>
    <button v-if="filtered" class="secondary-button" type="button" @click="$emit('clear')">Clear filters</button>
    <button v-else class="primary-button empty-add" type="button" :disabled="addDisabled" @click="$emit('add')">Add your first win</button>
  </div>
</template>
