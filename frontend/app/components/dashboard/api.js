export function unwrapData(response) {
  return response?.data ?? response ?? null
}

export function unwrapList(response) {
  const payload = unwrapData(response)
  return Array.isArray(payload) ? payload : (payload?.data ?? [])
}

export function paginationOf(response) {
  const payload = response?.meta ? response : unwrapData(response)
  return payload?.meta ?? {
    current_page: payload?.current_page ?? 1,
    last_page: payload?.last_page ?? 1,
    total: payload?.total ?? unwrapList(response).length,
  }
}

export async function callRepository(repository, methods, ...args) {
  for (const method of methods) {
    if (typeof repository?.[method] === 'function') {
      return repository[method](...args)
    }
  }

  throw new Error(`Operação de API indisponível (${methods.join(', ')}).`)
}

export function errorMessage(error, fallback = 'Não foi possível concluir a operação.') {
  return (
    error?.data?.message ??
    error?.response?._data?.message ??
    error?.message ??
    fallback
  )
}

export function formatDate(value) {
  if (!value) return '—'
  const normalized = /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T12:00:00-03:00` : value
  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'medium',
    timeZone: 'America/Sao_Paulo',
  }).format(new Date(normalized))
}
