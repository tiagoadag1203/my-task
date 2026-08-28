<script setup>
const props = defineProps({
  open: Boolean,
  task: { type: Object, default: null },
  submitting: Boolean,
  error: { type: String, default: '' },
})

const emit = defineEmits(['close', 'submit'])
const form = reactive({ title: '', description: '' })

watch(
  [() => props.open, () => props.task],
  () => {
    form.title = props.task?.title ?? ''
    form.description = props.task?.description ?? ''
  },
  { immediate: true },
)

function submit() {
  if (form.title.trim()) {
    emit('submit', { title: form.title.trim(), description: form.description.trim() || null })
  }
}
</script>

<template>
  <DashboardBaseModal
    :open="open"
    :title="task ? 'Editar tarefa' : 'Nova tarefa'"
    @close="$emit('close')"
  >
    <form class="space-y-4" @submit.prevent="submit">
      <UiInput
        v-model="form.title"
        label="Título"
        required
        maxlength="255"
      />
      <UiTextarea v-model="form.description" label="Descrição" :rows="4" />
      <p v-if="error" role="alert" class="text-sm font-semibold text-red-700 dark:text-red-300">
        {{ error }}
      </p>
      <div class="flex justify-end gap-3">
        <UiButton variant="ghost" @click="$emit('close')">Cancelar</UiButton>
        <UiButton type="submit" :loading="submitting" :disabled="submitting">
          Salvar
        </UiButton>
      </div>
    </form>
  </DashboardBaseModal>
</template>
