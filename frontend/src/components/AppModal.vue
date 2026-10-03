<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  busy: { type: Boolean, default: false },
})
const emit = defineEmits(['close'])
const dialog = ref(null)
let previousOverflow

function keepFocusInDialog(event) {
  const controls = dialog.value.querySelectorAll('button:enabled, input:enabled, select:enabled, textarea:enabled')
  const first = controls[0]
  const last = controls[controls.length - 1]

  if (props.busy || !first) {
    event.preventDefault()
  } else if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

onMounted(() => {
  previousOverflow = document.documentElement.style.overflow
  document.documentElement.style.overflow = 'hidden'
  dialog.value.showModal()
})

onBeforeUnmount(() => {
  dialog.value.close()
  document.documentElement.style.overflow = previousOverflow
})
</script>

<template>
  <Teleport to="body">
    <dialog
      ref="dialog"
      class="app-modal"
      aria-labelledby="modal-title"
      :aria-busy="busy"
      @cancel.prevent="!busy && emit('close')"
      @keydown.tab="keepFocusInDialog"
    >
      <h2 id="modal-title">{{ title }}</h2>
      <slot />
    </dialog>
  </Teleport>
</template>
