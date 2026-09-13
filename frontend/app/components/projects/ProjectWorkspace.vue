<script setup>
import { callRepository, unwrapData } from '../dashboard/api'

const props = defineProps({ projectId: { type: Number, required: true } })
defineEmits(['back'])
const { $api } = useNuxtApp()
const knownSprints = ref([])
const backlog = ref(null)
const sprintsPanel = ref(null)
const activeWorkspaceTab = ref('backlog')

const workspaceTabs = [
  { id: 'backlog', label: 'Backlog' },
  { id: 'sprints', label: 'Sprints' },
]

const { data: project, status, error, refresh } = await useAsyncData(
  () => `project-${props.projectId}`,
  async () => unwrapData(await callRepository($api.projects, ['get', 'show'], props.projectId)),
)

function syncSprints(sprints) {
  knownSprints.value = sprints ?? []
  backlog.value?.refresh()
}

function syncAll() {
  refresh()
  backlog.value?.refresh()
  sprintsPanel.value?.refresh()
}
</script>

<template>
  <section>
    <UiButton variant="ghost" class="mb-5" @click="$emit('back')">← Voltar aos projetos</UiButton>
    <div v-if="status === 'pending'" class="h-40 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" />
    <div v-else-if="error" class="rounded-2xl bg-red-50 p-5 text-red-900">
      Projeto não encontrado.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </div>
    <template v-else-if="project">
      <header class="mb-6 rounded-3xl bg-black p-6 text-white dark:bg-neutral-900">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-accent">Workspace do projeto</p>
        <h2 class="mt-1 font-title text-3xl">{{ project.name }}</h2>
        <p v-if="project.description" class="mt-2 max-w-3xl text-neutral-300">{{ project.description }}</p>
        <div class="mt-4 flex gap-5 text-sm"><span>{{ project.tasks_count ?? 0 }} tarefas</span><span>{{ project.sprints_count ?? 0 }} sprints</span></div>
      </header>

      <nav
        class="mb-6 flex gap-2 border-b border-[var(--app-border)]"
        role="tablist"
        aria-label="Áreas do projeto"
      >
        <button
          v-for="tab in workspaceTabs"
          :id="`project-tab-${tab.id}`"
          :key="tab.id"
          type="button"
          class="relative min-w-28 px-5 py-3 text-sm font-bold transition-colors"
          :class="activeWorkspaceTab === tab.id
            ? 'text-[var(--app-text)] after:absolute after:inset-x-0 after:-bottom-px after:h-1 after:rounded-t-full after:bg-[var(--app-accent)]'
            : 'text-[var(--app-text-muted)] hover:text-[var(--app-text)]'"
          role="tab"
          :aria-selected="activeWorkspaceTab === tab.id"
          :aria-controls="`project-panel-${tab.id}`"
          @click="activeWorkspaceTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </nav>

      <div
        v-show="activeWorkspaceTab === 'backlog'"
        id="project-panel-backlog"
        role="tabpanel"
        aria-labelledby="project-tab-backlog"
      >
        <BacklogPanel ref="backlog" :project-id="projectId" :sprints="knownSprints" @changed="syncAll" />
      </div>
      <div
        v-show="activeWorkspaceTab === 'sprints'"
        id="project-panel-sprints"
        role="tabpanel"
        aria-labelledby="project-tab-sprints"
      >
        <SprintsPanel ref="sprintsPanel" :project-id="projectId" @updated="syncSprints" />
      </div>
    </template>
  </section>
</template>
