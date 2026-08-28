<script setup>
import { callRepository, errorMessage, unwrapList } from './api'

const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const tasksRepository = $api.tasks
const editingTask = ref(null)
const modalOpen = ref(false)
const submitting = ref(false)
const feedback = ref('')

const { data: tasks, status, error, refresh } = await useAsyncData(
  'general-kanban',
  async () => unwrapList(await callRepository(tasksRepository, ['kanban', 'listKanban', 'kanbanIndex', 'indexKanban'])),
  { default: () => [] },
)

function openCreate() {
  editingTask.value = null
  feedback.value = ''
  modalOpen.value = true
}

function openEdit(task) {
  editingTask.value = task
  feedback.value = ''
  modalOpen.value = true
}

async function saveTask(payload) {
  submitting.value = true
  feedback.value = ''
  try {
    if (editingTask.value) {
      await callRepository(
        tasksRepository,
        ['update'],
        editingTask.value.id,
        { ...payload, status: editingTask.value.status },
      )
    } else {
      await callRepository(tasksRepository, ['createKanban', 'kanbanStore', 'storeKanban'], payload)
    }
    modalOpen.value = false
    await refresh()
    toast.success(editingTask.value ? 'Tarefa atualizada.' : 'Tarefa criada.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  } finally {
    submitting.value = false
  }
}

async function moveTask(task, statusValue) {
  if (statusValue === 'IN_PROGRESS' && tasks.value.filter((item) => item.status === 'IN_PROGRESS').length >= 3) {
    feedback.value = 'O limite de trabalho em progresso é de 3 tarefas. Conclua uma antes de iniciar outra.'
    return
  }

  const previous = task.status
  task.status = statusValue
  feedback.value = ''
  try {
    await callRepository(tasksRepository, ['update'], task.id, {
      title: task.title,
      description: task.description ?? null,
      status: statusValue,
    })
    toast.success('Status da tarefa atualizado.')
  } catch (exception) {
    task.status = previous
    feedback.value = errorMessage(exception)
    toast.warning(feedback.value)
  }
}

async function removeTask(task) {
  const confirmed = await ask({
    title: 'Excluir tarefa',
    message: `Excluir a tarefa “${task.title}”? Esta ação não pode ser desfeita.`,
    confirmLabel: 'Excluir',
  })
  if (!confirmed) return
  try {
    await callRepository(tasksRepository, ['remove', 'destroy', 'delete'], task.id)
    await refresh()
    toast.success('Tarefa excluída.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}
</script>

<template>
  <section aria-labelledby="kanban-title">
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">Fluxo pessoal</p>
        <h2 id="kanban-title" class="font-title text-3xl">Minhas tarefas</h2>
        <p class="mt-1 text-neutral-600 dark:text-neutral-300">Mantenha no máximo três tarefas em andamento.</p>
      </div>
      <UiButton @click="openCreate">+ Nova tarefa</UiButton>
    </header>

    <p v-if="feedback" role="status" class="mb-4 rounded-xl bg-amber-100 px-4 py-3 text-sm font-semibold text-amber-950">
      {{ feedback }}
    </p>
    <div v-if="status === 'pending'" class="grid gap-4 xl:grid-cols-3" aria-label="Carregando tarefas">
      <div v-for="index in 3" :key="index" class="h-72 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" />
    </div>
    <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900">
      <p>Não foi possível carregar o quadro.</p>
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </div>
    <KanbanBoard v-else :tasks="tasks" @move="moveTask" @edit="openEdit" @delete="removeTask" />

    <KanbanTaskFormModal
      :open="modalOpen"
      :task="editingTask"
      :submitting="submitting"
      :error="feedback"
      @close="modalOpen = false"
      @submit="saveTask"
    />
  </section>
</template>
