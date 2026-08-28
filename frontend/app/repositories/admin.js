const userPath = (userId, suffix = '') =>
  `/admin/users/${encodeURIComponent(String(userId))}${suffix}`

export const createAdminRepository = (api) => ({
  summary() {
    return api('/admin/summary')
  },

  analytics() {
    return api('/admin/analytics')
  },

  users(query) {
    return api('/admin/users', { query })
  },

  resetPassword(userId) {
    return api(userPath(userId, '/reset-password'), {
      method: 'PUT',
    })
  },

  exportCsv() {
    return api('/admin/export', {
      responseType: 'blob',
    })
  },
})
