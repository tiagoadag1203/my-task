<script setup>
import { callRepository, errorMessage, unwrapList } from '../dashboard/api'

const props = defineProps({
  sprint: { type: Object, required: true },
  migrationTargets: { type: Array, default: () => [] },
})
const emit = defineEmits(['changed'])
const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const modalOpen = ref(false)
const editingTask = ref(null)
const submitting = ref(false)
const feedback = ref('')
const readonly = computed(() => props.sprint.status !== 'ACTIVE')
defineExpose({ refresh: () => refresh() })

const { data: tasks, status, error, refresh } = await useAsyncData(
  () => `sprint-${props.sprint.id}-tasks`,
  async () => unwrapList(await callRepository($api.tasks, ['sprint', 'listSprint', 'sprintTasks'], props.sprint.id)),
  { default: () => [] },
)

async function move(task, nextStatus) {
  if (readonly.value) return
  if (nextStatus === 'IN_PROGRESS' && tasks.value.filter((item) => item.status === 'IN_PROGRESS').length >= 3) {
    feedback.value = 'Esta sprint já possui 3 tarefas em andamento. Conclua uma antes de iniciar outra.'
    return
  }
  const previous = task.status
  task.status = nextStatus
  try {
    await callRepository($api.tasks, ['update'], task.id, {
      title: task.title,
      description: task.description ?? null,
      status: nextStatus,
    })
    emit('changed')
    toast.success('Status da tarefa atualizado.')
  } catch (exception) {
    task.status = previous
    feedback.value = errorMessage(exception)
  }
}

function edit(task) {
  if (readonly.value) return
  editingTask.value = task
  feedback.value = ''
  modalOpen.value = true
}

async function save(payload) {
  submitting.value = true
  try {
    await callRepository($api.tasks, ['update'], editingTask.value.id, {
      ...payload,
      status: editingTask.value.status,
    })
    modalOpen.value = false
    await refresh()
    toast.success('Tarefa atualizada.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  } finally {
    submitting.value = false
  }
}

async function remove(task) {
  if (readonly.value) return
  const confirmed = await ask({
    title: 'Excluir tarefa',
    message: `Excluir “${task.title}”? Esta ação não pode ser desfeita.`,
    confirmLabel: 'Excluir',
  })
  if (!confirmed) return
  try {
    await callRepository($api.tasks, ['remove', 'destroy', 'delete'], task.id)
    await refresh()
    emit('changed')
    toast.success('Tarefa excluída.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}

async function migrate(task, sprintId) {
  try {
    await callRepository($api.tasks, ['migrate'], task.id, Number(sprintId))
    await refresh()
    emit('changed')
    toast.success('Tarefa migrada para a sprint ativa.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}


const { isFirstLoading, showError } = useFirstLoad(status, error)
</script>

<template>
  <section>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div>
        <UiBadge>{{ sprint.status }}</UiBadge>
        <p v-if="sprint.goal" class="mt-2 text-sm text-[var(--app-text-muted)]">{{ sprint.goal }}</p>
      </div>
      <p v-if="readonly" class="text-sm font-semibold text-neutral-500">
        {{ sprint.status === 'PLANNING' ? 'O quadro será liberado quando a sprint for ativada.' : 'Sprint finalizada: quadro somente leitura.' }}
      </p>
    </div>
    <p v-if="feedback" role="status" class="mb-4 rounded-xl bg-amber-100 p-3 text-sm font-semibold text-amber-950">{{ feedback }}</p>
    <p v-if="isFirstLoading" class="animate-pulse rounded-2xl bg-[var(--app-surface-muted)] p-10">Carregando tarefas…</p>
    <p v-else-if="showError" class="rounded-xl bg-red-50 p-4 text-red-900">
      Erro ao carregar tarefas.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </p>
    <KanbanBoard v-else :tasks="tasks" :readonly="readonly" @move="move" @edit="edit" @delete="remove" />

    <div v-if="sprint.status === 'FINISHED' && migrationTargets.length" class="mt-5 rounded-2xl border border-[var(--app-border)] p-4">
      <h4 class="font-title text-lg">Pendências da sprint</h4>
      <div v-for="task in tasks.filter((item) => item.status !== 'DONE')" :key="task.id" class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <span>{{ task.title }}</span>
        <UiSelect
          :model-value="''"
          class="max-w-xs"
          placeholder="Migrar para…"
          :options="migrationTargets.map((target) => ({ value: target.id, label: target.name }))"
          @update:model-value="(sprintId) => migrate(task, sprintId)"
        />
      </div>
    </div>
    <KanbanTaskFormModal :open="modalOpen" :task="editingTask" :submitting="submitting" :error="feedback" @close="modalOpen = false" @submit="save" />
  </section>
</template>
