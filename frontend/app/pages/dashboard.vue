<script setup>
definePageMeta({ layout: false })

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const isAdmin = computed(() => auth.user?.role === 'admin')
const allowedTabs = computed(() =>
  isAdmin.value
    ? ['visao-geral', 'participantes']
    : ['inicio', 'tarefas', 'projetos'],
)
const fallbackTab = computed(() => allowedTabs.value[0])
const activeTab = computed(() =>
  allowedTabs.value.includes(String(route.query.tab))
    ? String(route.query.tab)
    : fallbackTab.value,
)

const navigationItems = computed(() =>
  isAdmin.value
    ? [
        { label: 'Visão geral', to: { path: '/dashboard', query: { tab: 'visao-geral' } }, icon: 'home' },
        { label: 'Participantes', to: { path: '/dashboard', query: { tab: 'participantes' } }, icon: 'users' },
      ]
    : [
        { label: 'Início', to: { path: '/dashboard', query: { tab: 'inicio' } }, icon: 'home' },
        { label: 'Minhas tarefas', to: { path: '/dashboard', query: { tab: 'tarefas' } }, icon: 'tasks' },
        { label: 'Projetos', to: { path: '/dashboard', query: { tab: 'projetos' } }, icon: 'projects' },
      ],
)
const sidebarUser = computed(() => ({
  name: auth.user?.nickname || 'Usuário',
  email: auth.user?.email || '',
}))
const pageTitle = computed(() => (isAdmin.value ? 'Painel da pesquisa' : 'Meu espaço'))

watch(
  [() => route.query.tab, isAdmin],
  () => {
    if (!allowedTabs.value.includes(String(route.query.tab))) {
      router.replace({ query: { ...route.query, tab: fallbackTab.value, project: undefined } })
    }
  },
  { immediate: true },
)
</script>

<template>
  <NuxtLayout name="dashboard" :items="navigationItems" :user="sidebarUser" :title="pageTitle">
    <template #header>
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">
          {{ isAdmin ? 'Painel da pesquisa' : 'Meu espaço' }}
        </p>
        <h1 class="truncate font-title text-xl sm:text-2xl">
          Olá, {{ auth.user?.nickname || 'participante' }}
        </h1>
      </div>
    </template>

    <KeepAlive>
      <AdminResearcherParticipantsTab
        v-if="isAdmin && activeTab === 'participantes'"
      />
      <AdminResearcherOverviewTab v-else-if="isAdmin" />
      <DashboardHomeTab v-else-if="activeTab === 'inicio'" />
      <DashboardGeneralKanbanTab v-else-if="activeTab === 'tarefas'" />
      <ProjectsTab v-else-if="activeTab === 'projetos'" />
    </KeepAlive>
    <DashboardTamNudgeCard v-if="!isAdmin" :user="auth.user" />
  </NuxtLayout>
</template>
