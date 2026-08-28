let resolver = null

export function useConfirm() {
  const confirmation = useState('global:confirmation', () => ({
    open: false,
    title: 'Confirmar ação',
    message: '',
    confirmLabel: 'Confirmar',
    cancelLabel: 'Cancelar',
    tone: 'danger',
  }))

  const ask = (options = {}) =>
    new Promise((resolve) => {
      if (resolver) resolver(false)
      resolver = resolve
      confirmation.value = {
        open: true,
        title: 'Confirmar ação',
        message: '',
        confirmLabel: 'Confirmar',
        cancelLabel: 'Cancelar',
        tone: 'danger',
        ...options,
      }
    })

  const settle = (result) => {
    confirmation.value = { ...confirmation.value, open: false }
    resolver?.(result)
    resolver = null
  }

  return {
    confirmation: readonly(confirmation),
    ask,
    confirm: () => settle(true),
    cancel: () => settle(false),
  }
}
