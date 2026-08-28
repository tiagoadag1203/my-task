const taskPath = (taskId, suffix = '') =>
  `/tasks/${encodeURIComponent(String(taskId))}${suffix}`

const projectBacklogPath = (projectId) =>
  `/projects/${encodeURIComponent(String(projectId))}/backlog`

export const createTasksRepository = (api) => ({
  kanban(query) {
    return api('/kanban/tasks', { query })
  },

  createKanban(payload) {
    return api('/kanban/tasks', {
      method: 'POST',
      body: payload,
    })
  },

  backlog(projectId, query) {
    return api(projectBacklogPath(projectId), { query })
  },

  createBacklog(projectId, payload) {
    return api(projectBacklogPath(projectId), {
      method: 'POST',
      body: payload,
    })
  },

  sprint(sprintId, query) {
    return api(`/sprints/${encodeURIComponent(String(sprintId))}/tasks`, { query })
  },

  update(taskId, payload) {
    return api(taskPath(taskId), {
      method: 'PUT',
      body: payload,
    })
  },

  remove(taskId) {
    return api(taskPath(taskId), {
      method: 'DELETE',
    })
  },

  migrate(taskId, currentSprintId) {
    return api(taskPath(taskId, '/migrate'), {
      method: 'PUT',
      body: { current_sprint_id: currentSprintId },
    })
  },
})
