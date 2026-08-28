<script setup>
defineProps({
  count: { type: Number, required: true },
  limit: { type: Number, default: 3 },
})
</script>

<template>
  <span
    class="rounded-full px-2.5 py-1 text-xs font-bold"
    :class="count >= limit ? 'bg-amber-100 text-amber-900' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200'"
    :aria-label="`${count} de ${limit} tarefas em andamento`"
  >
    WIP {{ count }}/{{ limit }}
  </span>
</template>
