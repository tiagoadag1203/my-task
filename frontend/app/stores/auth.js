export const useAuthStore = defineStore('auth', () => {
  const token = useCookie('mytask_token', {
    sameSite: 'lax',
    secure: import.meta.production,
  })
  const user = ref(null)
  const expires_at = ref(null)
  const hydrated = ref(false)
  let hydratePromise = null

  const isAuthenticated = computed(() => Boolean(token.value && user.value))
  const role = computed(() => user.value?.role ?? null)
  const isUser = computed(() => role.value === 'user')
  const isAdmin = computed(() => role.value === 'admin')

  function clearSession() {
    token.value = null
    user.value = null
    expires_at.value = null
    hydrated.value = true
  }

  function applySession(response) {
    token.value = response.access_token
    user.value = response.user
    expires_at.value = response.expires_in
      ? new Date(Date.now() + response.expires_in * 1000).toISOString()
      : null
    hydrated.value = true
  }

  async function login(credentials) {
    const { $api } = useNuxtApp()
    const response = await $api.auth.login(credentials)
    applySession(response)
    return response
  }

  async function register(payload) {
    const { $api } = useNuxtApp()
    const response = await $api.auth.register(payload)
    applySession(response)
    return response
  }

  async function me() {
    const { $api } = useNuxtApp()
    const response = await $api.auth.me()
    user.value = response.data ?? response
    hydrated.value = true
    return user.value
  }

  async function logout() {
    const { $api } = useNuxtApp()

    try {
      if (token.value) {
        await $api.auth.logout()
      }
    } finally {
      clearSession()
    }
  }

  async function hydrate() {
    if (hydrated.value) {
      return user.value
    }

    if (!token.value) {
      clearSession()
      return null
    }

    if (expires_at.value && new Date(expires_at.value).getTime() <= Date.now()) {
      clearSession()
      return null
    }

    if (!hydratePromise) {
      hydratePromise = me()
        .catch(() => {
          clearSession()
          return null
        })
        .finally(() => {
          hydratePromise = null
        })
    }

    return hydratePromise
  }

  return {
    token,
    user,
    expires_at,
    hydrated,
    isAuthenticated,
    role,
    isUser,
    isAdmin,
    login,
    register,
    me,
    logout,
    hydrate,
    clearSession,
  }
})
