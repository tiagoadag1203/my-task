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
})

const route = useRoute()
const router = useRouter()
const isActive = (to) => {
  const resolved = router.resolve(to)
  return (
    route.path === resolved.path &&
    Object.entries(resolved.query).every(([key, value]) => String(route.query[key]) === String(value))
  )
}
</script>

<template>
  <nav
    class="fixed inset-x-0 bottom-0 z-40 grid h-[var(--app-mobile-nav-height)] border-t border-[var(--app-border)] bg-[var(--app-surface)] px-2 pb-[env(safe-area-inset-bottom)] text-[var(--app-text)] lg:hidden"
    :style="{ gridTemplateColumns: `repeat(${items.length}, minmax(0, 1fr))` }"
    aria-label="Navegação móvel do painel"
  >
    <NuxtLink
      v-for="item in props.items"
      :key="item.to"
      :to="item.to"
      class="relative flex min-w-0 flex-col items-center justify-center gap-1 rounded-lg px-1 text-[0.6875rem] font-semibold"
      :class="isActive(item.to) ? 'text-[var(--app-text)]' : 'text-[var(--app-text-muted)]'"
      :aria-current="isActive(item.to) ? 'page' : undefined"
    >
      <span
        v-if="isActive(item.to)"
        class="absolute inset-x-4 top-0 h-0.5 bg-[var(--app-accent)]"
        aria-hidden="true"
      />
      <UiIcon :name="item.icon" />
      <span class="truncate">{{ item.label }}</span>
    </NuxtLink>
  </nav>
</template>
