import { createRepositories } from '~/repositories'
import { useAuthStore } from '~/stores/auth'

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  let isRedirectingAfterUnauthorized = false

  const apiFetch = $fetch.create({
    baseURL: config.public.apiBase,

    onRequest({ options }) {
      const auth = useAuthStore(nuxtApp.$pinia)

      if (!auth.token) {
        return
      }

      const headers = new Headers(options.headers)
      headers.set('Authorization', `Bearer ${auth.token}`)
      options.headers = headers
    },

    async onResponseError({ response }) {
      if (response.status !== 401) {
        return
      }

      const auth = useAuthStore(nuxtApp.$pinia)
      auth.clearSession()

      if (
        import.meta.client &&
        !isRedirectingAfterUnauthorized &&
        nuxtApp.$router.currentRoute.value.path !== '/login'
      ) {
        isRedirectingAfterUnauthorized = true

        try {
          await nuxtApp.runWithContext(() => navigateTo('/login'))
        } finally {
          isRedirectingAfterUnauthorized = false
        }
      }
    },
  })

  return {
    provide: {
      api: createRepositories(apiFetch),
    },
  }
})
