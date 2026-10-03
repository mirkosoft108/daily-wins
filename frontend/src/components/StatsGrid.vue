<script setup>
defineProps({
  stats: { type: Object, default: null },
  loading: { type: Boolean, default: false },
})

const metrics = [
  { key: 'total_wins', label: 'Total wins', note: 'Every little victory counts.' },
  { key: 'wins_this_week', label: 'This week', note: 'Monday through Sunday.' },
  { key: 'current_streak', label: 'Current streak', note: 'One day at a time.' },
]
const numberFormat = new Intl.NumberFormat('en')
</script>

<template>
  <div>
    <dl class="stats-grid" :aria-busy="loading">
      <div
        v-for="metric in metrics"
        :key="metric.key"
        class="stat-card"
        :class="{ 'stat-card-accent': metric.key === 'current_streak' }"
      >
        <dt>
          {{ metric.label }}
          <span class="stat-note">{{ metric.note }}</span>
        </dt>
        <dd>
          <span class="stat-number">
            {{ loading || !stats ? '—' : numberFormat.format(stats[metric.key]) }}
          </span>
          <span v-if="metric.key === 'current_streak' && stats && !loading" class="stat-unit">
            {{ stats.current_streak === 1 ? 'day' : 'days' }}
          </span>
        </dd>
      </div>
    </dl>
    <p v-if="loading" class="sr-only" role="status">Loading your progress.</p>
  </div>
</template>
