<script setup lang="ts">
useSeoMeta({
  title: 'Em breve — MyTask',
  description: 'O MyTask está sendo preparado. Em breve, uma nova forma de organizar seu foco.',
  robots: 'noindex, nofollow',
})
</script>

<template>
  <main
    class="relative isolate grid min-h-screen place-items-center overflow-hidden bg-white px-5 py-16 text-black"
  >
    <div
      class="absolute inset-0 -z-10 bg-[linear-gradient(to_right,#0000000a_1px,transparent_1px),linear-gradient(to_bottom,#0000000a_1px,transparent_1px)] bg-[size:48px_48px]"
      aria-hidden="true"
    />
    <div
      class="absolute left-1/2 top-1/2 -z-10 size-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-accent/25 blur-3xl"
      aria-hidden="true"
    />

    <section class="w-full max-w-2xl text-center" aria-labelledby="coming-soon-title">
      <NuxtLink
        to="/"
        class="mb-12 inline-flex items-center gap-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-black"
        aria-label="MyTask — voltar à página inicial"
      >
        <span
          class="grid size-11 place-items-center rounded-xl border-2 border-black bg-black font-hero text-lg font-bold text-white shadow-accent-sm"
          aria-hidden="true"
        >
          M
        </span>
        <span class="font-title text-2xl">MyTask</span>
      </NuxtLink>

      <div
        class="rounded-[2rem] border-2 border-black bg-white p-8 shadow-[10px_10px_0_#000] sm:p-14"
      >
        <span
          class="inline-flex items-center gap-2 rounded-full bg-neutral-100 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em]"
        >
          <span class="size-2 animate-pulse rounded-full bg-accent ring-1 ring-black" />
          Em desenvolvimento
        </span>

        <h1
          id="coming-soon-title"
          class="mt-8 font-hero text-5xl font-bold leading-none tracking-tight sm:text-7xl"
        >
          Em breve.
        </h1>
        <p class="mx-auto mt-6 max-w-lg text-lg leading-relaxed text-neutral-600">
          As áreas de cadastro e acesso ainda estão sendo preparadas. Logo você poderá colocar
          seus planos em movimento com o MyTask.
        </p>

        <NuxtLink
          to="/"
          class="mt-9 inline-flex items-center justify-center gap-2 rounded-full border-2 border-black bg-black px-7 py-4 font-semibold text-white shadow-accent transition-transform hover:-translate-y-1 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-black"
        >
          <span aria-hidden="true">←</span>
          Voltar para o início
        </NuxtLink>
      </div>
    </section>
  </main>
</template>
