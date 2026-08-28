<script setup>
const props = defineProps({
  tasks: { type: Array, required: true },
  readonly: Boolean,
  wipLimit: { type: Number, default: 3 },
})

const emit = defineEmits(['move', 'edit', 'delete'])

const columns = [
  { value: 'TODO', label: 'A fazer', tone: 'todo' },
  { value: 'IN_PROGRESS', label: 'Em progresso', tone: 'progress' },
  { value: 'DONE', label: 'Concluído', tone: 'done' },
]

function tasksByStatus(status) {
  return props.tasks.filter((task) => task.status === status)
}

function dropTask(taskId, status) {
  const task = props.tasks.find((item) => item.id === taskId)
  if (task && task.status !== status) emit('move', task, status)
}
</script>

<template>
  <div class="grid gap-4 xl:grid-cols-3">
    <KanbanColumn
      v-for="column in columns"
      :key="column.value"
      :column="column"
      :tasks="tasksByStatus(column.value)"
      :readonly="readonly"
      :wip-limit="wipLimit"
      @drop-task="dropTask"
      @edit="$emit('edit', $event)"
      @delete="$emit('delete', $event)"
    />
  </div>
</template>
