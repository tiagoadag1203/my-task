<script setup>
import { VueDraggable } from 'vue-draggable-plus'

defineOptions({ name: 'KanbanColumn' })

const props = defineProps({
  column: { type: Object, required: true },
  tasks: { type: Array, required: true },
  readonly: Boolean,
  wipLimit: { type: Number, default: 3 },
})

const emit = defineEmits(['drop-task', 'edit', 'delete'])
const items = ref([])

watch(
  () => props.tasks,
  (tasks) => {
    items.value = [...tasks]
  },
  { immediate: true, deep: true },
)

function onAdd(event) {
  const id = Number(event.item?.dataset?.taskId)
  if (id) emit('drop-task', id, props.column.value)
}
</script>

<template>
  <section class="kanban-column" :aria-label="column.label">
    <header class="mb-3 flex items-center justify-between gap-2 px-1">
      <h3 class="kanban-column__title">{{ column.label }}</h3>
      <KanbanWipIndicator
        v-if="column.value === 'IN_PROGRESS'"
        :count="tasks.length"
        :limit="wipLimit"
      />
      <UiBadge v-else>{{ tasks.length }}</UiBadge>
    </header>
    <VueDraggable
      v-model="items"
      class="min-h-40 space-y-3"
      :group="{ name: 'mytask-kanban' }"
      :disabled="readonly"
      :animation="150"
      filter=".no-drag"
      ghost-class="opacity-40"
      @add="onAdd"
    >
      <KanbanTaskCard
        v-for="task in items"
        :key="`${task.id}-${column.value}`"
        :data-task-id="task.id"
        :task="task"
        :status="column.value"
        :readonly="readonly"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
      <p
        v-if="items.length === 0"
        class="rounded-2xl border border-dashed border-[var(--app-border)] p-5 text-center text-sm text-[var(--app-text-muted)]"
      >
        Solte uma tarefa aqui
      </p>
    </VueDraggable>
  </section>
</template>
