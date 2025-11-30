import { ref, computed, watch, type Ref } from 'vue'
import { getVariavel, type Variavel, type VariavelFilters } from '../services/variavelService'

export type { VariavelFilters } from '../services/variavelService'

export interface VariavelSummary {
  varPossivel: number | null
  varAtingido: number | null
  hasData: boolean
}

export function useVariavel(filters?: Ref<VariavelFilters | null>) {
  const variavel = ref<Variavel[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const loadVariavel = async (currentFilters?: VariavelFilters | null) => {
    try {
      loading.value = true
      error.value = null
      const data = await getVariavel(currentFilters || undefined)
      if (data) {
        variavel.value = data
      } else {
        variavel.value = []
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro desconhecido'
      console.error('Erro ao carregar variável:', err)
      variavel.value = []
    } finally {
      loading.value = false
    }
  }

  if (filters) {
    watch(filters, (newFilters) => {
      if (newFilters) {
        loadVariavel(newFilters)
      } else {
        loadVariavel()
      }
    }, { immediate: true, deep: true })
  }

  const summary = computed<VariavelSummary>(() => {
    if (loading.value) {
      return {
        varPossivel: null,
        varAtingido: null,
        hasData: false
      }
    }

    if (variavel.value.length === 0) {
      return {
        varPossivel: null,
        varAtingido: null,
        hasData: false
      }
    }

    let totalMeta = 0
    let totalRealizado = 0

    variavel.value.forEach(v => {
      if (!v) return
      const meta = Number(v.variavel_meta) || 0
      const real = Number(v.variavel_real) || 0
      totalMeta += meta
      totalRealizado += real
    })

    return {
      varPossivel: totalMeta,
      varAtingido: totalRealizado,
      hasData: true
    }
  })

  return {
    variavel,
    summary,
    loading,
    error,
    loadVariavel
  }
}

