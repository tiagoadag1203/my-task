import { useAuthStore } from '~/stores/auth'

export default defineNuxtRouteMiddleware(async (to) => {
  const isDashboardRoute = to.path === '/dashboard' || to.path.startsWith('/dashboard/')

  if (!isDashboardRoute) {
    return
  }

  const auth = useAuthStore()
  await auth.hydrate()

  if (!auth.isAuthenticated) {
    return navigateTo('/login')
  }
})
