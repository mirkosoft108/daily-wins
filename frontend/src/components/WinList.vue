<script setup>
import WinCard from './WinCard.vue'

defineProps({
  wins: { type: Array, required: true },
  editDisabled: { type: Boolean, default: false },
  busy: { type: Boolean, default: false },
})
defineEmits(['edit', 'delete'])
</script>

<template>
  <ul class="win-list">
    <li v-for="win in wins" :key="win.id">
      <WinCard
        :win="win" :edit-disabled="editDisabled" :busy="busy"
        @edit="$emit('edit', $event)" @delete="$emit('delete', $event)"
      />
    </li>
  </ul>
</template>
