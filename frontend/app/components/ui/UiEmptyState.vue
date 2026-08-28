<script setup>
defineProps({
  title: { type: String, default: 'Nada por aqui' },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="grid min-h-52 place-items-center rounded-2xl border border-dashed border-[var(--app-border)] p-6 text-center">
    <div class="max-w-sm">
      <div v-if="$slots.icon" class="mx-auto mb-4 grid size-11 place-items-center rounded-xl bg-[var(--app-surface-muted)] text-[var(--app-text)]">
        <slot name="icon" />
      </div>
      <h2 class="font-title text-lg text-[var(--app-text)]">{{ title }}</h2>
      <p v-if="description" class="mt-2 text-sm text-[var(--app-text-muted)]">{{ description }}</p>
      <div v-if="$slots.actions" class="mt-5 flex justify-center">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
