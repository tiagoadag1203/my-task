<script setup lang="ts">
useSeoMeta({
  title: 'MyTask — Agilidade pessoal para o que importa',
  description:
    'Organize sua rotina com Personal Kanban e transforme grandes metas em progresso com Scrum Pessoal.',
  ogTitle: 'MyTask — Menos caos. Mais foco.',
  ogDescription:
    'Um modelo híbrido de agilidade pessoal que une Kanban e Scrum para transformar planos em progresso.',
  ogType: 'website',
  twitterCard: 'summary_large_image',
})
</script>

<template>
  <div class="min-h-screen bg-white text-black">
    <LandingNavbar />
    <main>
      <LandingHero />
      <LandingMethods />
      <LandingCta />
    </main>
    <LandingFooter />
  </div>
</template>
