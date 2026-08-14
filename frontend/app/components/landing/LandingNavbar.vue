<script setup lang="ts">
const isMenuOpen = ref(false)
const isAtTop = ref(true)

const isTransparent = computed(() => isAtTop.value && !isMenuOpen.value)

const updateScroll = () => {
  isAtTop.value = window.scrollY < 8
}

const closeMenu = () => {
  isMenuOpen.value = false
}

onMounted(() => {
  updateScroll()
  window.addEventListener('scroll', updateScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateScroll)
})
</script>

<template>
  <header
    class="fixed inset-x-0 top-0 z-50 border-b transition-[background-color,border-color,backdrop-filter] duration-300"
    :class="
      isTransparent
        ? 'border-transparent bg-transparent'
        : 'border-black/10 bg-white/90 backdrop-blur-xl'
    "
  >
    <nav
      class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-12"
      aria-label="Navegação principal"
    >
      <a class="group flex items-center gap-3" href="#inicio" aria-label="MyTask — início">
        <span
          class="grid size-10 place-items-center rounded-xl border-2 border-black bg-black font-hero text-lg font-bold text-white shadow-accent-sm transition-transform group-hover:-translate-y-0.5"
          aria-hidden="true"
        >
          M
        </span>
        <span class="font-title text-xl tracking-tight text-black">MyTask</span>
      </a>

      <div class="hidden items-center gap-8 lg:flex">
        <a class="nav-link" href="#como-funciona">Como funciona</a>
        <a class="nav-link" href="#kanban">Kanban</a>
        <a class="nav-link" href="#scrum">Scrum</a>
      </div>

      <div class="hidden items-center gap-3 lg:flex">
        <NuxtLink
          to="/em-breve"
          class="rounded-full px-5 py-2.5 text-sm font-semibold text-black transition-colors hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
        >
          Entrar
        </NuxtLink>
        <NuxtLink
          to="/em-breve"
          class="rounded-full border-2 border-black bg-black px-5 py-2.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-neutral-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
        >
          Criar conta
        </NuxtLink>
      </div>

      <button
        type="button"
        class="grid size-11 place-items-center rounded-full border border-black/15 text-black lg:hidden"
        :aria-expanded="isMenuOpen"
        aria-controls="mobile-menu"
        aria-label="Alternar menu"
        @click="isMenuOpen = !isMenuOpen"
      >
        <svg
          v-if="!isMenuOpen"
          aria-hidden="true"
          class="size-5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
        >
          <path d="M4 7h16M4 12h16M4 17h16" />
        </svg>
        <svg
          v-else
          aria-hidden="true"
          class="size-5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
        >
          <path d="m6 6 12 12M18 6 6 18" />
        </svg>
      </button>
    </nav>

    <div
      v-show="isMenuOpen"
      id="mobile-menu"
      class="border-t border-black/10 bg-white px-5 py-5 lg:hidden"
    >
      <div class="mx-auto flex max-w-7xl flex-col gap-1">
        <a class="mobile-nav-link" href="#como-funciona" @click="closeMenu">Como funciona</a>
        <a class="mobile-nav-link" href="#kanban" @click="closeMenu">Kanban</a>
        <a class="mobile-nav-link" href="#scrum" @click="closeMenu">Scrum</a>
        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-black/10 pt-5">
          <NuxtLink
            to="/em-breve"
            class="rounded-full border border-black px-4 py-3 text-center text-sm font-semibold"
            @click="closeMenu"
          >
            Entrar
          </NuxtLink>
          <NuxtLink
            to="/em-breve"
            class="rounded-full bg-black px-4 py-3 text-center text-sm font-semibold text-white"
            @click="closeMenu"
          >
            Criar conta
          </NuxtLink>
        </div>
      </div>
    </div>
  </header>
</template>
