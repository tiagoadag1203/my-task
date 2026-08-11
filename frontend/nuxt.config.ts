export default defineNuxtConfig({
  compatibilityDate: '2026-08-11',
  ssr: false,
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss', '@nuxt/eslint', '@pinia/nuxt'],
  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    public: {
      apiBase: '/api/v1',
    },
  },
  app: {
    head: {
      title: 'MyTask',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'MyTask - plataforma de produtividade pessoal.' },
      ],
    },
  },
})