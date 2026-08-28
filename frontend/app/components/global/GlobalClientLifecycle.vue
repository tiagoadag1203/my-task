<script setup>
const { preference, initializeTheme, resolveTheme } = useTheme()
let mediaQuery

const handleSystemTheme = () => {
  if (preference.value === 'system') resolveTheme()
}

onMounted(() => {
  initializeTheme()
  mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  mediaQuery.addEventListener('change', handleSystemTheme)
})

onUnmounted(() => {
  mediaQuery?.removeEventListener('change', handleSystemTheme)
})
</script>

<template>
  <span class="sr-only" aria-hidden="true" />
</template>
