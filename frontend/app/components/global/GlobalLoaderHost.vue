<script setup>
const { isLoading, message } = useLoader()
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isLoading"
      class="fixed inset-0 z-[110] grid place-items-center bg-black/65 p-4"
      role="status"
      aria-live="assertive"
      :aria-label="message"
    >
      <div class="flex items-center gap-3 rounded-xl border border-[var(--app-border)] bg-[var(--app-surface)] px-5 py-4 text-[var(--app-text)]">
        <UiSpinner class="size-5 text-[var(--app-accent)]" />
        <span class="text-sm font-semibold">{{ message }}</span>
      </div>
    </div>
  </Teleport>
</template>
