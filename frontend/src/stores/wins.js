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
  const saving = ref(false)
  const mutationErrors = ref({})
  const mutationError = ref('')
  const successMessage = ref('')
  let winsController = null
  let statsRequest = 0

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
    const request = ++statsRequest
    statsLoading.value = true
    statsError.value = ''

    try {
      const { data } = await http.get('/stats')
      const fields = ['total_wins', 'wins_this_week', 'current_streak']
      if (!fields.every((key) => Number.isInteger(data[key]) && data[key] >= 0)) {
        throw new Error('Invalid statistics response')
      }
      if (request === statsRequest) stats.value = data
    } catch {
      if (request === statsRequest) statsError.value = "Your progress couldn't be loaded. Please try again."
    } finally {
      if (request === statsRequest) statsLoading.value = false
    }
  }

  function loadDashboard() {
    return Promise.all([fetchWins(), fetchCategories(), fetchStats()])
  }

  function clearMutationFeedback() {
    mutationErrors.value = {}
    mutationError.value = ''
    successMessage.value = ''
  }

  async function mutateWin(request, message) {
    if (saving.value) return false
    saving.value = true
    clearMutationFeedback()

    try {
      await request()
      successMessage.value = message
      await Promise.all([fetchWins(), fetchStats()])
      return true
    } catch (error) {
      if (error.response?.status === 422) {
        mutationErrors.value = error.response.data.errors || {}
        mutationError.value = 'Please check the highlighted fields.'
      } else if (error.response?.status === 404) {
        mutationError.value = 'This win no longer exists. Close this dialog and refresh the page.'
      } else {
        mutationError.value = "Your change couldn't be saved. Please try again."
      }
      return false
    } finally {
      saving.value = false
    }
  }

  function saveWin(payload, id = null) {
    return mutateWin(
      () => id === null ? http.post('/wins', payload) : http.put(`/wins/${id}`, payload),
      id === null ? 'Win added.' : 'Win updated.',
    )
  }

  function deleteWin(id) {
    return mutateWin(() => http.delete(`/wins/${id}`), 'Win deleted.')
  }

  return {
    wins, categories, stats, search, category,
    winsLoading, categoriesLoading, statsLoading,
    winsError, categoriesError, statsError,
    fetchWins, fetchCategories, fetchStats, loadDashboard, cancelWinsRequest,
    saving, mutationErrors, mutationError, successMessage,
    clearMutationFeedback, saveWin, deleteWin,
  }
})
