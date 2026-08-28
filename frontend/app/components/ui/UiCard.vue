<script setup>
defineProps({
  as: { type: String, default: 'section' },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  padded: { type: Boolean, default: true },
  accent: Boolean,
})
</script>

<template>
  <component
    :is="as"
    class="app-surface overflow-hidden"
    :class="{ 'p-5 sm:p-6': padded, 'border-l-4 !border-l-[var(--app-accent)]': accent }"
  >
    <header v-if="title || description || $slots.header || $slots.actions" class="mb-5 flex items-start justify-between gap-4">
      <slot name="header">
        <div>
          <h2 v-if="title" class="font-title text-lg text-[var(--app-text)]">{{ title }}</h2>
          <p v-if="description" class="mt-1 text-sm text-[var(--app-text-muted)]">{{ description }}</p>
        </div>
      </slot>
      <slot name="actions" />
    </header>
    <slot />
    <footer v-if="$slots.footer" class="mt-5 border-t border-[var(--app-border)] pt-4">
      <slot name="footer" />
    </footer>
  </component>
</template>
