<script setup>
const { confirmation, confirm, cancel } = useConfirm()
const isOpen = computed({
  get: () => confirmation.value.open,
  set: (value) => {
    if (!value) cancel()
  },
})
</script>

<template>
  <UiModal v-model="isOpen" :title="confirmation.title" size="sm">
    <p class="text-sm leading-6 text-[var(--app-text-muted)]">{{ confirmation.message }}</p>
    <template #footer>
      <UiButton variant="secondary" @click="cancel">{{ confirmation.cancelLabel }}</UiButton>
      <UiButton :variant="confirmation.tone === 'danger' ? 'danger' : 'accent'" @click="confirm">
        {{ confirmation.confirmLabel }}
      </UiButton>
    </template>
  </UiModal>
</template>
