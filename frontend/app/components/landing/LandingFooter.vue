<template>
  <footer class="border-t border-black/10 bg-white">
    <div
      class="mx-auto grid max-w-7xl gap-10 px-5 py-12 sm:px-8 md:grid-cols-[1fr_auto] md:items-end lg:px-12"
    >
      <div>
        <a class="inline-flex items-center gap-3" href="#inicio" aria-label="Voltar ao início">
          <span
            class="grid size-9 place-items-center rounded-xl bg-black font-hero font-bold text-white"
            aria-hidden="true"
          >
            M
          </span>
          <span class="font-title text-xl">MyTask</span>
        </a>
        <p class="mt-5 max-w-md leading-relaxed text-neutral-600">
          Metodologias ágeis aplicadas à produtividade pessoal para mais clareza, foco e evolução
          contínua.
        </p>
      </div>

      <nav class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold" aria-label="Navegação do rodapé">
        <a class="transition-colors hover:text-neutral-500" href="#como-funciona">Como funciona</a>
        <a class="transition-colors hover:text-neutral-500" href="#kanban">Kanban</a>
        <a class="transition-colors hover:text-neutral-500" href="#scrum">Scrum</a>
      </nav>
    </div>

    <div class="border-t border-black/10">
      <div
        class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-6 text-xs text-neutral-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12"
      >
        <p>MyTask · Projeto acadêmico de produtividade pessoal.</p>
        <p>Scrum Pessoal + Personal Kanban</p>
      </div>
    </div>
  </footer>
</template>
