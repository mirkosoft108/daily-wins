<script setup>
defineProps({ message: { type: String, required: true } })
defineEmits(['retry'])
</script>

<template>
  <div class="state-panel error-panel" role="alert">
    <h3>Something went wrong</h3>
    <p>{{ message }}</p>
    <button class="secondary-button" type="button" @click="$emit('retry')">Try again</button>
  </div>
</template>
