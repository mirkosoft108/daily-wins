import { defineStore } from 'pinia'
import { ref } from 'vue'
import http from '../api/http'

export const useWinsStore = defineStore('wins', () => {
  const wins = ref([])
  const categories = ref([])
  const stats = ref(null)
  const search = ref('')
  const category = ref('')
  const winsLoading = ref(false)
  const categoriesLoading = ref(false)
  const statsLoading = ref(false)
  const winsError = ref('')
  const categoriesError = ref('')
  const statsError = ref('')
  let winsController = null

  function cancelWinsRequest() {
    winsController?.abort()
    winsController = null
  }

  async function fetchWins() {
    cancelWinsRequest()
    const controller = new AbortController()
    winsController = controller
    winsLoading.value = true
    winsError.value = ''

    try {
      const { data } = await http.get('/wins', {
        params: {
          search: search.value.trim() || undefined,
          category: category.value || undefined,
        },
        signal: controller.signal,
      })

      if (!Array.isArray(data.data)) throw new Error('Invalid wins response')
      if (!controller.signal.aborted) wins.value = data.data
    } catch {
      if (!controller.signal.aborted) {
        winsError.value = "Your wins couldn't be loaded. Please try again."
      }
    } finally {
      if (winsController === controller) {
        winsLoading.value = false
        winsController = null
      }
    }
  }

  async function fetchCategories() {
    categoriesLoading.value = true
    categoriesError.value = ''

    try {
      const { data } = await http.get('/categories')
      if (!Array.isArray(data)) throw new Error('Invalid categories response')
      categories.value = data
    } catch {
      categoriesError.value = "Categories couldn't be loaded. Please try again."
    } finally {
      categoriesLoading.value = false
    }
  }

  async function fetchStats() {
    statsLoading.value = true
    statsError.value = ''

    try {
      const { data } = await http.get('/stats')
      const fields = ['total_wins', 'wins_this_week', 'current_streak']
      if (!fields.every((key) => Number.isInteger(data[key]) && data[key] >= 0)) {
        throw new Error('Invalid statistics response')
      }
      stats.value = data
    } catch {
      statsError.value = "Your progress couldn't be loaded. Please try again."
    } finally {
      statsLoading.value = false
    }
  }

  function loadDashboard() {
    return Promise.all([fetchWins(), fetchCategories(), fetchStats()])
  }

  return {
    wins, categories, stats, search, category,
    winsLoading, categoriesLoading, statsLoading,
    winsError, categoriesError, statsError,
    fetchWins, fetchCategories, fetchStats, loadDashboard, cancelWinsRequest,
  }
})
