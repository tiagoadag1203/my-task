<script setup>
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'
import { callRepository, errorMessage, unwrapData } from '../dashboard/api'

ChartJS.register(ArcElement, BarElement, CategoryScale, Legend, LinearScale, Tooltip)

const { isDark, colors, legendOptions, scaleOptions } = useChartTheme()
const { $api } = useNuxtApp()
const exportError = ref('')
const exporting = ref(false)
const { data, status, error, refresh } = await useAsyncData('admin-overview', async () => {
  const [summaryResponse, analyticsResponse] = await Promise.all([
    callRepository($api.admin, ['summary']),
    callRepository($api.admin, ['analytics']),
  ])
  return { summary: unwrapData(summaryResponse), analytics: unwrapData(analyticsResponse) }
})
const summary = computed(() => data.value?.summary ?? {})
const analytics = computed(() => data.value?.analytics ?? {})
const cycleTimes = computed(() => [
  { label: 'Global', value: Number(analytics.value?.cycle_time_hours?.global_average ?? 0) },
  { label: 'Kanban', value: Number(analytics.value?.cycle_time_hours?.kanban_average ?? 0) },
  { label: 'Scrum', value: Number(analytics.value?.cycle_time_hours?.scrum_average ?? 0) },
])
const cycleTimeData = computed(() => ({
  labels: cycleTimes.value.map((item) => item.label),
  datasets: [{
    label: 'Horas',
    data: cycleTimes.value.map((item) => item.value),
    backgroundColor: [colors.value.done, colors.value.pending, colors.value.accent],
  }],
}))
const sprintRate = computed(() => Number(analytics.value?.sprints?.average_completion_rate ?? 0))
const sprintCompletionData = computed(() => ({
  labels: ['Concluídas', 'Pendentes'],
  datasets: [{
    data: [sprintRate.value, Math.max(0, 100 - sprintRate.value)],
    backgroundColor: [colors.value.accent, colors.value.grid],
    borderWidth: 0,
  }],
}))
const barOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: scaleOptions.value,
    y: { ...scaleOptions.value, beginAtZero: true },
  },
}))
const doughnutOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: legendOptions.value },
}))
const cards = computed(() => [
  ['Participantes', summary.value.users ?? 0],
  ['Projetos', summary.value.projects ?? 0],
  ['Tarefas', summary.value.tasks ?? 0],
  ['Tarefas concluídas', summary.value.completed_tasks ?? 0],
  ['Sprints ativas', summary.value.active_sprints ?? 0],
  ['Sprints finalizadas', summary.value.finished_sprints ?? 0],
])

async function exportCsv() {
  exporting.value = true
  exportError.value = ''
  try {
    const blob = await callRepository($api.admin, ['exportCsv', 'export'])
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `mytask-export-${new Date().toISOString().slice(0, 10)}.csv`
    link.click()
    URL.revokeObjectURL(url)
  } catch (exception) {
    exportError.value = errorMessage(exception, 'Não foi possível exportar os dados.')
  } finally {
    exporting.value = false
  }
}
</script>

<template>
  <section aria-labelledby="research-title">
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">Acompanhamento científico</p><h2 id="research-title" class="font-title text-3xl">Visão geral</h2></div>
      <UiButton :loading="exporting" :disabled="exporting" @click="exportCsv">
        Exportar CSV
      </UiButton>
    </header>
    <UiCard class="mb-5">
      <p class="text-sm text-[var(--app-text-muted)]">
        A exportação exclui apelidos e e-mails e usa identificadores pseudonimizados com salt efêmero.
      </p>
    </UiCard>
    <p v-if="exportError" role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-900">{{ exportError }}</p>
    <div v-if="status === 'pending'" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"><div v-for="index in 6" :key="index" class="h-32 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" /></div>
    <div v-else-if="error" class="rounded-2xl bg-red-50 p-5 text-red-900">
      Não foi possível carregar os dados.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </div>
    <template v-else>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <UiCard v-for="[label, value] in cards" :key="label" as="article">
          <p class="text-sm text-[var(--app-text-muted)]">{{ label }}</p>
          <strong class="mt-2 block font-title text-4xl">{{ value }}</strong>
        </UiCard>
      </div>
      <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <UiCard as="article">
          <h3 class="font-title text-xl">Tempo médio de ciclo</h3>
          <p class="text-sm text-[var(--app-text-muted)]">
            Em horas · amostra {{ analytics.cycle_time_hours?.sample_size ?? 0 }}
          </p>
          <div class="relative mt-6 h-64">
            <ClientOnly>
              <Bar
                :key="String(isDark)"
                :data="cycleTimeData"
                :options="barOptions"
                aria-label="Comparação do tempo médio de ciclo"
              />
            </ClientOnly>
          </div>
        </UiCard>
        <UiCard as="article">
          <h3 class="font-title text-xl">Conclusão de sprints</h3>
          <p class="text-sm text-[var(--app-text-muted)]">
            {{ analytics.sprints?.finished_count ?? 0 }} sprints finalizadas
          </p>
          <div class="relative mt-6 h-64">
            <ClientOnly>
              <Doughnut
                :key="String(isDark)"
                :data="sprintCompletionData"
                :options="doughnutOptions"
                aria-label="Taxa média de conclusão das sprints"
              />
            </ClientOnly>
          </div>
        </UiCard>
      </div>
    </template>
  </section>
</template>
