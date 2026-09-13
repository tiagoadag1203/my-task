<script setup>
import { callRepository, errorMessage, formatDate, unwrapList } from '../dashboard/api'

const props = defineProps({
  projectId: { type: Number, required: true },
})
const emit = defineEmits(['updated'])
const route = useRoute()
const router = useRouter()
const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const selectedId = computed(() => Number(route.query.sprint) || null)
const modalOpen = ref(false)
const editingSprint = ref(null)
const submitting = ref(false)
const feedback = ref('')
defineExpose({ refresh: () => refresh() })

const { data: sprints, status, error, refresh } = await useAsyncData(
  () => `project-${props.projectId}-sprints`,
  async () => unwrapList(await callRepository($api.sprints, ['list', 'index'], props.projectId)),
  { default: () => [] },
)
const selectedSprint = computed(() =>
  sprints.value.find((sprint) => sprint.id === selectedId.value)
  ?? sprints.value.find((sprint) => sprint.status === 'ACTIVE')
  ?? sprints.value[0],
)
const migrationTargets = computed(() => sprints.value.filter((sprint) => ['PLANNING', 'ACTIVE'].includes(sprint.status)))

function sprintStatusLabel(status) {
  return {
    ACTIVE: 'Ativa',
    PLANNING: 'Em planejamento',
    FINISHED: 'Encerrada',
  }[status] ?? status
}

function sprintCardClass(sprint) {
  if (sprint.status === 'ACTIVE') {
    return 'border-2 border-[var(--app-accent)] bg-[color-mix(in_srgb,var(--app-accent)_10%,var(--app-surface))] shadow-accent-sm'
  }

  if (selectedSprint.value?.id === sprint.id) {
    return 'border-[var(--app-text)]'
  }

  return 'opacity-75'
}

function select(sprint) {
  router.push({ query: { ...route.query, sprint: sprint.id } })
}

function openForm(sprint = null) {
  editingSprint.value = sprint
  feedback.value = ''
  modalOpen.value = true
}

async function save(payload) {
  submitting.value = true
  try {
    if (editingSprint.value) {
      await callRepository($api.sprints, ['update'], editingSprint.value.id, payload)
    } else {
      await callRepository($api.sprints, ['create', 'store'], props.projectId, payload)
    }
    modalOpen.value = false
    await refresh()
    emit('updated', sprints.value)
    toast.success(editingSprint.value ? 'Sprint atualizada.' : 'Sprint criada.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  } finally {
    submitting.value = false
  }
}

function sprintMenu(sprint) {
  if (sprint.status === 'PLANNING') {
    return [
      { label: 'Editar', onSelect: () => openForm(sprint) },
      { label: 'Ativar', onSelect: () => changeStatus(sprint, 'ACTIVE') },
      { label: 'Excluir', tone: 'danger', onSelect: () => remove(sprint) },
    ]
  }

  if (sprint.status === 'ACTIVE') {
    return [{ label: 'Finalizar', onSelect: () => changeStatus(sprint, 'FINISHED') }]
  }

  return []
}

async function changeStatus(sprint, nextStatus) {
  const action = nextStatus === 'ACTIVE' ? 'ativar' : 'finalizar'
  const confirmed = await ask({
    title: `${nextStatus === 'ACTIVE' ? 'Ativar' : 'Finalizar'} sprint`,
    message: `Deseja ${action} a sprint “${sprint.name}”?`,
    confirmLabel: nextStatus === 'ACTIVE' ? 'Ativar' : 'Finalizar',
    tone: 'accent',
  })
  if (!confirmed) return
  try {
    await callRepository($api.sprints, ['updateStatus', 'changeStatus'], sprint.id, nextStatus)
    await refresh()
    emit('updated', sprints.value)
    toast.success(nextStatus === 'ACTIVE' ? 'Sprint ativada.' : 'Sprint finalizada.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}

async function remove(sprint) {
  const confirmed = await ask({
    title: 'Excluir sprint',
    message: `Excluir “${sprint.name}”? As tarefas retornarão ao backlog.`,
    confirmLabel: 'Excluir sprint',
  })
  if (!confirmed) return
  try {
    await callRepository($api.sprints, ['remove', 'destroy', 'delete'], sprint.id)
    await refresh()
    emit('updated', sprints.value)
    toast.success('Sprint excluída; as tarefas retornaram ao backlog.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}

let refreshTimer
onMounted(() => {
  refreshTimer = window.setInterval(() => refresh(), 60_000)
  document.addEventListener('visibilitychange', onVisibility)
})
onBeforeUnmount(() => {
  window.clearInterval(refreshTimer)
  document.removeEventListener('visibilitychange', onVisibility)
})
function onVisibility() {
  if (document.visibilityState === 'visible') refresh()
}

watch(sprints, (value) => emit('updated', value), { immediate: true })
</script>

<template>
  <section aria-labelledby="sprints-title">
    <header class="mb-4 flex items-center justify-between gap-3">
      <div><p class="text-xs font-bold uppercase tracking-wider text-neutral-500">Ciclos de entrega</p><h3 id="sprints-title" class="font-title text-2xl">Sprints</h3></div>
      <UiButton size="sm" @click="openForm()">+ Sprint</UiButton>
    </header>
    <p v-if="feedback" role="status" class="mb-3 rounded-xl bg-amber-100 p-3 text-sm font-semibold text-amber-950">{{ feedback }}</p>
    <p v-if="status === 'pending'" class="animate-pulse rounded-2xl bg-[var(--app-surface-muted)] p-8">Carregando sprints…</p>
    <p v-else-if="error" class="rounded-xl bg-red-50 p-4 text-red-900">
      Erro ao carregar sprints.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </p>
    <div v-else>
      <div class="mb-5 flex gap-3 overflow-x-auto pb-2">
        <article
          v-for="sprint in sprints"
          :key="sprint.id"
          class="app-card relative min-w-64 cursor-pointer p-5 transition-all hover:border-[var(--app-text)] hover:opacity-100"
          :class="sprintCardClass(sprint)"
          role="button"
          :aria-label="`Selecionar sprint ${sprint.name}`"
          tabindex="0"
          @click="select(sprint)"
          @keydown.enter.prevent="select(sprint)"
          @keydown.space.prevent="select(sprint)"
        >
          <div class="flex items-start gap-2">
            <div class="min-w-0 flex-1">
              <UiBadge :tone="sprint.status === 'ACTIVE' ? 'accent' : 'neutral'" :dot="sprint.status === 'ACTIVE'">
                {{ sprintStatusLabel(sprint.status) }}
              </UiBadge>
              <h4 class="mt-2 font-title text-lg">{{ sprint.name }}</h4>
              <p class="mt-1 text-xs text-[var(--app-text-muted)]">
                {{ formatDate(sprint.started_at) }} – {{ formatDate(sprint.ended_at) }}
              </p>
            </div>
            <UiMenu
              v-if="sprintMenu(sprint).length"
              :label="`Ações da sprint ${sprint.name}`"
              :items="sprintMenu(sprint)"
            />
          </div>
        </article>
        <UiEmptyState
          v-if="!sprints.length"
          title="Crie uma sprint para planejar a próxima entrega."
        />
      </div>
      <UiCard v-if="selectedSprint">
        <h3 class="mb-3 font-title text-2xl">{{ selectedSprint.name }}</h3>
        <SprintsSprintBoard
          :key="selectedSprint.id"
          :sprint="selectedSprint"
          :migration-targets="migrationTargets.filter((item) => item.status === 'ACTIVE' && item.id !== selectedSprint.id)"
          @changed="refresh"
        />
      </UiCard>
    </div>
    <SprintsSprintFormModal
      :open="modalOpen"
      :sprint="editingSprint"
      :submitting="submitting"
      :error="feedback"
      @close="modalOpen = false"
      @submit="save"
    />
  </section>
</template>
