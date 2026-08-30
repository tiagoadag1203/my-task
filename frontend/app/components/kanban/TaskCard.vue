<script setup>
const props = defineProps({
  task: { type: Object, required: true },
  status: { type: String, default: '' },
  readonly: Boolean,
})

const emit = defineEmits(['edit', 'delete'])
const visualStatus = computed(() => props.status || props.task.status)

const menuItems = computed(() => [
  { label: 'Editar', onSelect: () => emit('edit', props.task) },
  { label: 'Excluir', tone: 'danger', onSelect: () => emit('delete', props.task) },
])
</script>

<template>
  <article
    class="task-card group"
    :data-status="visualStatus"
  >
    <div class="flex items-start gap-2">
      <h4 class="min-w-0 flex-1 font-semibold leading-snug">{{ task.title }}</h4>
      <UiMenu
        v-if="!readonly"
        :label="`Ações da tarefa ${task.title}`"
        :items="menuItems"
      />
    </div>
    <p v-if="task.description" class="mt-2 line-clamp-3 text-sm text-[var(--app-text-muted)]">
      {{ task.description }}
    </p>
  </article>
</template>
