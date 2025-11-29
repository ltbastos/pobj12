import { computed, type Ref, type ComputedRef } from 'vue'
import type { Period, ProdutoFilters } from '../types'
import { useResumoData } from './useResumoData'
import type { FilterState } from './useGlobalFilters'
import type { ProdutoCard } from './useProdutos'

/**
 * Composable que integra filtros hierárquicos, período e produtos
 */
export function useFilteredProdutos(
  filterState: Ref<FilterState> | ComputedRef<FilterState>,
  period: Ref<Period> | ComputedRef<Period>
) {
  const resumo = useResumoData(filterState, period)

  const produtoFilters = computed<ProdutoFilters>(() => resumo.buildFilters())

  const produtosPorFamilia = computed(() => {
    const familiasMap = new Map<string, Map<string, ProdutoCard>>()

    resumo.produtos.value.forEach(produto => {
      const familiaId = produto.id_familia || 'sem-familia'
      const familiaNome = produto.familia || 'Sem Família'
      const indicadorId = produto.id_indicador || produto.id

      if (!indicadorId) {
        return
      }

      if (!familiasMap.has(familiaId)) {
        familiasMap.set(familiaId, new Map())
      }

      const indicadoresMap = familiasMap.get(familiaId)!

      if (indicadoresMap.has(indicadorId)) {
        const card = indicadoresMap.get(indicadorId)!
        card.meta = (card.meta || 0) + (produto.meta || 0)
        card.realizado = (card.realizado || 0) + (produto.realizado || 0)
        card.pontos = (card.pontos || 0) + (produto.pontos || 0)
        card.pontosMeta = (card.pontosMeta || 0) + (produto.pontos_meta || produto.peso || 0)

        if (produto.ultima_atualizacao) {
          if (!card.ultimaAtualizacao || produto.ultima_atualizacao > card.ultimaAtualizacao) {
            card.ultimaAtualizacao = produto.ultima_atualizacao
          }
        }

        const metaVal = card.meta || 0
        const realizadoVal = card.realizado || 0
        card.ating = metaVal > 0 ? (realizadoVal / metaVal) : 0
        card.atingido = metaVal > 0 ? (realizadoVal / metaVal) >= 1 : false
      } else {
        const metaVal = produto.meta || 0
        const realizadoVal = produto.realizado || 0
        const card: ProdutoCard = {
          id: indicadorId,
          nome: produto.indicador || 'Indicador',
          familiaId,
          familiaNome,
          indicadorId,
          indicadorNome: produto.indicador || 'Indicador',
          metrica: produto.metrica || 'valor',
          peso: produto.peso || 0,
          pontosMeta: produto.pontos_meta || produto.peso || 0,
          meta: metaVal,
          realizado: realizadoVal,
          pontos: produto.pontos || 0,
          atingido: metaVal > 0 ? (realizadoVal / metaVal) >= 1 : false,
          ating: metaVal > 0 ? (realizadoVal / metaVal) : 0,
          ultimaAtualizacao: produto.ultima_atualizacao
        }

        indicadoresMap.set(indicadorId, card)
      }
    })

    return Array.from(familiasMap.entries()).map(([familiaId, indicadoresMap]) => {
      const items = Array.from(indicadoresMap.values())
      const pontosTotal = items.reduce((acc, item) => acc + (item.pontosMeta || 0), 0)
      const pontosHit = items.reduce((acc, item) => acc + Math.max(0, item.pontos || 0), 0)
      const metaTotal = items.reduce((acc, item) => acc + (item.meta || 0), 0)
      const realizadoTotal = items.reduce((acc, item) => acc + (item.realizado || 0), 0)
      const atingPct = metaTotal > 0 ? (realizadoTotal / metaTotal) * 100 : 0

      return {
        id: familiaId,
        label: items[0]?.familiaNome || 'Sem Família',
        items,
        totals: {
          pontosTotal,
          pontosHit,
          metaTotal,
          realizadoTotal,
          atingPct
        }
      }
    })
  })

  return {
    produtos: resumo.produtos,
    produtosPorFamilia,
    loading: resumo.loading,
    error: resumo.error,
    loadProdutos: resumo.loadResumo,
    produtoFilters
  }
}

