import { computed, type Ref, type ComputedRef } from 'vue'
import { useFilteredProdutos } from './useFilteredProdutos'
import { useResumoData } from './useResumoData'
import type { Period } from '../types'
import type { FilterState } from './useGlobalFilters'

export function useResumoSummary(
  filterState: Ref<FilterState> | ComputedRef<FilterState>,
  period: Ref<Period> | ComputedRef<Period>
) {
  const { produtosPorFamilia } = useFilteredProdutos(filterState, period)
  const resumo = useResumoData(filterState, period)

  const variavelSummary = computed(() => {
    const data = resumo.variavel.value || []
    if (!data.length) {
      return {
        varPossivel: null,
        varAtingido: null
      }
    }

    const totals = data.reduce(
      (acc, item) => {
        const meta = Number(item.variavel_meta) || 0
        const real = Number(item.variavel_real) || 0
        acc.meta += meta
        acc.real += real
        return acc
      },
      { meta: 0, real: 0 }
    )

    return {
      varPossivel: totals.meta,
      varAtingido: totals.real
    }
  })

  const summary = computed(() => {
    const familias = produtosPorFamilia.value || []

    let indicadoresAtingidos = 0
    let indicadoresTotal = 0
    let pontosAtingidos = 0
    let pontosTotal = 0

    familias.forEach(familia => {
      familia.items.forEach(item => {
        indicadoresTotal++
        if (item.atingido) {
          indicadoresAtingidos++
        }

        const pontosMeta = item.pontosMeta || 0
        const pontosReal = Math.max(0, item.pontos || 0)

        pontosTotal += pontosMeta
        pontosAtingidos += Math.min(pontosReal, pontosMeta)
      })
    })

    return {
      indicadoresAtingidos,
      indicadoresTotal,
      pontosAtingidos,
      pontosPossiveis: pontosTotal,
      varPossivel: variavelSummary.value.varPossivel,
      varAtingido: variavelSummary.value.varAtingido
    }
  })

  return {
    summary
  }
}

