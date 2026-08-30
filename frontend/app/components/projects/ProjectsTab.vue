<script setup>
import { callRepository, errorMessage, paginationOf, unwrapList } from '../dashboard/api'

const route = useRoute()
const router = useRouter()
const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const page = computed(() => Math.max(1, Number(route.query.page) || 1))
const selectedProject = computed(() => Number(route.query.project) || null)
const modalOpen = ref(false)
const editingProject = ref(null)
const submitting = ref(false)
const feedback = ref('')

const { data: response, status, error, refresh } = await useAsyncData(
  () => `projects-${page.value}`,
  () => callRepository($api.projects, ['list', 'index', 'all'], { page: page.value }),
  { watch: [page] },
)
const projects = computed(() => unwrapList(response.value))
const pagination = computed(() => paginationOf(response.value))

function setQuery(values) {
  router.push({ query: { ...route.query, ...values } })
}

function openForm(project = null) {
  editingProject.value = project
  feedback.value = ''
  modalOpen.value = true
}

async function save(payload) {
  submitting.value = true
  feedback.value = ''
  try {
    if (editingProject.value) {
      await callRepository($api.projects, ['update'], editingProject.value.id, payload)
    } else {
      await callRepository($api.projects, ['create', 'store'], payload)
    }
    modalOpen.value = false
    await refresh()
    toast.success(editingProject.value ? 'Projeto atualizado.' : 'Projeto criado.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  } finally {
    submitting.value = false
  }
}

function projectMenu(project) {
  return [
    { label: 'Editar', onSelect: () => openForm(project) },
    { label: 'Excluir', tone: 'danger', onSelect: () => remove(project) },
  ]
}

function openProject(project) {
  setQuery({ project: project.id })
}

async function remove(project) {
  const confirmed = await ask({
    title: 'Excluir projeto',
    message: `Excluir “${project.name}” e todas as suas sprints e tarefas? Esta ação não pode ser desfeita.`,
    confirmLabel: 'Excluir projeto',
  })
  if (!confirmed) return
  try {
    await callRepository($api.projects, ['remove', 'destroy', 'delete'], project.id)
    await refresh()
    toast.success('Projeto excluído.')
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}
</script>

<template>
  <ProjectsProjectWorkspace
    v-if="selectedProject"
    :project-id="selectedProject"
    @back="setQuery({ project: undefined, sprint: undefined })"
  />
  <section v-else aria-labelledby="projects-title">
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--app-text-muted)]">
          Planejamento estratégico
        </p>
        <h2 id="projects-title" class="font-title text-3xl">Projetos</h2>
      </div>
      <UiButton @click="openForm()">+ Novo projeto</UiButton>
    </header>

    <p v-if="feedback" role="status" class="mb-4 rounded-xl bg-amber-100 p-3 text-sm font-semibold text-amber-950">
      {{ feedback }}
    </p>
    <div v-if="status === 'pending'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="index in 6" :key="index" class="h-48 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" />
    </div>
    <div v-else-if="error" class="rounded-2xl bg-red-50 p-5 text-red-900">
      Não foi possível carregar seus projetos.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </div>
    <UiEmptyState
      v-else-if="!projects.length"
      title="Seu primeiro projeto começa aqui"
      description="Organize uma meta em backlog e sprints curtas."
    >
      <template #actions>
        <UiButton @click="openForm()">Criar projeto</UiButton>
      </template>
    </UiEmptyState>
    <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article
        v-for="project in projects"
        :key="project.id"
        class="app-card relative flex min-h-52 cursor-pointer flex-col p-5 transition-colors hover:border-[var(--app-text)]"
        role="link"
        :aria-label="`Abrir projeto ${project.name}`"
        tabindex="0"
        @click="openProject(project)"
        @keydown.enter.prevent="openProject(project)"
        @keydown.space.prevent="openProject(project)"
      >
        <div class="flex items-start gap-2">
          <h3 class="min-w-0 flex-1 font-title text-xl">{{ project.name }}</h3>
          <UiMenu
            :label="`Ações do projeto ${project.name}`"
            :items="projectMenu(project)"
          />
        </div>
        <p class="mt-2 line-clamp-3 text-[var(--app-text-muted)]">
          {{ project.description || 'Sem descrição.' }}
        </p>
        <div class="mt-auto flex gap-4 pt-4 text-sm text-[var(--app-text-muted)]">
          <span>{{ project.sprints_count ?? 0 }} sprints</span>
          <span>{{ project.tasks_count ?? 0 }} tarefas</span>
        </div>
      </article>
    </div>

    <nav v-if="pagination.last_page > 1" class="mt-6 flex items-center justify-center gap-3" aria-label="Paginação de projetos">
      <UiButton variant="secondary" size="sm" :disabled="page <= 1" @click="setQuery({ page: page - 1 })">
        Anterior
      </UiButton>
      <span class="text-sm">Página {{ page }} de {{ pagination.last_page }}</span>
      <UiButton
        variant="secondary"
        size="sm"
        :disabled="page >= pagination.last_page"
        @click="setQuery({ page: page + 1 })"
      >
        Próxima
      </UiButton>
    </nav>
    <ProjectsProjectFormModal
      :open="modalOpen"
      :project="editingProject"
      :submitting="submitting"
      :error="feedback"
      @close="modalOpen = false"
      @submit="save"
    />
  </section>
</template>
