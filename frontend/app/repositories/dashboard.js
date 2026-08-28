export const createDashboardRepository = (api) => ({
  analytics() {
    return api('/dashboard/analytics')
  },

  health() {
    return api('/health')
  },
})
