import { ref, computed } from 'vue'
import type { Period } from '../types'
import { getDefaultPeriod } from '../services/calendarioService'

export interface FilterState {
  segmento?: string
  diretoria?: string
  gerencia?: string
  agencia?: string
  ggestao?: string
  gerente?: string
  familia?: string
  indicador?: string
  subindicador?: string
  status?: string
}

// Estado global dos filtros
const filterState = ref<FilterState>({})
// Inicializa período com o período padrão
const period = ref<Period>(getDefaultPeriod())

/**
 * Composable global para gerenciar filtros e período
 * Permite que múltiplos componentes compartilhem o mesmo estado de filtros
 */
export function useGlobalFilters() {
  const updateFilter = (key: keyof FilterState, value: string | undefined): void => {
    if (value === '' || value === 'Todos' || value === 'Todas') {
      filterState.value[key] = undefined
    } else {
      filterState.value[key] = value
    }
  }

  const updatePeriod = (newPeriod: Period): void => {
    period.value = { ...newPeriod }
  }

  const clearFilters = (): void => {
    filterState.value = {}
  }

  const clearFilter = (key: keyof FilterState): void => {
    filterState.value[key] = undefined
  }

  return {
    filterState: computed(() => filterState.value),
    period: computed(() => period.value),
    updateFilter,
    updatePeriod,
    clearFilters,
    clearFilter
  }
}

