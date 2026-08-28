<script setup>
const props = defineProps({
  to: { type: [String, Object], default: null },
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' },
  size: { type: String, default: 'md' },
  loading: Boolean,
  disabled: Boolean,
  block: Boolean,
})

const component = computed(() => (props.to ? resolveComponent('NuxtLink') : 'button'))

const classes = computed(() => {
  if (props.variant === 'cta') {
    return [
      'inline-flex items-center justify-center gap-2 rounded-full border-2 border-black bg-black px-6 py-3.5 font-semibold text-white shadow-accent transition-all hover:-translate-y-1 hover:shadow-accent-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-black disabled:pointer-events-none disabled:opacity-60 disabled:hover:translate-y-0',
      props.block ? 'w-full' : '',
    ]
  }

  return [
    'inline-flex items-center justify-center gap-2 rounded-xl border font-semibold transition-colors focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
    {
      'border-black bg-black text-white hover:bg-neutral-800 dark:border-white dark:bg-white dark:text-black dark:hover:bg-neutral-200':
        props.variant === 'primary',
      'border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] hover:bg-[var(--app-surface-muted)]':
        props.variant === 'secondary',
      'border-transparent bg-transparent text-[var(--app-text)] hover:bg-[var(--app-surface-muted)]':
        props.variant === 'ghost',
      'border-[var(--app-accent)] bg-[var(--app-accent)] text-[var(--app-accent-contrast)] hover:bg-[#00e6ac]':
        props.variant === 'accent',
      'border-red-600 bg-red-600 text-white hover:bg-red-700': props.variant === 'danger',
      'size-9 px-0': props.size === 'icon',
      'h-9 px-3 text-sm': props.size === 'sm',
      'h-11 px-4 text-sm': props.size === 'md',
      'h-13 px-6 text-base': props.size === 'lg',
      'w-full': props.block,
    },
  ]
})
</script>

<template>
  <component
    :is="component"
    :to="to || undefined"
    :type="to ? undefined : type"
    :disabled="to ? undefined : disabled || loading"
    :aria-disabled="disabled || loading || undefined"
    :aria-busy="loading || undefined"
    :class="classes"
  >
    <UiSpinner v-if="loading" class="size-4" />
    <slot name="leading" />
    <slot />
    <slot name="trailing" />
  </component>
</template>
