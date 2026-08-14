<template>
  <section
    id="inicio"
    class="relative isolate overflow-hidden border-b border-black/10 bg-white"
    aria-labelledby="hero-title"
  >
    <div
      class="pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(to_right,#0000000a_1px,transparent_1px),linear-gradient(to_bottom,#0000000a_1px,transparent_1px)] bg-[size:48px_48px] [mask-image:linear-gradient(to_bottom,black,transparent_80%)]"
      aria-hidden="true"
    />
    <div
      class="pointer-events-none absolute -right-24 top-28 -z-10 size-80 rounded-full bg-accent/20 blur-3xl"
      aria-hidden="true"
    />

    <div
      class="mx-auto grid min-h-screen max-w-7xl items-center gap-16 px-5 pb-20 pt-28 sm:px-8 lg:grid-cols-[1.04fr_0.96fr] lg:px-12 lg:pb-24 lg:pt-32"
    >
      <div class="max-w-3xl">

        <h1
          id="hero-title"
          class="font-hero text-[clamp(3.4rem,8vw,7.2rem)] font-bold leading-[0.91] tracking-[-0.065em] text-black"
        >
          Menos caos.
          <span class="relative inline-block">
            Mais foco.
            <span
              class="absolute -bottom-1 left-1 h-3 w-[98%] -rotate-1 bg-accent -z-10"
              aria-hidden="true"
            />
          </span>
        </h1>

        <p class="mt-8 max-w-2xl text-xl leading-relaxed text-neutral-600 sm:text-2xl">
          Um modelo híbrido de agilidade pessoal que une o fluxo do Kanban à clareza do Scrum
          para transformar planos em progresso.
        </p>

        <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
          <NuxtLink
            to="/em-breve"
            class="group inline-flex items-center justify-center gap-3 rounded-full border-2 border-black bg-black px-7 py-4 font-semibold text-white shadow-accent transition-all hover:-translate-y-1 hover:shadow-accent-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-black"
          >
            Começar em breve
            <svg
              aria-hidden="true"
              class="size-4 transition-transform group-hover:translate-x-1"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M5 12h14m-6-6 6 6-6 6" />
            </svg>
          </NuxtLink>
          <a
            href="#como-funciona"
            class="inline-flex items-center justify-center gap-2 rounded-full px-7 py-4 font-semibold text-black transition-colors hover:bg-neutral-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
          >
            Conhecer o método
            <span aria-hidden="true">↓</span>
          </a>
        </div>
      </div>

      <div class="relative mx-auto w-full max-w-xl lg:mx-0">
        <div
          class="absolute -inset-4 -z-10 rotate-2 rounded-[2.25rem] border-2 border-black bg-accent"
          aria-hidden="true"
        />
        <div
          class="relative overflow-hidden rounded-[2rem] border-2 border-black bg-white p-4 shadow-[12px_14px_0_rgba(0,0,0,0.12)] sm:p-6"
        >
          <div class="mb-6 flex items-center justify-between border-b border-black/10 pb-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                Sprint atual
              </p>
              <p class="mt-1 font-title text-lg text-black">Tirar o projeto do papel</p>
            </div>
            <span class="rounded-full bg-black px-3 py-1.5 text-xs font-semibold text-white">
              7 dias
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 sm:gap-3" aria-label="Exemplo visual de quadro Kanban">
            <div v-for="column in columns" :key="column.title" class="min-w-0">
              <div class="mb-3 flex items-center justify-between gap-1 px-1">
                <p class="truncate text-[10px] font-bold uppercase tracking-wider sm:text-xs">
                  {{ column.title }}
                </p>
                <span
                  v-if="column.limit"
                  class="rounded-full bg-accent px-1.5 py-0.5 text-[9px] font-bold sm:text-[10px]"
                >
                  {{ column.limit }}
                </span>
              </div>
              <div class="space-y-2.5 rounded-xl bg-neutral-100 p-2 sm:p-3">
                <div
                  v-for="task in column.tasks"
                  :key="task"
                  class="min-h-20 rounded-lg border border-black/10 bg-white p-2.5 text-[11px] leading-snug shadow-sm sm:p-3 sm:text-xs"
                >
                  <span class="mb-2 block h-1.5 w-7 rounded-full bg-accent" aria-hidden="true" />
                  {{ task }}
                </div>
              </div>
            </div>
          </div>

          <div class="mt-5 flex items-center justify-between rounded-xl bg-black px-4 py-3 text-white">
            <span class="text-xs text-white/65">Progresso da sprint</span>
            <div class="mx-4 h-1.5 flex-1 overflow-hidden rounded-full bg-white/20">
              <div class="h-full w-2/3 rounded-full bg-accent" />
            </div>
            <span class="text-xs font-bold">67%</span>
          </div>
        </div>

        <div
          class="floating-card absolute -bottom-7 -left-3 rounded-2xl border-2 border-black bg-white px-4 py-3 shadow-[5px_5px_0_#000] sm:-left-9"
        >
          <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">WIP saudável</p>
          <p class="mt-1 font-hero text-2xl font-bold">2 <span class="text-sm text-neutral-400">/ 3</span></p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const columns = [
  {
    title: 'A fazer',
    tasks: ['Definir objetivo', 'Listar prioridades'],
  },
  {
    title: 'Em curso',
    limit: 'WIP 3',
    tasks: ['Criar protótipo', 'Validar fluxo'],
  },
  {
    title: 'Concluído',
    tasks: ['Mapear problema'],
  },
]
</script>
