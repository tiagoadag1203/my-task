<script setup>
const props = defineProps({
  open: Boolean,
  sprint: { type: Object, default: null },
  submitting: Boolean,
  error: { type: String, default: '' },
})

defineEmits(['close', 'submit'])
const form = reactive({ name: '', goal: '', started_at: '', ended_at: '' })

watch([() => props.open, () => props.sprint], () => {
  Object.assign(form, {
    name: props.sprint?.name ?? '',
    goal: props.sprint?.goal ?? '',
    started_at: props.sprint?.started_at ?? '',
    ended_at: props.sprint?.ended_at ?? '',
  })
}, { immediate: true })
</script>

<template>
  <DashboardBaseModal
    :open="open"
    :title="sprint ? 'Editar sprint' : 'Nova sprint'"
    @close="$emit('close')"
  >
    <form
      class="space-y-4"
      @submit.prevent="$emit('submit', {
        name: form.name.trim(),
        goal: form.goal.trim() || null,
        started_at: form.started_at,
        ended_at: form.ended_at,
      })"
    >
      <UiInput v-model="form.name" label="Nome" required maxlength="255" />
      <UiTextarea v-model="form.goal" label="Objetivo" :rows="3" />
      <div class="grid gap-4 sm:grid-cols-2">
        <UiInput v-model="form.started_at" label="Início" type="date" required />
        <UiInput
          v-model="form.ended_at"
          label="Fim"
          type="date"
          required
          :min="form.started_at"
        />
      </div>
      <p v-if="error" role="alert" class="text-sm font-semibold text-red-700 dark:text-red-300">
        {{ error }}
      </p>
      <div class="flex justify-end gap-3">
        <UiButton variant="ghost" @click="$emit('close')">Cancelar</UiButton>
        <UiButton
          type="submit"
          :loading="submitting"
          :disabled="submitting || !form.name || !form.started_at || !form.ended_at"
        >
          Salvar
        </UiButton>
      </div>
    </form>
  </DashboardBaseModal>
</template>
