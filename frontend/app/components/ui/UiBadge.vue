<script setup>
defineProps({
  tone: { type: String, default: 'neutral' },
  dot: Boolean,
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
    :class="{
      'border-[var(--app-border)] bg-[var(--app-surface-muted)] text-[var(--app-text)]': tone === 'neutral',
      'border-[var(--app-accent)] bg-[var(--app-accent)] text-[var(--app-accent-contrast)]': tone === 'accent' || tone === 'done',
      'border-orange-300 bg-orange-100 text-orange-800 dark:border-orange-800 dark:bg-orange-950 dark:text-orange-200':
        tone === 'todo',
      'border-blue-300 bg-blue-100 text-blue-800 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200':
        tone === 'progress',
      'border-green-700/30 bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300': tone === 'success',
      'border-amber-700/30 bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300': tone === 'warning',
      'border-red-700/30 bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': tone === 'danger',
    }"
  >
    <span v-if="dot" class="size-1.5 rounded-full bg-current" aria-hidden="true" />
    <slot />
  </span>
</template>
