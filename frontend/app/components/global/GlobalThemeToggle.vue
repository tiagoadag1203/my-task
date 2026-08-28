<script setup>
const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <button
    type="button"
    class="grid size-10 place-items-center rounded-xl border border-[var(--app-border)] text-[var(--app-text)] transition-colors hover:bg-[var(--app-surface-muted)]"
    :aria-label="isDark ? 'Ativar tema claro' : 'Ativar tema escuro'"
    :title="isDark ? 'Tema claro' : 'Tema escuro'"
    @click="toggleTheme"
  >
    <svg v-if="isDark" aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="4" />
      <path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
    </svg>
    <svg v-else aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" />
    </svg>
  </button>
</template>
