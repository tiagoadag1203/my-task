<script setup>
defineProps({
  label: { type: String, default: 'Carregando' },
})
</script>

<template>
  <svg
    class="animate-spin"
    viewBox="0 0 24 24"
    fill="none"
    role="status"
    :aria-label="label"
  >
    <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
    <path
      class="opacity-90"
      d="M21 12a9 9 0 0 0-9-9"
      stroke="currentColor"
      stroke-width="3"
      stroke-linecap="round"
    />
  </svg>
</template>
