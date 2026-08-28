import { describe, expect, it, vi } from 'vitest'
import { createRepositories } from '../../app/repositories'

describe('repositórios da API', () => {
  it('expõe todos os domínios sobre o mesmo cliente', () => {
    const repositories = createRepositories(vi.fn())
    expect(Object.keys(repositories)).toEqual([
      'auth',
      'dashboard',
      'projects',
      'sprints',
      'tasks',
      'admin',
    ])
  })

  it('envia credenciais e consulta a sessão pelos endpoints corretos', async () => {
    const api = vi.fn().mockResolvedValue({ access_token: 'token' })
    const { auth } = createRepositories(api)

    await auth.login({ email: 'user@example.com', password: 'secret123' })
    await auth.me()

    expect(api).toHaveBeenNthCalledWith(1, '/auth/login', {
      method: 'POST',
      body: { email: 'user@example.com', password: 'secret123' },
    })
    expect(api).toHaveBeenNthCalledWith(2, '/auth/me')
  })

  it('consulta as estatísticas agregadas do participante', async () => {
    const api = vi.fn()
    const { dashboard } = createRepositories(api)

    await dashboard.analytics()

    expect(api).toHaveBeenCalledWith('/dashboard/analytics')
  })

  it('sempre envia o sprint de destino no contrato de migração', async () => {
    const api = vi.fn()
    const { tasks } = createRepositories(api)

    await tasks.migrate(10, 22)

    expect(api).toHaveBeenCalledWith('/tasks/10/migrate', {
      method: 'PUT',
      body: { current_sprint_id: 22 },
    })
  })

  it('solicita exportação administrativa como blob', async () => {
    const api = vi.fn()
    const { admin } = createRepositories(api)

    await admin.exportCsv()

    expect(api).toHaveBeenCalledWith('/admin/export', { responseType: 'blob' })
  })
})
