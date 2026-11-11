<script setup>
import { callRepository, errorMessage, unwrapList } from '../dashboard/api'

const props = defineProps({
  projectId: { type: Number, required: true },
  sprints: { type: Array, default: () => [] },
})
const emit = defineEmits(['changed'])
const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const modalOpen = ref(false)
const editingTask = ref(null)
const submitting = ref(false)
const feedback = ref('')
defineExpose({ refresh: () => refresh() })

const { data: tasks, status, error, refresh } = await useAsyncData(
  () => `project-${props.projectId}-backlog`,
  async () => unwrapList(await callRepository($api.tasks, ['backlog', 'listBacklog', 'backlogIndex'], props.projectId)),
  { default: () => [] },
)
const destinations = computed(() => props.sprints.filter((sprint) => ['PLANNING', 'ACTIVE'].includes(sprint.status)))

function openForm(task = null) {
  editingTask.value = task
  feedback.value = ''
  modalOpen.value = true
}

async function save(payload) {
  submitting.value = true
  try {
    if (editingTask.value) {
      await callRepository($api.tasks, ['update'], editingTask.value.id, { ...payload, status: 'BACKLOG' })
    } else {
      await callRepository($api.tasks, ['createBacklog', 'backlogStore', 'storeBacklog'], props.projectId, payload)
    }
    modalOpen.value = false
    await refresh()
    emit('changed')
    toast.success(editingTask.value ? 'Tarefa atualizada.' : 'Tarefa adicionada ao backlog.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  } finally {
    submitting.value = false
  }
}

async function migrate(task, sprintId) {
  if (!sprintId) return
  try {
    await callRepository($api.tasks, ['migrate'], task.id, Number(sprintId))
    await refresh()
    emit('changed')
    toast.success('Tarefa alocada à sprint.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}

async function remove(task) {
  const confirmed = await ask({
    title: 'Excluir tarefa do backlog',
    message: `Excluir a tarefa “${task.title}”? Esta ação não pode ser desfeita.`,
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


const { isFirstLoading, showError } = useFirstLoad(status, error)
</script>

<template>
  <UiCard as="section" aria-labelledby="backlog-title">
    <header class="mb-4 flex items-center justify-between gap-3">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-[var(--app-text-muted)]">
          Ideias e entregas
        </p>
        <h3 id="backlog-title" class="font-title text-2xl">Backlog</h3>
      </div>
      <UiButton size="sm" @click="openForm()">+ Tarefa</UiButton>
    </header>
    <p v-if="feedback" role="status" class="mb-3 rounded-xl bg-amber-100 p-3 text-sm text-amber-950">
      {{ feedback }}
    </p>
    <p v-if="isFirstLoading" class="animate-pulse rounded-xl bg-[var(--app-surface-muted)] p-6">
      Carregando backlog…
    </p>
    <p v-else-if="showError" class="rounded-xl bg-red-50 p-4 text-red-900">
      Erro ao carregar.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </p>
    <UiEmptyState
      v-else-if="!tasks.length"
      title="Nenhuma tarefa aguardando planejamento."
    />
    <ul v-else class="space-y-3">
      <li
        v-for="task in tasks"
        :key="task.id"
        class="rounded-2xl border border-[var(--app-border)] p-4"
      >
        <div class="flex items-start justify-between gap-3">
          <div>
            <h4 class="font-semibold">{{ task.title }}</h4>
            <p v-if="task.description" class="mt-1 text-sm text-[var(--app-text-muted)]">
              {{ task.description }}
            </p>
          </div>
          <UiMenu
            :label="`Ações da tarefa ${task.title}`"
            :items="[
              { label: 'Editar', onSelect: () => openForm(task) },
              { label: 'Excluir', tone: 'danger', onSelect: () => remove(task) },
            ]"
          />
        </div>
        <UiSelect
          v-if="destinations.length"
          :model-value="''"
          class="mt-3"
          label="Alocar em sprint"
          placeholder="Selecione…"
          :options="destinations.map((sprint) => ({
            value: sprint.id,
            label: `${sprint.name} · ${sprint.status}`,
          }))"
          @update:model-value="(sprintId) => migrate(task, sprintId)"
        />
      </li>
    </ul>
    <KanbanTaskFormModal
      :open="modalOpen"
      :task="editingTask"
      :submitting="submitting"
      :error="feedback"
      @close="modalOpen = false"
      @submit="save"
    />
  </UiCard>
</template>
