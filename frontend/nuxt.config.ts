import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-08-11',
  ssr: false,
  devtools: { enabled: true },
  modules: ['@nuxt/eslint', '@pinia/nuxt'],
  css: ['~/assets/css/main.css'],
  vite: {
    plugins: [tailwindcss()],
  },
  runtimeConfig: {
    public: {
      apiBase: '/api',
      tamFormUrl: '',
      appTimezone: 'America/Sao_Paulo',
    },
  },
  app: {
    head: {
      title: 'MyTask',
      htmlAttrs: {
        lang: 'pt-BR',
      },
      script: [
        {
          innerHTML:
            "(function(){try{var p=localStorage.getItem('mytask-theme')||'system';var d=p==='dark'||(p!=='light'&&matchMedia('(prefers-color-scheme: dark)').matches);document.documentElement.classList.toggle('dark',d);document.documentElement.dataset.theme=d?'dark':'light';}catch(e){}})();",
        },
      ],
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'MyTask - plataforma de produtividade pessoal.' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: 'anonymous' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600;700&family=Rubik:wght@500;600;700&family=Spinnaker&display=swap',
        },
      ],
    },
  },
})