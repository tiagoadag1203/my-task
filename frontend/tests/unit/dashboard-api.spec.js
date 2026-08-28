import { describe, expect, it, vi } from 'vitest'
import {
  callRepository,
  errorMessage,
  paginationOf,
  unwrapData,
  unwrapList,
} from '../../app/components/dashboard/api'

describe('utilitários de integração do dashboard', () => {
  it('normaliza resources e coleções Laravel', () => {
    expect(unwrapData({ data: { id: 1 } })).toEqual({ id: 1 })
    expect(unwrapList({ data: [{ id: 1 }] })).toEqual([{ id: 1 }])
    expect(unwrapList({ data: { data: [{ id: 2 }] } })).toEqual([{ id: 2 }])
  })

  it('preserva metadados de paginação', () => {
    expect(paginationOf({ data: [], meta: { current_page: 2, last_page: 4, total: 48 } }))
      .toEqual({ current_page: 2, last_page: 4, total: 48 })
  })

  it('usa apenas métodos existentes no repositório', async () => {
    const list = vi.fn().mockResolvedValue(['ok'])

    await expect(callRepository({ list }, ['index', 'list'])).resolves.toEqual(['ok'])
    expect(list).toHaveBeenCalledOnce()
  })

  it('prioriza a mensagem retornada pela API', () => {
    expect(errorMessage({ data: { message: 'Limite WIP atingido.' } })).toBe(
      'Limite WIP atingido.',
    )
  })
})
