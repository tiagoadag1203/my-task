<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
})

const route = useRoute()
const titleId = useId()
const isLogin = computed(() => route.path.startsWith('/login'))
</script>

<template>
  <div
    class="relative isolate min-h-screen overflow-hidden bg-white font-body text-black"
    style="color-scheme: light; --app-surface: #ffffff; --app-border: #d4d4d4; --app-text: #0a0a0a; --app-text-muted: #525252;"
  >
    <div
      class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(to_right,#0000000a_1px,transparent_1px),linear-gradient(to_bottom,#0000000a_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent_88%)]"
      aria-hidden="true"
    />
    <div
      class="pointer-events-none absolute left-1/2 top-24 -z-10 size-80 -translate-x-1/2 rounded-full bg-accent/25 blur-3xl"
      aria-hidden="true"
    />

    <header class="relative z-10 border-b border-black/10">
      <nav
        class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-12"
        aria-label="Navegação de autenticação"
      >
        <NuxtLink
          to="/"
          class="group flex items-center gap-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-black"
          aria-label="MyTask — voltar à página inicial"
        >
          <span
            class="grid size-10 place-items-center rounded-xl border-2 border-black bg-black font-hero text-lg font-bold text-white shadow-accent-sm transition-transform group-hover:-translate-y-0.5"
            aria-hidden="true"
          >
            M
          </span>
          <span class="font-title text-xl tracking-tight">MyTask</span>
        </NuxtLink>

        <div class="flex items-center gap-3">
          <NuxtLink
            v-if="!isLogin"
            to="/login"
            class="rounded-full px-5 py-2.5 text-sm font-semibold text-black transition-colors hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
          >
            Entrar
          </NuxtLink>
          <NuxtLink
            v-else
            to="/register"
            class="rounded-full border-2 border-black bg-black px-5 py-2.5 text-sm font-semibold text-white shadow-accent-sm transition-all hover:-translate-y-0.5 hover:bg-neutral-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
          >
            Criar conta
          </NuxtLink>
        </div>
      </nav>
    </header>

    <main class="relative z-10 grid min-h-[calc(100vh-5rem)] place-items-center px-5 py-16">
      <section class="relative w-full max-w-md" :aria-labelledby="titleId">
        <div
          class="absolute -inset-4 -z-10 rotate-2 rounded-[2.25rem] border-2 border-black bg-accent"
          aria-hidden="true"
        />
        <div
          class="relative rounded-[2rem] border-2 border-black bg-white p-7 shadow-[12px_14px_0_rgba(0,0,0,0.12)] sm:p-10"
        >
          <h1 :id="titleId" class="font-title text-4xl tracking-tight">{{ title }}</h1>
          <p v-if="description" class="mt-3 text-neutral-600">{{ description }}</p>
          <div class="mt-8">
            <slot />
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
