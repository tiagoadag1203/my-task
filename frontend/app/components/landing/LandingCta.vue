<template>
  <section class="bg-white px-5 py-16 sm:px-8 sm:py-24 lg:px-12">
    <div
      class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-black px-6 py-16 text-center text-white sm:px-12 sm:py-20"
    >
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,color-mix(in_srgb,var(--color-accent)_20%,transparent),transparent_35%)]"
        aria-hidden="true"
      />
      <div
        class="absolute -left-10 -top-10 size-32 rounded-full border border-white/15"
        aria-hidden="true"
      />
      <div
        class="absolute -bottom-16 -right-12 size-48 rounded-full border border-accent/40"
        aria-hidden="true"
      />

      <div class="relative mx-auto max-w-3xl">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-accent">Seu foco começa aqui</p>
        <h2 class="mt-5 font-title text-3xl leading-tight sm:text-5xl">
          Produtividade não é fazer tudo. É avançar no que importa.
        </h2>
        <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/65">
          O MyTask está sendo preparado para tornar metodologias ágeis simples, pessoais e parte
          da sua rotina.
        </p>
        <NuxtLink
          to="/em-breve"
          class="mt-9 inline-flex items-center justify-center rounded-full bg-white px-7 py-4 font-semibold text-black shadow-accent transition-transform hover:-translate-y-1 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent"
        >
          Quero conhecer o MyTask
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
