<script setup>
import {
  ArcElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
} from 'chart.js'
import { Doughnut, Line } from 'vue-chartjs'
import { callRepository, unwrapData } from './api'

ChartJS.register(ArcElement, CategoryScale, Legend, LineElement, LinearScale, PointElement, Tooltip)

const { $api } = useNuxtApp()
const { isDark, colors, legendOptions, scaleOptions } = useChartTheme()
const { data: analytics, status, error, refresh } = await useAsyncData(
  'participant-dashboard-analytics',
  async () => unwrapData(await callRepository($api.dashboard, ['analytics', 'index'])),
)

const distribution = computed(() => analytics.value?.task_distribution ?? {})
const total = computed(() => Number(distribution.value.total ?? 0))
const weekly = computed(() => analytics.value?.weekly_completed ?? [])
const doughnutData = computed(() => ({
  labels: ['A fazer', 'Em progresso', 'Concluídas'],
  datasets: [{
    data: [
      Number(distribution.value.todo ?? 0),
      Number(distribution.value.in_progress ?? 0),
      Number(distribution.value.done ?? 0),
    ],
    backgroundColor: [colors.value.todo, colors.value.progress, colors.value.done],
    borderWidth: 0,
  }],
}))
const weeklyData = computed(() => ({
  labels: weekly.value.map((day) =>
    new Date(`${day.date}T12:00:00`).toLocaleDateString('pt-BR', { weekday: 'short' }),
  ),
  datasets: [{
    label: 'Tarefas concluídas',
    data: weekly.value.map((day) => Number(day.count)),
    borderColor: colors.value.accent,
    backgroundColor: colors.value.accent,
    tension: 0.25,
  }],
}))
const doughnutOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: legendOptions.value },
}))
const lineOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: legendOptions.value },
  scales: {
    x: scaleOptions.value,
    y: { ...scaleOptions.value, beginAtZero: true },
  },
}))

const { isFirstLoading, showError } = useFirstLoad(status, error)
</script>

<template>
  <section aria-labelledby="overview-title">
    <header class="mb-6">
      <p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">Visão do dia</p>
      <h2 id="overview-title" class="font-title text-3xl">Seu progresso</h2>
      <p class="mt-1 text-neutral-600 dark:text-neutral-300">Uma leitura simples para orientar o próximo passo.</p>
    </header>

    <div v-if="isFirstLoading" class="grid gap-5 lg:grid-cols-2">
      <div v-for="index in 2" :key="index" class="h-80 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" />
    </div>
    <div v-else-if="showError" class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900">
      <p>As estatísticas ainda não puderam ser carregadas.</p>
      <UiButton variant="ghost" size="sm" class="mt-2" @click="refresh">Tentar novamente</UiButton>
    </div>
    <div v-else class="grid gap-5 lg:grid-cols-2">
      <UiCard as="article">
        <h3 class="font-title text-xl">Distribuição atual</h3>
        <div v-if="total" class="relative mt-6 h-64">
          <ClientOnly>
            <Doughnut
              :key="String(isDark)"
              :data="doughnutData"
              :options="doughnutOptions"
              aria-label="Distribuição das tarefas"
            />
          </ClientOnly>
        </div>
        <p v-else class="mt-8 rounded-2xl bg-[var(--app-surface-muted)] p-6 text-center text-[var(--app-text-muted)]">
          Crie sua primeira tarefa para acompanhar a distribuição.
        </p>
      </UiCard>

      <UiCard as="article">
        <h3 class="font-title text-xl">Concluídas nos últimos 7 dias</h3>
        <div v-if="weekly.length" class="relative mt-6 h-64">
          <ClientOnly>
            <Line
              :key="String(isDark)"
              :data="weeklyData"
              :options="lineOptions"
              aria-label="Evolução semanal de tarefas concluídas"
            />
          </ClientOnly>
        </div>
        <p v-else class="mt-8 rounded-2xl bg-[var(--app-surface-muted)] p-6 text-center text-[var(--app-text-muted)]">
          O histórico aparecerá após as primeiras conclusões.
        </p>
      </UiCard>
    </div>
  </section>
</template>
