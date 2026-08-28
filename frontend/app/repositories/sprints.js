const projectSprintsPath = (projectId) =>
  `/projects/${encodeURIComponent(String(projectId))}/sprints`

const sprintPath = (sprintId, suffix = '') =>
  `/sprints/${encodeURIComponent(String(sprintId))}${suffix}`

export const createSprintsRepository = (api) => ({
  list(projectId, query) {
    return api(projectSprintsPath(projectId), { query })
  },

  create(projectId, payload) {
    return api(projectSprintsPath(projectId), {
      method: 'POST',
      body: payload,
    })
  },

  get(sprintId) {
    return api(sprintPath(sprintId))
  },

  update(sprintId, payload) {
    return api(sprintPath(sprintId), {
      method: 'PUT',
      body: payload,
    })
  },

  remove(sprintId) {
    return api(sprintPath(sprintId), {
      method: 'DELETE',
    })
  },

  updateStatus(sprintId, status) {
    return api(sprintPath(sprintId, '/status'), {
      method: 'PUT',
      body: { status },
    })
  },

  tasks(sprintId, query) {
    return api(sprintPath(sprintId, '/tasks'), { query })
  },
})
