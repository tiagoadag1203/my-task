let toastId = 0

export function useToast() {
  const toasts = useState('global:toasts', () => [])

  const remove = (id) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
  }

  const show = (options) => {
    const normalized = typeof options === 'string' ? { message: options } : options
    const toast = {
      id: ++toastId,
      type: 'info',
      duration: 5000,
      title: '',
      message: '',
      ...normalized,
    }

    toasts.value.push(toast)
    return toast.id
  }

  return {
    toasts: readonly(toasts),
    show,
    success: (message, options = {}) => show({ ...options, message, type: 'success' }),
    error: (message, options = {}) => show({ ...options, message, type: 'error' }),
    warning: (message, options = {}) => show({ ...options, message, type: 'warning' }),
    info: (message, options = {}) => show({ ...options, message, type: 'info' }),
    remove,
    clear: () => {
      toasts.value = []
    },
  }
}
