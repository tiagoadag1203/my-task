export function useLoader() {
  const pending = useState('global:loader:pending', () => 0)
  const message = useState('global:loader:message', () => 'Carregando…')

  const start = (text = 'Carregando…') => {
    pending.value += 1
    message.value = text
  }

  const stop = () => {
    pending.value = Math.max(0, pending.value - 1)
    if (pending.value === 0) message.value = 'Carregando…'
  }

  const wrap = async (operation, text) => {
    start(text)
    try {
      return await operation()
    } finally {
      stop()
    }
  }

  return {
    isLoading: computed(() => pending.value > 0),
    message: readonly(message),
    start,
    stop,
    wrap,
    reset: () => {
      pending.value = 0
    },
  }
}
