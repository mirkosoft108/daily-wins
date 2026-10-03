<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useWinsStore } from '../stores/wins'
import AppHeader from '../components/AppHeader.vue'
import StatsGrid from '../components/StatsGrid.vue'
import WinFilters from '../components/WinFilters.vue'
import WinList from '../components/WinList.vue'
import LoadingState from '../components/LoadingState.vue'
import EmptyState from '../components/EmptyState.vue'
import ErrorState from '../components/ErrorState.vue'
import AppModal from '../components/AppModal.vue'
import WinForm from '../components/WinForm.vue'

const store = useWinsStore()
const {
  wins, categories, stats, search, category,
  winsLoading, categoriesLoading, statsLoading,
  winsError, categoriesError, statsError,
  saving, mutationErrors, mutationError, successMessage,
} = storeToRefs(store)
const filtered = computed(() => Boolean(search.value.trim() || category.value))
const formDisabled = computed(() => categoriesLoading.value || Boolean(categoriesError.value) || !categories.value.length || saving.value)
const modalMode = ref('')
const selectedWin = ref(null)
const addButton = ref(null)
let returnFocus
let searchTimer

function openDialog(mode, win = null) {
  if (saving.value || (mode !== 'delete' && formDisabled.value)) return
  returnFocus = document.activeElement
  store.clearMutationFeedback()
  selectedWin.value = win
  modalMode.value = mode
}

async function closeDialog() {
  if (saving.value) return
  modalMode.value = ''
  selectedWin.value = null
  await nextTick()
  const target = returnFocus?.isConnected ? returnFocus : addButton.value
  target?.focus()
}

async function saveWin(payload) {
  if (await store.saveWin(payload, selectedWin.value?.id ?? null)) closeDialog()
}

async function deleteWin() {
  if (await store.deleteWin(selectedWin.value.id)) closeDialog()
}

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
    <div class="dashboard-header">
      <AppHeader />
      <button ref="addButton" class="primary-button" type="button" :disabled="formDisabled" @click="openDialog('create')">
        <span aria-hidden="true">+</span> Add Win
      </button>
    </div>

    <div v-if="successMessage" class="success-feedback" role="status">
      <p>{{ successMessage }}</p>
      <button class="text-button" type="button" aria-label="Dismiss success message" @click="successMessage = ''">Dismiss</button>
    </div>

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
        <EmptyState
          v-else-if="wins.length === 0" :filtered="filtered" :add-disabled="formDisabled"
          @clear="clearFilters" @add="openDialog('create')"
        />
        <WinList
          v-else :wins="wins" :edit-disabled="formDisabled" :busy="saving"
          @edit="openDialog('edit', $event)" @delete="openDialog('delete', $event)"
        />
      </div>
    </section>
  </main>

  <AppModal
    v-if="modalMode"
    :title="modalMode === 'delete' ? 'Delete this win?' : modalMode === 'edit' ? 'Edit your win' : 'Add a win'"
    :busy="saving" @close="closeDialog"
  >
    <template v-if="modalMode === 'delete'">
      <p class="delete-description">Delete “<strong>{{ selectedWin.title }}</strong>”? This cannot be undone.</p>
      <p v-if="mutationError" class="form-error" role="alert">{{ mutationError }}</p>
      <div class="dialog-actions">
        <button class="secondary-button" type="button" :disabled="saving" autofocus @click="closeDialog">Cancel</button>
        <button class="danger-button" type="button" :disabled="saving" @click="deleteWin">{{ saving ? 'Deleting…' : 'Delete win' }}</button>
      </div>
    </template>
    <WinForm
      v-else :win="selectedWin" :categories="categories" :saving="saving"
      :errors="mutationErrors" :error="mutationError" @submit="saveWin" @cancel="closeDialog"
    />
  </AppModal>
</template>
