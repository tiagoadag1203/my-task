export function useModal() {
  const modal = useState('global:modal', () => ({
    open: false,
    title: '',
    description: '',
    message: '',
    size: 'md',
  }))

  const open = (options = {}) => {
    modal.value = {
      open: true,
      title: '',
      description: '',
      message: '',
      size: 'md',
      ...options,
    }
  }

  const close = () => {
    modal.value = { ...modal.value, open: false }
  }

  return { modal: readonly(modal), open, close }
}
