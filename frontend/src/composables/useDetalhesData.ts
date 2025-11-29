import { ref, computed, watch, type Ref, type ComputedRef } from 'vue'
import type { Period, DetalhesItem, DetalhesFilters } from '../types'
import type { FilterState } from './useGlobalFilters'
import { getDetalhes } from '../services/detalhesService'

const detalhesPayload = ref<DetalhesItem[] | null>(null)
const detalhesLoading = ref(false)
const detalhesError = ref<string | null>(null)
const lastFilters = ref<DetalhesFilters | null>(null)

let watcherRegistered = false

function sanitizeValue(value?: string | null): string | undefined {
  if (!value) return undefined
  const trimmed = value.trim()
  if (!trimmed) return undefined
  const lower = trimmed.toLowerCase()
  if (lower === 'todos' || lower === 'todas') return undefined
  return trimmed
}

function buildFiltersFromState(state?: FilterState, period?: Period): DetalhesFilters {
  const filters: DetalhesFilters = {}
  if (state) {
    const segmento = sanitizeValue(state.segmento)
    const diretoria = sanitizeValue(state.diretoria)
    const regional = sanitizeValue(state.gerencia)
    const agencia = sanitizeValue(state.agencia)
    const gerente = sanitizeValue(state.gerente)
    const gerenteGestao = sanitizeValue(state.ggestao)
    const familia = sanitizeValue(state.familia)
    const indicador = sanitizeValue(state.indicador)
    const subindicador = sanitizeValue(state.subindicador)

    if (segmento) filters.segmento = segmento
    if (diretoria) filters.diretoria = diretoria
    if (regional) filters.regional = regional
    if (agencia) filters.agencia = agencia
    if (gerente) filters.gerente = gerente
    if (gerenteGestao) filters.gerenteGestao = gerenteGestao
    if (familia) filters.familia = familia
    if (indicador) filters.indicador = indicador
    if (subindicador) filters.subindicador = subindicador
  }

  if (period?.start) {
    filters.dataInicio = period.start
  }
  if (period?.end) {
    filters.dataFim = period.end
  }

  return filters
}

// Função para comparar dois objetos de filtros
function filtersEqual(f1: DetalhesFilters, f2: DetalhesFilters): boolean {
  const keys1 = Object.keys(f1).sort()
  const keys2 = Object.keys(f2).sort()
  
  if (keys1.length !== keys2.length) {
    return false
  }
  
  for (const key of keys1) {
    if (f1[key as keyof DetalhesFilters] !== f2[key as keyof DetalhesFilters]) {
      return false
    }
  }
  
  return true
}

async function fetchDetalhes(filters: DetalhesFilters): Promise<void> {
  // Evita chamadas duplicadas com os mesmos filtros
  if (lastFilters.value && filtersEqual(lastFilters.value, filters)) {
    return
  }
  
  // Evita múltiplas chamadas simultâneas
  if (detalhesLoading.value) {
    return
  }
  
  lastFilters.value = filters
  detalhesLoading.value = true
  detalhesError.value = null

  try {
    const data = await getDetalhes(filters)
    if (data) {
      detalhesPayload.value = data
    } else {
      detalhesError.value = 'Não foi possível carregar os dados de detalhes'
      detalhesPayload.value = []
    }
  } catch (error) {
    console.error('Erro ao carregar detalhes:', error)
    detalhesError.value = error instanceof Error ? error.message : 'Erro desconhecido'
  } finally {
    detalhesLoading.value = false
  }
}

export function useDetalhesData(
  filterState: Ref<FilterState> | ComputedRef<FilterState>,
  period: Ref<Period> | ComputedRef<Period>
) {
  if (!watcherRegistered) {
    watcherRegistered = true
    
    // Usa um timeout para evitar múltiplas chamadas no carregamento inicial
    let timeoutId: ReturnType<typeof setTimeout> | null = null
    
    watch(
      [filterState, period],
      ([currentFilters, currentPeriod]) => {
        // Debounce para evitar múltiplas chamadas rápidas
        if (timeoutId) {
          clearTimeout(timeoutId)
        }
        
        timeoutId = setTimeout(() => {
          const filters = buildFiltersFromState(currentFilters, currentPeriod)
          fetchDetalhes(filters)
          timeoutId = null
        }, 100) // 100ms de debounce
      },
      { deep: true, immediate: true }
    )
  }

  const loadDetalhes = async () => {
    const filters = lastFilters.value ?? buildFiltersFromState(filterState.value, period.value)
    await fetchDetalhes(filters)
  }

  return {
    detalhes: computed(() => detalhesPayload.value ?? []),
    loading: computed(() => detalhesLoading.value),
    error: computed(() => detalhesError.value),
    loadDetalhes,
    buildFilters: () => buildFiltersFromState(filterState.value, period.value)
  }
}

