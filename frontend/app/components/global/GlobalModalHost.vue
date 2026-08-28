<script setup>
const { modal, close } = useModal()
const isOpen = computed({
  get: () => modal.value.open,
  set: (value) => {
    if (!value) close()
  },
})
</script>

<template>
  <UiModal
    v-model="isOpen"
    :title="modal.title"
    :description="modal.description"
    :size="modal.size"
  >
    <p class="text-sm leading-6 text-[var(--app-text-muted)]">{{ modal.message }}</p>
    <template #footer>
      <UiButton variant="accent" @click="close">Entendi</UiButton>
    </template>
  </UiModal>
</template>
