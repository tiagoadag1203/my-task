<script setup>
const open = defineModel({ type: Boolean, default: false })
const props = defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  size: { type: String, default: 'md' },
  closeLabel: { type: String, default: 'Fechar diálogo' },
  persistent: Boolean,
})

const titleId = useId()
const dialog = ref(null)
let previousFocus

const close = () => {
  if (!props.persistent) open.value = false
}

const onKeydown = (event) => {
  if (event.key === 'Escape') close()
  if (event.key !== 'Tab') return

  const focusable = dialog.value?.querySelectorAll(
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
  )
  if (!focusable?.length) {
    event.preventDefault()
    return
  }

  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

watch(open, async (isOpen) => {
  if (!import.meta.client) return

  if (isOpen) {
    previousFocus = document.activeElement
    document.body.style.overflow = 'hidden'
    await nextTick()
    dialog.value?.focus()
  } else {
    document.body.style.overflow = ''
    previousFocus?.focus?.()
  }
})

onUnmounted(() => {
  if (import.meta.client) document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[80] grid place-items-center bg-black/60 p-4"
      @keydown="onKeydown"
      @mousedown.self="close"
    >
      <section
        ref="dialog"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="title ? titleId : undefined"
        tabindex="-1"
        class="max-h-[calc(100dvh-2rem)] w-full overflow-y-auto rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-5 text-[var(--app-text)] outline-none sm:p-6"
        :class="{ 'max-w-sm': size === 'sm', 'max-w-lg': size === 'md', 'max-w-2xl': size === 'lg', 'max-w-4xl': size === 'xl' }"
      >
        <header class="flex items-start justify-between gap-5">
          <div>
            <h2 v-if="title" :id="titleId" class="font-title text-xl">{{ title }}</h2>
            <p v-if="description" class="mt-1 text-sm text-[var(--app-text-muted)]">{{ description }}</p>
          </div>
          <button
            type="button"
            class="grid size-9 shrink-0 place-items-center rounded-lg text-xl text-[var(--app-text-muted)] hover:bg-[var(--app-surface-muted)] hover:text-[var(--app-text)]"
            :aria-label="closeLabel"
            @click="close"
          >
            <span aria-hidden="true">×</span>
          </button>
        </header>
        <div class="mt-5">
          <slot />
        </div>
        <footer v-if="$slots.footer" class="mt-6 flex flex-wrap justify-end gap-3">
          <slot name="footer" />
        </footer>
      </section>
    </div>
  </Teleport>
</template>
