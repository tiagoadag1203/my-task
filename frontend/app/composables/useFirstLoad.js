/**
 * Distingue o primeiro carregamento de um useAsyncData das atualizações
 * seguintes (refresh). Assim o skeleton e o bloco de erro aparecem só
 * enquanto ainda não há dados, e um refresh após uma ação atualiza a tela
 * em segundo plano, sem substituir o conteúdo já exibido.
 */
export function useFirstLoad(status, error) {
  const toast = useToast()
  const loaded = ref(status.value === 'success')

  watch(status, (value) => {
    if (value === 'success') loaded.value = true
  })

  if (error) {
    watch(error, (value) => {
      if (value && loaded.value) toast.warning('Não foi possível atualizar os dados. Tente novamente.')
    })
  }

  return {
    loaded: readonly(loaded),
    isFirstLoading: computed(() => !loaded.value && status.value === 'pending'),
    showError: computed(() => !loaded.value && Boolean(error?.value)),
  }
}
