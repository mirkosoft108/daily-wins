<script setup>
import { nextTick, reactive, ref, watch } from 'vue'

const props = defineProps({
  win: { type: Object, default: null },
  categories: { type: Array, required: true },
  saving: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
  error: { type: String, default: '' },
})
const emit = defineEmits(['submit', 'cancel'])
const form = ref(null)
const today = new Date()
const todayDate = [
  today.getFullYear(),
  String(today.getMonth() + 1).padStart(2, '0'),
  String(today.getDate()).padStart(2, '0'),
].join('-')
const fields = reactive({
  title: props.win?.title || '',
  description: props.win?.description || '',
  category_id: props.win?.category.id || '',
  win_date: props.win?.win_date || todayDate,
})
const localErrors = ref({})

function fieldError(name) {
  const error = localErrors.value[name] || props.errors[name]
  return Array.isArray(error) ? error.join(' ') : error || ''
}

async function focusInvalidField() {
  await nextTick()
  form.value?.querySelector('[aria-invalid="true"]')?.focus()
}

watch(() => props.errors, focusInvalidField)

function submit() {
  if (props.saving) return
  localErrors.value = {}
  if (!fields.title.trim()) localErrors.value.title = 'Give your win a title.'
  else if (fields.title.trim().length > 120) localErrors.value.title = 'Use 120 characters or fewer.'
  if (!fields.category_id) localErrors.value.category_id = 'Choose a category.'
  if (!fields.win_date) localErrors.value.win_date = 'Choose a date.'

  if (Object.keys(localErrors.value).length) {
    focusInvalidField()
    return
  }

  emit('submit', {
    title: fields.title.trim(),
    description: fields.description.trim() || null,
    category_id: Number(fields.category_id),
    win_date: fields.win_date,
  })
}
</script>

<template>
  <form ref="form" class="win-form" novalidate :aria-busy="saving" @submit.prevent="submit">
    <p class="form-intro">{{ win ? 'Make this moment your own.' : 'A small step is worth remembering.' }}</p>
    <p v-if="error" class="form-error" role="alert">{{ error }}</p>
    <fieldset :disabled="saving">
      <div class="form-field">
        <label for="win-title">Title</label>
        <input
          id="win-title" v-model="fields.title" type="text" maxlength="120" required autofocus
          :aria-invalid="Boolean(fieldError('title'))"
          :aria-describedby="fieldError('title') ? 'title-hint title-error' : 'title-hint'"
        />
        <p id="title-hint" class="field-hint">Up to 120 characters.</p>
        <p v-if="fieldError('title')" id="title-error" class="field-error">{{ fieldError('title') }}</p>
      </div>
      <div class="form-field">
        <label for="win-description">Description <span class="field-hint">(optional)</span></label>
        <textarea
          id="win-description" v-model="fields.description" rows="3"
          :aria-invalid="Boolean(fieldError('description'))"
          :aria-describedby="fieldError('description') ? 'description-error' : undefined"
        />
        <p v-if="fieldError('description')" id="description-error" class="field-error">{{ fieldError('description') }}</p>
      </div>
      <div class="form-field">
        <label for="win-form-category">Category</label>
        <select
          id="win-form-category" v-model="fields.category_id" required
          :aria-invalid="Boolean(fieldError('category_id'))"
          :aria-describedby="fieldError('category_id') ? 'category-error' : undefined"
        >
          <option disabled value="">Choose a category</option>
          <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
        </select>
        <p v-if="fieldError('category_id')" id="category-error" class="field-error">{{ fieldError('category_id') }}</p>
      </div>
      <div class="form-field">
        <label for="win-date">Date</label>
        <input
          id="win-date" v-model="fields.win_date" type="date" required
          :aria-invalid="Boolean(fieldError('win_date'))"
          :aria-describedby="fieldError('win_date') ? 'date-error' : undefined"
        />
        <p v-if="fieldError('win_date')" id="date-error" class="field-error">{{ fieldError('win_date') }}</p>
      </div>
    </fieldset>
    <div class="dialog-actions">
      <button class="secondary-button" type="button" :disabled="saving" @click="emit('cancel')">Cancel</button>
      <button class="primary-button" type="submit" :disabled="saving">
        {{ saving ? 'Saving…' : win ? 'Save changes' : 'Add Win' }}
      </button>
    </div>
  </form>
</template>
