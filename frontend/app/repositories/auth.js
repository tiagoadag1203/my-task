export const createAuthRepository = (api) => ({
  login(credentials) {
    return api('/auth/login', {
      method: 'POST',
      body: credentials,
    })
  },

  register(payload) {
    return api('/auth/register', {
      method: 'POST',
      body: payload,
    })
  },

  me() {
    return api('/auth/me')
  },

  logout() {
    return api('/auth/logout', {
      method: 'POST',
    })
  },
})
