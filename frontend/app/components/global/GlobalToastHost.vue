<script setup>
const { toasts, remove } = useToast()
const timers = new Map()

watch(
  toasts,
  (items) => {
    for (const toast of items) {
      if (toast.duration > 0 && !timers.has(toast.id)) {
        timers.set(
          toast.id,
          window.setTimeout(() => {
            remove(toast.id)
            timers.delete(toast.id)
          }, toast.duration),
        )
      }
    }
  },
  { deep: true },
)

onUnmounted(() => {
  for (const timer of timers.values()) window.clearTimeout(timer)
})
</script>

<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed inset-x-4 top-4 z-[100] flex flex-col items-end gap-2 sm:left-auto sm:w-96"
      aria-live="polite"
      aria-atomic="false"
    >
      <TransitionGroup name="toast">
        <article
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex w-full gap-3 rounded-xl border bg-[var(--app-surface)] p-4 text-[var(--app-text)]"
          :class="{
            'border-[var(--app-border)]': toast.type === 'info',
            'border-green-600': toast.type === 'success',
            'border-amber-500': toast.type === 'warning',
            'border-red-600': toast.type === 'error',
          }"
          :role="toast.type === 'error' ? 'alert' : 'status'"
        >
          <span
            class="mt-1 size-2 shrink-0 rounded-full"
            :class="{
              'bg-[var(--app-text-muted)]': toast.type === 'info',
              'bg-green-600': toast.type === 'success',
              'bg-amber-500': toast.type === 'warning',
              'bg-red-600': toast.type === 'error',
            }"
            aria-hidden="true"
          />
          <div class="min-w-0 flex-1">
            <p v-if="toast.title" class="font-semibold">{{ toast.title }}</p>
            <p class="text-sm text-[var(--app-text-muted)]">{{ toast.message }}</p>
          </div>
          <button
            type="button"
            class="grid size-7 shrink-0 place-items-center rounded-lg text-lg hover:bg-[var(--app-surface-muted)]"
            aria-label="Fechar notificação"
            @click="remove(toast.id)"
          >
            <span aria-hidden="true">×</span>
          </button>
        </article>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 160ms ease,
    transform 160ms ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-0.5rem);
}
</style>
