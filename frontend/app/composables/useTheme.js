const STORAGE_KEY = 'mytask-theme'

export function useTheme() {
  const preference = useState('theme:preference', () => 'system')
  const theme = useState('theme:resolved', () => 'light')
  const initialized = useState('theme:initialized', () => false)

  const applyTheme = (nextTheme) => {
    theme.value = nextTheme

    if (import.meta.client) {
      document.documentElement.classList.toggle('dark', nextTheme === 'dark')
      document.documentElement.dataset.theme = nextTheme
    }
  }

  const resolveTheme = () => {
    const nextTheme =
      preference.value === 'system'
        ? window.matchMedia('(prefers-color-scheme: dark)').matches
          ? 'dark'
          : 'light'
        : preference.value

    applyTheme(nextTheme)
  }

  const setTheme = (value) => {
    if (!['light', 'dark', 'system'].includes(value)) return

    preference.value = value
    if (import.meta.client) {
      localStorage.setItem(STORAGE_KEY, value)
      resolveTheme()
    }
  }

  const toggleTheme = () => setTheme(theme.value === 'dark' ? 'light' : 'dark')

  const initializeTheme = () => {
    if (!import.meta.client || initialized.value) return

    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored && ['light', 'dark', 'system'].includes(stored)) preference.value = stored
    resolveTheme()
    initialized.value = true
  }

  return {
    preference: readonly(preference),
    theme: readonly(theme),
    isDark: computed(() => theme.value === 'dark'),
    initialized: readonly(initialized),
    applyTheme,
    resolveTheme,
    setTheme,
    toggleTheme,
    initializeTheme,
  }
}
