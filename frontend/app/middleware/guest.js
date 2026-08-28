import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async () => {
  const auth = useAuthStore()
  await auth.hydrate()

  if (auth.isAuthenticated) {
    return navigateTo('/dashboard')
  }
})
