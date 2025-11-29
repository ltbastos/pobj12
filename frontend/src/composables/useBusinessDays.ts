import { ref, computed } from 'vue'
import { useGlobalFilters } from './useGlobalFilters'
import { useResumoData } from './useResumoData'

export interface BusinessSnapshot {
  total: number
  elapsed: number
  remaining: number
  monthStart: string
  monthEnd: string
  today: string
}

export function useBusinessDays() {
  const calendario = ref<any[]>([])
  const { filterState, period } = useGlobalFilters()
  const resumo = useResumoData(filterState, period)

  const loadCalendario = async () => {
    await resumo.loadResumo()
  }

  const getCurrentMonthBusinessSnapshot = computed<BusinessSnapshot>(() => resumo.businessSnapshot.value)

  return {
    calendario,
    loading: resumo.loading,
    loadCalendario,
    getCurrentMonthBusinessSnapshot
  }
}

