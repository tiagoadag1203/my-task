<script setup>
defineProps({
  label: { type: String, default: 'Mais ações' },
  items: { type: Array, default: () => [] },
})

const open = ref(false)
const trigger = ref(null)
const panel = ref(null)
const position = ref({ top: 0, left: 0 })

function close() {
  open.value = false
}

function updatePosition() {
  const rect = trigger.value?.getBoundingClientRect()
  if (!rect) return

  const menuWidth = panel.value?.offsetWidth || 160
  const menuHeight = panel.value?.offsetHeight || 0
  const gap = 4
  let top = rect.bottom + gap
  let left = rect.right - menuWidth

  if (left < 8) left = 8
  if (left + menuWidth > window.innerWidth - 8) {
    left = Math.max(8, window.innerWidth - menuWidth - 8)
  }
  if (menuHeight && top + menuHeight > window.innerHeight - 8) {
    top = Math.max(8, rect.top - menuHeight - gap)
  }

  position.value = { top, left }
}

async function toggle() {
  open.value = !open.value
  if (!open.value) return
  await nextTick()
  updatePosition()
}

function onSelect(item) {
  if (item.disabled) return
  close()
  item.onSelect?.()
}

function onDocumentPointerDown(event) {
  if (trigger.value?.contains(event.target) || panel.value?.contains(event.target)) return
  close()
}

function onDocumentKeydown(event) {
  if (event.key === 'Escape') close()
}

watch(open, (isOpen) => {
  if (!import.meta.client) return

  if (isOpen) {
    window.addEventListener('scroll', updatePosition, true)
    window.addEventListener('resize', updatePosition)
    return
  }

  window.removeEventListener('scroll', updatePosition, true)
  window.removeEventListener('resize', updatePosition)
})

onMounted(() => {
  document.addEventListener('pointerdown', onDocumentPointerDown)
  document.addEventListener('keydown', onDocumentKeydown)
})

onUnmounted(() => {
  document.removeEventListener('pointerdown', onDocumentPointerDown)
  document.removeEventListener('keydown', onDocumentKeydown)
  window.removeEventListener('scroll', updatePosition, true)
  window.removeEventListener('resize', updatePosition)
})
</script>

<template>
  <div class="no-drag" @pointerdown.stop>
    <button
      ref="trigger"
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
    <Teleport to="body">
      <div
        v-if="open"
        ref="panel"
        role="menu"
        class="fixed z-[100] min-w-40 overflow-hidden rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] py-1 shadow-lg"
        :style="{ top: `${position.top}px`, left: `${position.left}px` }"
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
    </Teleport>
  </div>
</template>
