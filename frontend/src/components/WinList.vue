<script setup>
import WinCard from './WinCard.vue'

defineProps({ wins: { type: Array, required: true } })
</script>

<template>
  <ul class="win-list">
    <li v-for="win in wins" :key="win.id">
      <WinCard :win="win" />
    </li>
  </ul>
</template>
