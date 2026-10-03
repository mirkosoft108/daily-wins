<script setup>
defineProps({
  search: { type: String, default: '' },
  category: { type: String, default: '' },
  categories: { type: Array, required: true },
  categoriesDisabled: { type: Boolean, default: false },
})
defineEmits(['update:search', 'update:category', 'clear'])
</script>

<template>
  <div class="win-filters">
    <div class="filter-field search-field">
      <label for="win-search">Search</label>
      <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <circle cx="10.5" cy="10.5" r="6.5" />
          <path d="m16 16 4 4" stroke-linecap="round" />
        </svg>
        <input
          id="win-search"
          type="search"
          placeholder="Search your wins…"
          :value="search"
          @input="$emit('update:search', $event.target.value)"
        />
      </div>
    </div>
    <div class="filter-field">
      <label for="win-category">Category</label>
      <select
        id="win-category"
        :value="category"
        :disabled="categoriesDisabled"
        @change="$emit('update:category', $event.target.value)"
      >
        <option value="">All categories</option>
        <option v-for="item in categories" :key="item.id" :value="item.slug">{{ item.name }}</option>
      </select>
    </div>
    <button v-if="search || category" class="text-button clear-filters" type="button" @click="$emit('clear')">
      Clear filters
    </button>
  </div>
</template>
