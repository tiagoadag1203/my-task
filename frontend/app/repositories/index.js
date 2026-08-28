import { createAdminRepository } from './admin'
import { createAuthRepository } from './auth'
import { createDashboardRepository } from './dashboard'
import { createProjectsRepository } from './projects'
import { createSprintsRepository } from './sprints'
import { createTasksRepository } from './tasks'

export const createRepositories = (api) => ({
  auth: createAuthRepository(api),
  dashboard: createDashboardRepository(api),
  projects: createProjectsRepository(api),
  sprints: createSprintsRepository(api),
  tasks: createTasksRepository(api),
  admin: createAdminRepository(api),
})
