import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { getDefaultPeriod, formatBRDate } from '../services/calendarioService'
import { useCalendarioCache } from './useCalendarioCache'
import type { Period } from '../types'

export function usePeriodManager() {
  const route = useRoute()
  const { loadCalendario } = useCalendarioCache()

  const period = ref<Period>(getDefaultPeriod())
  const isSimuladoresPage = computed(() => route.name === 'Simuladores')

  const startFormatted = computed(() => formatBRDate(period.value.start))
  const endFormatted = computed(() => formatBRDate(period.value.end))

  const updatePeriod = (p: Period) => {
    period.value = p
  }

  onMounted(() => loadCalendario())

  // Re-renderiza o header do período ao trocar de rota
  watch(() => route.name, () => {})

  return {
    period,
    isSimuladoresPage,
    startFormatted,
    endFormatted,
    updatePeriod,
  }
}
