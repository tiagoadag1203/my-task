const projectPath = (projectId, suffix = '') =>
  `/projects/${encodeURIComponent(String(projectId))}${suffix}`

export const createProjectsRepository = (api) => ({
  list(query) {
    return api('/projects', { query })
  },

  create(payload) {
    return api('/projects', {
      method: 'POST',
      body: payload,
    })
  },

  get(projectId) {
    return api(projectPath(projectId))
  },

  update(projectId, payload) {
    return api(projectPath(projectId), {
      method: 'PUT',
      body: payload,
    })
  },

  remove(projectId) {
    return api(projectPath(projectId), {
      method: 'DELETE',
    })
  },

  backlog(projectId, query) {
    return api(projectPath(projectId, '/backlog'), { query })
  },
})
