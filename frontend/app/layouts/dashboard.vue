<script setup>
const props = defineProps({
  title: { type: String, default: '' },
  user: { type: Object, default: () => ({ name: 'Usuário', email: '' }) },
  items: { type: Array, default: () => [] },
})

const auth = useAuthStore()
const isLoggingOut = ref(false)

async function logout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await auth.logout()
    await navigateTo('/login')
  } finally {
    isLoggingOut.value = false
  }
}
</script>

<template>
  <div class="min-h-dvh bg-[var(--app-bg)] text-[var(--app-text)]">
    <a
      href="#dashboard-content"
      class="fixed left-3 top-3 z-[120] -translate-y-20 rounded-lg bg-[var(--app-accent)] px-4 py-2 font-semibold text-[var(--app-accent-contrast)] focus:translate-y-0"
    >
      Pular para o conteúdo
    </a>

    <DashboardSidebar :items="props.items" :user="props.user" @logout="logout" />

    <div class="lg:pl-[var(--app-sidebar-width)]">
      <header
        class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-[var(--app-border)] bg-[var(--app-surface)] px-4 sm:px-6 lg:h-20 lg:px-8"
      >
        <div class="min-w-0">
          <slot name="header">
            <h1 class="truncate font-title text-xl sm:text-2xl">{{ title || 'MyTask' }}</h1>
          </slot>
        </div>
        <div class="ml-4 flex shrink-0 items-center gap-2">
          <slot name="actions" />
          <div class="lg:hidden">
            <GlobalThemeToggle />
          </div>
        </div>
      </header>

      <main
        id="dashboard-content"
        class="mx-auto w-full max-w-[100rem] p-4 pb-[calc(var(--app-mobile-nav-height)+1.5rem)] sm:p-6 sm:pb-[calc(var(--app-mobile-nav-height)+1.5rem)] lg:p-8"
        tabindex="-1"
      >
        <slot />
      </main>
    </div>

    <DashboardMobileNav :items="props.items" />
  </div>
</template>
