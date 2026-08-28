<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => [
      { label: 'Início', to: { path: '/dashboard', query: { tab: 'inicio' } }, icon: 'home' },
      { label: 'Minhas tarefas', to: { path: '/dashboard', query: { tab: 'tarefas' } }, icon: 'tasks' },
      { label: 'Projetos', to: { path: '/dashboard', query: { tab: 'projetos' } }, icon: 'projects' },
    ],
  },
  user: { type: Object, default: () => ({ name: 'Usuário', email: '' }) },
})

const route = useRoute()
const router = useRouter()
const emit = defineEmits(['logout'])
const isActive = (to) => {
  const resolved = router.resolve(to)
  return (
    route.path === resolved.path &&
    Object.entries(resolved.query).every(([key, value]) => String(route.query[key]) === String(value))
  )
}
</script>

<template>
  <aside
    class="fixed inset-y-0 left-0 z-30 hidden w-[var(--app-sidebar-width)] flex-col border-r border-[var(--app-border)] bg-[var(--app-surface)] text-[var(--app-text)] lg:flex"
    aria-label="Navegação do painel"
  >
    <div class="flex h-20 items-center border-b border-[var(--app-border)] px-5">
      <NuxtLink to="/dashboard" class="flex items-center gap-3 rounded-lg" aria-label="MyTask — painel">
        <span class="grid size-9 place-items-center rounded-lg bg-black font-hero font-bold text-white dark:bg-white dark:text-black" aria-hidden="true">M</span>
        <span class="font-title text-xl">MyTask</span>
      </NuxtLink>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Seções do painel">
      <NuxtLink
        v-for="item in props.items"
        :key="item.to"
        :to="item.to"
        class="flex min-h-11 items-center gap-3 rounded-xl border px-3 text-sm font-semibold transition-colors"
        :class="
          isActive(item.to)
            ? 'border-[var(--app-accent)] bg-[var(--app-accent)] text-[var(--app-accent-contrast)]'
            : 'border-transparent text-[var(--app-text-muted)] hover:bg-[var(--app-surface-muted)] hover:text-[var(--app-text)]'
        "
        :aria-current="isActive(item.to) ? 'page' : undefined"
      >
        <span class="grid size-6 place-items-center" aria-hidden="true"><UiIcon :name="item.icon" /></span>
        {{ item.label }}
      </NuxtLink>
    </nav>

    <div class="border-t border-[var(--app-border)] p-3">
      <div class="flex items-center gap-3 rounded-xl p-2">
        <UiAvatar :name="user.name" :src="user.avatar" size="sm" />
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold">{{ user.name }}</p>
          <p v-if="user.email" class="truncate text-xs text-[var(--app-text-muted)]">{{ user.email }}</p>
        </div>
        <GlobalThemeToggle />
      </div>
      <button
        type="button"
        class="mt-2 flex min-h-11 w-full items-center rounded-xl px-3 text-sm font-semibold text-[var(--app-text-muted)] hover:bg-[var(--app-surface-muted)] hover:text-[var(--app-text)]"
        @click="emit('logout')"
      >
        Sair
      </button>
    </div>
  </aside>
</template>
