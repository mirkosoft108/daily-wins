<script setup>
import { computed, onBeforeUnmount, onMounted, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useWinsStore } from '../stores/wins'
import AppHeader from '../components/AppHeader.vue'
import StatsGrid from '../components/StatsGrid.vue'
import WinFilters from '../components/WinFilters.vue'
import WinList from '../components/WinList.vue'
import LoadingState from '../components/LoadingState.vue'
import EmptyState from '../components/EmptyState.vue'
import ErrorState from '../components/ErrorState.vue'

const store = useWinsStore()
const {
  wins, categories, stats, search, category,
  winsLoading, categoriesLoading, statsLoading,
  winsError, categoriesError, statsError,
} = storeToRefs(store)
const filtered = computed(() => Boolean(search.value.trim() || category.value))
let searchTimer

function clearFilters() {
  search.value = ''
  category.value = ''
}

watch([search, category], ([, nextCategory], [, previousCategory]) => {
  clearTimeout(searchTimer)
  store.cancelWinsRequest()
  winsLoading.value = true
  winsError.value = ''

  if (nextCategory !== previousCategory) {
    store.fetchWins()
  } else {
    searchTimer = setTimeout(() => store.fetchWins(), 300)
  }
})

onMounted(() => store.loadDashboard())
onBeforeUnmount(() => {
  clearTimeout(searchTimer)
  store.cancelWinsRequest()
})
</script>

<template>
  <main class="dashboard">
    <AppHeader />

    <section class="progress-section" aria-labelledby="progress-heading">
      <h2 id="progress-heading" class="section-label">Your progress</h2>
      <ErrorState v-if="statsError" :message="statsError" @retry="store.fetchStats" />
      <StatsGrid v-else :stats="stats" :loading="statsLoading" />
    </section>

    <section class="wins-section" aria-labelledby="wins-heading">
      <div class="section-heading">
        <div>
          <h2 id="wins-heading">Your wins</h2>
          <p>Small moments worth remembering.</p>
        </div>
        <p class="result-count" aria-live="polite">
          {{ winsLoading ? 'Loading…' : winsError ? 'Unavailable' : `${wins.length} ${wins.length === 1 ? 'win' : 'wins'}` }}
        </p>
      </div>

      <WinFilters
        v-model:search="search"
        v-model:category="category"
        :categories="categories"
        :categories-disabled="categoriesLoading || Boolean(categoriesError)"
        @clear="clearFilters"
      />
      <ErrorState v-if="categoriesError" class="category-error" :message="categoriesError" @retry="store.fetchCategories" />

      <div class="wins-results" :aria-busy="winsLoading">
        <LoadingState v-if="winsLoading" />
        <ErrorState v-else-if="winsError" :message="winsError" @retry="store.fetchWins" />
        <EmptyState v-else-if="wins.length === 0" :filtered="filtered" @clear="clearFilters" />
        <WinList v-else :wins="wins" />
      </div>
    </section>
  </main>
</template>
