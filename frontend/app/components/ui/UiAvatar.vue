<script setup>
const props = defineProps({
  src: { type: String, default: '' },
  alt: { type: String, default: '' },
  name: { type: String, default: '' },
  size: { type: String, default: 'md' },
})

const initials = computed(() =>
  props.name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0))
    .join('')
    .toUpperCase(),
)
</script>

<template>
  <span
    class="inline-grid shrink-0 place-items-center overflow-hidden rounded-full border border-[var(--app-border)] bg-[var(--app-surface-muted)] font-semibold text-[var(--app-text)]"
    :class="{ 'size-8 text-xs': size === 'sm', 'size-10 text-sm': size === 'md', 'size-12 text-base': size === 'lg' }"
  >
    <img v-if="src" :src="src" :alt="alt || name" class="size-full object-cover">
    <span v-else aria-hidden="true">{{ initials || '?' }}</span>
    <span v-if="!src" class="sr-only">{{ name || 'Usuário' }}</span>
  </span>
</template>
