import { ref } from 'vue'
import { getCalendario, type CalendarioItem } from '../services/calendarioService'

// Cache global do calendário
const calendarioCache = ref<CalendarioItem[]>([])
const isLoading = ref(false)
const loadPromise = ref<Promise<CalendarioItem[] | null> | null>(null)

/**
 * Composable para gerenciar o cache global do calendário
 * Carrega os dados apenas uma vez e reutiliza em todos os componentes
 */
export function useCalendarioCache() {
  const loadCalendario = async (): Promise<CalendarioItem[] | null> => {
    // Se já temos os dados em cache, retorna imediatamente
    if (calendarioCache.value.length > 0) {
      return calendarioCache.value
    }

    // Se já está carregando, retorna a promise existente
    if (loadPromise.value) {
      return loadPromise.value
    }

    // Inicia o carregamento
    isLoading.value = true
    loadPromise.value = getCalendario()
      .then((data) => {
        if (data) {
          calendarioCache.value = data
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
    calendarioCache.value = []
    loadPromise.value = null
  }

  return {
    calendarioData: calendarioCache,
    isLoading,
    loadCalendario,
    clearCache
  }
}

