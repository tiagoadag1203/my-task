<script setup>
const props = defineProps({
  user: { type: Object, default: null },
})

const config = useRuntimeConfig()
const dismissed = ref(true)
const storageKey = computed(() => `mytask:tam-dismissed:${props.user?.id ?? 'anonymous'}`)
const isEligible = computed(() => {
  if (!props.user?.created_at || !config.public.tamFormUrl) return false
  const createdAt = new Date(props.user.created_at).getTime()
  return Number.isFinite(createdAt) && Date.now() - createdAt >= 15 * 24 * 60 * 60 * 1000
})
const visible = computed(() => isEligible.value && !dismissed.value)

onMounted(() => {
  dismissed.value = localStorage.getItem(storageKey.value) === '1'
})

function dismiss() {
  dismissed.value = true
  localStorage.setItem(storageKey.value, '1')
}
</script>

<template>
  <aside
    v-if="visible"
    class="fixed bottom-[calc(var(--app-mobile-nav-height)+1rem)] right-4 z-30 max-w-sm rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-4 text-[var(--app-text)] lg:bottom-6 lg:right-6"
    aria-labelledby="tam-nudge-title"
  >
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[var(--app-text-muted)]">
          Pesquisa acadêmica
        </p>
        <h2 id="tam-nudge-title" class="mt-1 font-title text-lg">Conte como está sua experiência</h2>
      </div>
      <button
        type="button"
        class="grid size-8 shrink-0 place-items-center rounded-lg hover:bg-[var(--app-surface-muted)]"
        aria-label="Dispensar convite da pesquisa"
        @click="dismiss"
      >
        ×
      </button>
    </div>
    <p class="mt-2 text-sm text-[var(--app-text-muted)]">
      Sua resposta ajuda a avaliar a utilidade e a facilidade de uso do MyTask.
    </p>
    <a
      :href="config.public.tamFormUrl"
      target="_blank"
      rel="noopener noreferrer"
      class="mt-4 inline-flex min-h-10 items-center rounded-xl bg-[var(--app-accent)] px-4 text-sm font-bold text-[var(--app-accent-contrast)]"
    >
      Responder questionário
    </a>
  </aside>
</template>
