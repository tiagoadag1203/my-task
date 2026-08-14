<template>
  <section id="como-funciona" class="scroll-mt-20 bg-white py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12">
      <div class="mx-auto max-w-3xl text-center">
        <p class="section-eyebrow">Uma nova forma de produzir</p>
        <h2 class="section-title mt-5">Organizar não precisa ser mais uma tarefa.</h2>
        <p class="mt-6 text-lg leading-relaxed text-neutral-600 sm:text-xl">
          O MyTask adapta práticas ágeis ao cotidiano para você enxergar prioridades, limitar
          distrações e avançar com intenção.
        </p>
      </div>

      <div class="mt-16 grid gap-5 md:grid-cols-3">
        <article
          v-for="(benefit, index) in benefits"
          :key="benefit.title"
          class="group rounded-3xl border border-black/15 bg-white p-7 transition-all hover:-translate-y-1 hover:border-black hover:shadow-[6px_6px_0_#000]"
        >
          <span
            class="grid size-11 place-items-center rounded-2xl bg-neutral-100 font-hero text-sm font-bold transition-colors group-hover:bg-accent"
          >
            0{{ index + 1 }}
          </span>
          <h3 class="mt-8 font-title text-xl text-black">{{ benefit.title }}</h3>
          <p class="mt-3 leading-relaxed text-neutral-600">{{ benefit.description }}</p>
        </article>
      </div>

      <div class="my-24 flex items-center gap-4" aria-hidden="true">
        <span class="h-px flex-1 bg-black/15" />
        <span class="size-2 rotate-45 bg-accent ring-1 ring-black" />
        <span class="h-px flex-1 bg-black/15" />
      </div>

      <article
        id="kanban"
        class="scroll-mt-28 grid items-center gap-14 lg:grid-cols-2 lg:gap-20"
        aria-labelledby="kanban-title"
      >
        <div>
          <p class="section-eyebrow">01 · Personal Kanban</p>
          <h2 id="kanban-title" class="section-title mt-5">Visualize o agora. Proteja seu foco.</h2>
          <p class="mt-6 text-lg leading-relaxed text-neutral-600">
            Três colunas transformam tarefas soltas em um fluxo claro. O limite de trabalho em
            andamento evita que tudo pareça urgente ao mesmo tempo.
          </p>

          <ul class="mt-8 space-y-5">
            <li v-for="item in kanbanPoints" :key="item.title" class="flex gap-4">
              <span
                class="mt-1 grid size-7 shrink-0 place-items-center rounded-full bg-black text-sm text-accent"
                aria-hidden="true"
              >
                ✓
              </span>
              <div>
                <h3 class="font-title text-base text-black">{{ item.title }}</h3>
                <p class="mt-1 text-neutral-600">{{ item.description }}</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="relative rounded-[2rem] border-2 border-black bg-neutral-100 p-5 sm:p-8">
          <div class="absolute -right-3 -top-3 size-8 rounded-full border-2 border-black bg-accent" />
          <div class="mb-6 flex items-end justify-between">
            <div>
              <p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">Meu dia</p>
              <p class="mt-1 font-title text-xl">Fluxo de tarefas</p>
            </div>
            <p class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold shadow-sm">5 tarefas</p>
          </div>

          <div class="space-y-3">
            <div
              v-for="(task, index) in kanbanTasks"
              :key="task"
              class="flex items-center gap-4 rounded-2xl border border-black/10 bg-white p-4 shadow-sm"
            >
              <span
                class="grid size-8 shrink-0 place-items-center rounded-lg text-xs font-bold"
                :class="index < 2 ? 'bg-accent text-black' : 'bg-neutral-100 text-neutral-500'"
              >
                {{ index < 2 ? '→' : '·' }}
              </span>
              <span class="flex-1 text-sm font-semibold sm:text-base">{{ task }}</span>
              <span v-if="index < 2" class="text-xs font-semibold text-neutral-400">Em curso</span>
            </div>
          </div>

          <div class="mt-5 flex items-center gap-3 rounded-2xl border border-black bg-black p-4 text-white">
            <span class="grid size-9 place-items-center rounded-full bg-accent font-hero font-bold text-black">
              2
            </span>
            <p class="text-sm">
              Você está dentro do limite.
              <span class="block text-white/60">Máximo de 3 tarefas em andamento.</span>
            </p>
          </div>
        </div>
      </article>

      <article
        id="scrum"
        class="mt-32 scroll-mt-28 grid items-center gap-14 lg:grid-cols-2 lg:gap-20"
        aria-labelledby="scrum-title"
      >
        <div class="order-2 lg:order-1">
          <div class="rounded-[2rem] border-2 border-black bg-black p-5 text-white sm:p-8">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/50">Projeto pessoal</p>
                <h3 class="mt-2 font-title text-xl">Lançar meu portfólio</h3>
              </div>
              <span class="size-3 rounded-full bg-accent shadow-accent-glow" />
            </div>

            <div class="relative mt-10">
              <div
                class="absolute left-[15px] top-4 h-[calc(100%-2rem)] w-px bg-white/20"
                aria-hidden="true"
              />
              <div v-for="(step, index) in scrumSteps" :key="step.title" class="relative flex gap-5 pb-8 last:pb-0">
                <span
                  class="z-10 grid size-8 shrink-0 place-items-center rounded-full border text-xs font-bold"
                  :class="
                    index < 2
                      ? 'border-accent bg-accent text-black'
                      : 'border-white/25 bg-black text-white/50'
                  "
                >
                  {{ index + 1 }}
                </span>
                <div class="pt-1">
                  <p class="font-title text-base">{{ step.title }}</p>
                  <p class="mt-1 text-sm text-white/50">{{ step.description }}</p>
                </div>
              </div>
            </div>

            <div class="mt-9 rounded-2xl border border-white/15 bg-white/5 p-4">
              <div class="flex justify-between text-xs">
                <span class="text-white/50">Sprint 02 · Em andamento</span>
                <span class="font-bold text-accent">60%</span>
              </div>
              <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                <div class="h-full w-3/5 rounded-full bg-accent" />
              </div>
            </div>
          </div>
        </div>

        <div class="order-1 lg:order-2">
          <p class="section-eyebrow">02 · Scrum Pessoal</p>
          <h2 id="scrum-title" class="section-title mt-5">Grandes metas, próximos passos claros.</h2>
          <p class="mt-6 text-lg leading-relaxed text-neutral-600">
            Tire objetivos complexos da cabeça e organize-os em projetos, backlogs e sprints.
            Cada ciclo tem um foco definido e espaço para aprender com o progresso.
          </p>

          <div class="mt-9 grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-black/15 p-5">
              <p class="font-hero text-3xl font-bold">1 ciclo</p>
              <p class="mt-2 text-sm text-neutral-600">de cada vez, com uma meta visível.</p>
            </div>
            <div class="rounded-2xl border border-black/15 p-5">
              <p class="font-hero text-3xl font-bold">100%</p>
              <p class="mt-2 text-sm text-neutral-600">do foco na sprint que está ativa.</p>
            </div>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup lang="ts">
const benefits = [
  {
    title: 'Clareza antes da ação',
    description: 'Transforme ideias e obrigações em prioridades que você consegue enxergar.',
  },
  {
    title: 'Menos sobrecarga',
    description: 'Limite o trabalho em andamento para reduzir trocas de contexto e fadiga.',
  },
  {
    title: 'Evolução contínua',
    description: 'Planeje em ciclos curtos, acompanhe o avanço e ajuste o caminho.',
  },
]

const kanbanPoints = [
  {
    title: 'Um fluxo fácil de entender',
    description: 'A Fazer, Em Progresso e Concluído — sem complexidade desnecessária.',
  },
  {
    title: 'WIP máximo de três',
    description: 'Um lembrete educativo para terminar antes de começar algo novo.',
  },
]

const kanbanTasks = ['Revisar capítulo do TCC', 'Preparar apresentação', 'Organizar referências']

const scrumSteps = [
  { title: 'Projeto', description: 'Defina o resultado que deseja alcançar.' },
  { title: 'Backlog', description: 'Reúna e priorize tudo que precisa acontecer.' },
  { title: 'Sprint', description: 'Escolha o foco do próximo ciclo.' },
  { title: 'Progresso', description: 'Conclua, aprenda e planeje novamente.' },
]
</script>
