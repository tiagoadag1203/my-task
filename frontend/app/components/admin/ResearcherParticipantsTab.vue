<script setup>
import { callRepository, errorMessage, formatDate, paginationOf, unwrapList } from '../dashboard/api'

const { $api } = useNuxtApp()
const { ask } = useConfirm()
const toast = useToast()
const search = ref('')
const appliedSearch = ref('')
const page = ref(1)
const feedback = ref('')
let debounceTimer

watch(search, (value) => {
  window.clearTimeout(debounceTimer)
  debounceTimer = window.setTimeout(() => {
    appliedSearch.value = value.trim()
    page.value = 1
  }, 350)
})
onBeforeUnmount(() => window.clearTimeout(debounceTimer))

const { data: response, status, error, refresh } = await useAsyncData(
  () => `admin-users-${page.value}-${appliedSearch.value}`,
  () => callRepository($api.admin, ['users', 'listUsers'], {
    page: page.value,
    search: appliedSearch.value || undefined,
  }),
  { watch: [page, appliedSearch] },
)
const participants = computed(() => unwrapList(response.value))
const pagination = computed(() => paginationOf(response.value))

async function resetPassword(participant) {
  const confirmed = await ask({
    title: 'Redefinir senha',
    message: `Redefinir a senha de ${participant.nickname} para o padrão 123456?`,
    confirmLabel: 'Redefinir',
  })
  if (!confirmed) return
  feedback.value = ''
  try {
    const response = await callRepository($api.admin, ['resetPassword'], participant.id)
    feedback.value = response?.message ?? 'Senha redefinida com sucesso para o padrão 123456.'
    toast.success(feedback.value)
  } catch (exception) {
    feedback.value = errorMessage(exception)
  }
}

const { isFirstLoading, showError } = useFirstLoad(status, error)
</script>

<template>
  <section aria-labelledby="participants-title">
    <header class="mb-6">
      <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--app-text-muted)]">
        Gestão da amostra
      </p>
      <h2 id="participants-title" class="font-title text-3xl">Participantes</h2>
    </header>

    <UiInput
      v-model="search"
      class="mb-5 max-w-xl"
      label="Buscar por apelido ou e-mail"
      type="search"
      placeholder="Digite para buscar…"
    />

    <p v-if="feedback" role="status" class="mb-4 rounded-xl bg-accent/20 p-3 text-sm font-semibold">
      {{ feedback }}
    </p>
    <div v-if="isFirstLoading" class="h-72 animate-pulse rounded-3xl bg-[var(--app-surface-muted)]" />
    <div v-else-if="showError" class="rounded-2xl bg-red-50 p-5 text-red-900">
      Não foi possível carregar os participantes.
      <UiButton variant="ghost" size="sm" @click="refresh">Tentar novamente</UiButton>
    </div>
    <UiEmptyState
      v-else-if="!participants.length"
      title="Nenhum participante encontrado."
    />
    <UiCard v-else as="div" :padded="false">
      <div class="overflow-x-auto">
        <table class="w-full min-w-3xl text-left">
          <thead class="bg-[var(--app-surface-muted)] text-sm">
            <tr>
              <th class="p-4 font-semibold">ID</th>
              <th class="p-4 font-semibold">Apelido</th>
              <th class="p-4 font-semibold">E-mail</th>
              <th class="p-4 font-semibold">Inscrição</th>
              <th class="p-4 font-semibold">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="participant in participants"
              :key="participant.id"
              class="border-t border-[var(--app-border)]"
            >
              <td class="p-4">{{ participant.id }}</td>
              <td class="p-4 font-semibold">{{ participant.nickname }}</td>
              <td class="p-4">{{ participant.email }}</td>
              <td class="p-4">{{ formatDate(participant.created_at) }}</td>
              <td class="p-4">
                <UiButton size="sm" variant="secondary" @click="resetPassword(participant)">
                  Resetar senha
                </UiButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </UiCard>
    <nav
      v-if="pagination.last_page > 1"
      class="mt-5 flex items-center justify-center gap-3"
      aria-label="Paginação de participantes"
    >
      <UiButton variant="secondary" size="sm" :disabled="page <= 1" @click="page--">
        Anterior
      </UiButton>
      <span class="text-sm">Página {{ page }} de {{ pagination.last_page }}</span>
      <UiButton
        variant="secondary"
        size="sm"
        :disabled="page >= pagination.last_page"
        @click="page++"
      >
        Próxima
      </UiButton>
    </nav>
  </section>
</template>
