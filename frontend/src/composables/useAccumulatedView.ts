import { ref } from 'vue'
import type { Period } from '../types'

export interface AccumulatedViewOption {
  value: 'mensal' | 'semestral' | 'anual'
  label: string
  monthsBack: number
}

export const ACCUMULATED_VIEW_OPTIONS: AccumulatedViewOption[] = [
  { value: 'mensal', label: 'Mensal', monthsBack: 0 },
  { value: 'semestral', label: 'Semestral', monthsBack: 5 },
  { value: 'anual', label: 'Anual', monthsBack: 11 }
]

export function computeAccumulatedPeriod(
  view: 'mensal' | 'semestral' | 'anual',
  referenceEndISO?: string
): Period {
  const today = new Date()
  const todayISO = today.toISOString().split('T')[0]
  
  const endISO = referenceEndISO || todayISO
  const endDate = new Date(endISO + 'T00:00:00Z')
  
  if (isNaN(endDate.getTime())) {
    const fallback = new Date()
    const fallbackEnd = fallback.toISOString().split('T')[0]
    return {
      start: `${fallback.getFullYear()}-${String(fallback.getMonth() + 1).padStart(2, '0')}-01`,
      end: fallbackEnd || `${fallback.getFullYear()}-${String(fallback.getMonth() + 1).padStart(2, '0')}-${String(fallback.getDate()).padStart(2, '0')}`
    }
  }
  
  let startDate: Date
  
  if (view === 'anual') {
    startDate = new Date(Date.UTC(endDate.getUTCFullYear(), 0, 1))
  } else if (view === 'mensal') {
    startDate = new Date(Date.UTC(endDate.getUTCFullYear(), endDate.getUTCMonth(), 1))
  } else {
    const monthsBack = ACCUMULATED_VIEW_OPTIONS.find(opt => opt.value === view)?.monthsBack || 5
    startDate = new Date(Date.UTC(endDate.getUTCFullYear(), endDate.getUTCMonth() - monthsBack, 1))
  }
  
  const startISO = startDate.toISOString().split('T')[0] || ''
  const endIsoFinal = endDate.toISOString().split('T')[0] || ''
  
  return {
    start: startISO,
    end: endIsoFinal
  }
}

export function syncPeriodFromAccumulatedView(
  view: 'mensal' | 'semestral' | 'anual',
  currentPeriod: Period,
  referenceEndISO?: string
): Period {
  if (view === 'mensal') {
    const todayISO = new Date().toISOString().split('T')[0] || ''
    const ref = referenceEndISO || currentPeriod.end || todayISO
    const safeRef = ref || todayISO
    const refDate = new Date(safeRef + 'T00:00:00Z')
    const startDate = new Date(Date.UTC(refDate.getUTCFullYear(), refDate.getUTCMonth(), 1))
    const startISO = startDate.toISOString().split('T')[0] || ''
    
    return {
      start: startISO,
      end: safeRef
    }
  }
  
  return computeAccumulatedPeriod(view, referenceEndISO || currentPeriod.end)
}

export function useAccumulatedView(period: { value: Period }, updatePeriod: (period: Period) => void) {
  const accumulatedView = ref<'mensal' | 'semestral' | 'anual'>('mensal')
  
  const handleViewChange = (view: 'mensal' | 'semestral' | 'anual'): void => {
    accumulatedView.value = view
    const newPeriod = syncPeriodFromAccumulatedView(view, period.value)
    updatePeriod(newPeriod)
  }
  
  return {
    accumulatedView,
    handleViewChange,
    options: ACCUMULATED_VIEW_OPTIONS
  }
}
