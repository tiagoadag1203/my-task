<script setup>
const props = defineProps({
  open: Boolean,
  title: { type: String, required: true },
  description: { type: String, default: '' },
})

const emit = defineEmits(['close'])
const model = computed({
  get: () => props.open,
  set: (value) => {
    if (!value) emit('close')
  },
})
</script>

<template>
  <UiModal v-model="model" :title="title" :description="description">
    <slot />
  </UiModal>
</template>
