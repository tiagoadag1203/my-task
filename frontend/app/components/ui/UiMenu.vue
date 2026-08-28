<script setup>
defineProps({
  label: { type: String, default: 'Mais ações' },
  items: { type: Array, default: () => [] },
})

const open = ref(false)
const root = ref(null)

function close() {
  open.value = false
}

function toggle() {
  open.value = !open.value
}

function onSelect(item) {
  if (item.disabled) return
  close()
  item.onSelect?.()
}

function onDocumentPointerDown(event) {
  if (!root.value?.contains(event.target)) close()
}

function onDocumentKeydown(event) {
  if (event.key === 'Escape') close()
}

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentPointerDown)
  document.addEventListener('keydown', onDocumentKeydown)
})

onUnmounted(() => {
  document.removeEventListener('pointerdown', onDocumentPointerDown)
  document.removeEventListener('keydown', onDocumentKeydown)
})
</script>

<template>
  <div ref="root" class="relative no-drag" @pointerdown.stop>
    <button
      type="button"
      class="grid size-8 place-items-center rounded-lg text-[var(--app-text-muted)] hover:bg-[var(--app-surface-muted)] hover:text-[var(--app-text)]"
      :aria-label="label"
      aria-haspopup="menu"
      :aria-expanded="open"
      @click.stop="toggle"
    >
      <slot name="trigger">
        <UiIcon name="more" />
      </slot>
    </button>
    <div
      v-if="open"
      role="menu"
      class="absolute right-0 z-40 mt-1 min-w-40 overflow-hidden rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] py-1 shadow-lg"
    >
      <slot :close="close">
        <button
          v-for="item in items"
          :key="item.label"
          type="button"
          role="menuitem"
          class="flex w-full px-3 py-2 text-left text-sm font-semibold hover:bg-[var(--app-surface-muted)]"
          :class="item.tone === 'danger' ? 'text-red-700 dark:text-red-300' : 'text-[var(--app-text)]'"
          :disabled="item.disabled"
          @click.stop="onSelect(item)"
        >
          {{ item.label }}
        </button>
      </slot>
    </div>
  </div>
</template>
