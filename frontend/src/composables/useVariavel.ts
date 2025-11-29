import { ref, computed, watch, type Ref } from 'vue'
import { getVariavel, type Variavel, type VariavelFilters } from '../services/variavelService'

// Re-exporta VariavelFilters para uso em outros composables
export type { VariavelFilters } from '../services/variavelService'

export interface VariavelSummary {
  varPossivel: number | null
  varAtingido: number | null
  hasData: boolean
}

/**
 * Composable para calcular totais de variável agregados
 */
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
        console.log('Variável carregada:', data.length, 'registros')
      } else {
        variavel.value = []
        // Não define erro se não houver dados, apenas array vazio
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro desconhecido'
      console.error('Erro ao carregar variável:', err)
      variavel.value = []
    } finally {
      loading.value = false
    }
  }

  // Observa mudanças nos filtros e recarrega variável
  if (filters) {
    watch(filters, (newFilters) => {
      if (newFilters) {
        loadVariavel(newFilters)
      } else {
        loadVariavel()
      }
    }, { immediate: true, deep: true })
  }

  // Calcula totais agregados de variável
  const summary = computed<VariavelSummary>(() => {
    // Se ainda está carregando, retorna null
    if (loading.value) {
      return {
        varPossivel: null,
        varAtingido: null,
        hasData: false
      }
    }

    // Se não há dados e já terminou de carregar, retorna null (não mostra o card)
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
      // O backend retorna variavel_meta e variavel_real no DTO
      const meta = Number(v.variavel_meta) || 0
      const real = Number(v.variavel_real) || 0
      totalMeta += meta
      totalRealizado += real
    })

    console.log('Summary variável:', { totalMeta, totalRealizado, registros: variavel.value.length })

    // Retorna os valores agregados
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

