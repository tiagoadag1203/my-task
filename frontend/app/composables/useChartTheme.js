export function useChartTheme() {
  const { isDark } = useTheme()

  const colors = computed(() =>
    isDark.value
      ? {
          text: '#fafafa',
          muted: '#a3a3a3',
          grid: '#404040',
          surface: '#171717',
          accent: '#00ffbf',
          todo: '#f97316',
          progress: '#3b82f6',
          pending: '#a3a3a3',
          done: '#00ffbf',
        }
      : {
          text: '#0a0a0a',
          muted: '#525252',
          grid: '#e5e5e5',
          surface: '#ffffff',
          accent: '#00ffbf',
          todo: '#f97316',
          progress: '#3b82f6',
          pending: '#737373',
          done: '#00ffbf',
        },
  )

  const legendOptions = computed(() => ({
    position: 'bottom',
    labels: {
      color: colors.value.text,
      usePointStyle: true,
    },
  }))

  const scaleOptions = computed(() => ({
    ticks: { color: colors.value.muted },
    grid: { color: colors.value.grid },
    border: { color: colors.value.grid },
  }))

  return { isDark, colors, legendOptions, scaleOptions }
}
