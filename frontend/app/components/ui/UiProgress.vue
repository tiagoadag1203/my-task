<script setup>
const props = defineProps({
  value: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  label: { type: String, default: 'Progresso' },
  showValue: Boolean,
})

const percentage = computed(() =>
  Math.min(100, Math.max(0, Math.round((props.value / Math.max(props.max, 1)) * 100))),
)
</script>

<template>
  <div>
    <div v-if="showValue" class="mb-1.5 flex justify-between gap-4 text-xs">
      <span class="font-medium text-[var(--app-text)]">{{ label }}</span>
      <span class="text-[var(--app-text-muted)]">{{ percentage }}%</span>
    </div>
    <div
      class="h-2 overflow-hidden rounded-full bg-[var(--app-surface-muted)]"
      role="progressbar"
      :aria-label="label"
      :aria-valuenow="value"
      aria-valuemin="0"
      :aria-valuemax="max"
    >
      <div class="h-full rounded-full bg-[var(--app-accent)] transition-[width]" :style="{ width: `${percentage}%` }" />
    </div>
  </div>
</template>
