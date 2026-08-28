<script setup>
const props = defineProps({
  open: Boolean,
  project: { type: Object, default: null },
  submitting: Boolean,
  error: { type: String, default: '' },
})

defineEmits(['close', 'submit'])
const form = reactive({ name: '', description: '' })

watch([() => props.open, () => props.project], () => {
  form.name = props.project?.name ?? ''
  form.description = props.project?.description ?? ''
}, { immediate: true })
</script>

<template>
  <DashboardBaseModal
    :open="open"
    :title="project ? 'Editar projeto' : 'Novo projeto'"
    @close="$emit('close')"
  >
    <form
      class="space-y-4"
      @submit.prevent="$emit('submit', { name: form.name.trim(), description: form.description.trim() || null })"
    >
      <UiInput v-model="form.name" label="Nome" required maxlength="255" />
      <UiTextarea v-model="form.description" label="Descrição" :rows="4" />
      <p v-if="error" role="alert" class="text-sm font-semibold text-red-700 dark:text-red-300">
        {{ error }}
      </p>
      <div class="flex justify-end gap-3">
        <UiButton variant="ghost" @click="$emit('close')">Cancelar</UiButton>
        <UiButton type="submit" :loading="submitting" :disabled="submitting || !form.name.trim()">
          Salvar
        </UiButton>
      </div>
    </form>
  </DashboardBaseModal>
</template>
