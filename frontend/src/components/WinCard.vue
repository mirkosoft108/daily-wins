<script setup>
import { computed } from 'vue'

const props = defineProps({ win: { type: Object, required: true } })
const dateFormat = new Intl.DateTimeFormat('en', {
  month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC',
})
const formattedDate = computed(() => dateFormat.format(new Date(`${props.win.win_date}T00:00:00Z`)))
</script>

<template>
  <article class="win-card">
    <div class="win-meta">
      <span class="category-badge">{{ win.category.name }}</span>
      <time :datetime="win.win_date">{{ formattedDate }}</time>
    </div>
    <h3>{{ win.title }}</h3>
    <p v-if="win.description" class="win-description">{{ win.description }}</p>
  </article>
</template>
