import { ref } from 'vue'
import { getInit, type InitData } from '../services/initService'

// Cache global do init
const initCache = ref<InitData | null>(null)
const isLoading = ref(false)
const loadPromise = ref<Promise<InitData | null> | null>(null)

/**
 * Composable para gerenciar o cache global do init
 * Carrega os dados apenas uma vez e reutiliza em todos os componentes
 */
export function useInitCache() {
  const loadInit = async (): Promise<InitData | null> => {
    // Se já temos os dados em cache, retorna imediatamente
    if (initCache.value) {
      return initCache.value
    }

    // Se já está carregando, retorna a promise existente
    if (loadPromise.value) {
      return loadPromise.value
    }

    // Inicia o carregamento
    isLoading.value = true
    loadPromise.value = getInit()
      .then((data) => {
        if (data) {
          initCache.value = data
        }
        return data
      })
      .finally(() => {
        isLoading.value = false
        loadPromise.value = null
      })

    return loadPromise.value
  }

  const clearCache = (): void => {
    initCache.value = null
    loadPromise.value = null
  }

  return {
    initData: initCache,
    isLoading,
    loadInit,
    clearCache
  }
}

