import { computed, type Ref } from 'vue'
import type { ProdutoMensal, ProdutoFilters } from '../types'
import { useGlobalFilters } from './useGlobalFilters'
import { useResumoData } from './useResumoData'

export interface LegacySection {
  id: string
  label: string
  items: LegacyItem[]
  totals: {
    pontosTotal: number
    pontosHit: number
    metaTotal: number
    realizadoTotal: number
    atingPct: number
  }
}

export interface LegacyItem {
  id: string
  nome: string
  metrica: string
  peso: number
  pontosMeta: number
  meta: number
  realizado: number
  monthMeta: number
  monthReal: number
  referenciaHoje?: number
  projecao?: number
  metaDiariaNecessaria?: number
  pontos?: number
  pontosBrutos?: number
  ating?: number
  ultimaAtualizacao?: string
  months: Array<{
    mes: string
    meta: number
    realizado: number
    atingimento: number
  }>
  children?: LegacyItem[]
}

export function useProdutosLegacy(_filters?: Ref<ProdutoFilters | null>) {
  const { filterState, period } = useGlobalFilters()
  const resumo = useResumoData(filterState, period)
  const produtos = resumo.produtosMensais
  const loading = resumo.loading
  const error = resumo.error
  const getCurrentMonthBusinessSnapshot = resumo.businessSnapshot

  // Agrupa produtos por família e indicador
  const produtosPorFamilia = computed<LegacySection[]>(() => {
    const familiasMap = new Map<string, Map<string, LegacyItem>>()
    
    produtos.value.forEach((produto: ProdutoMensal) => {
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
      
      // Se o indicador já existe, soma os valores dos subindicadores
      if (indicadoresMap.has(indicadorId)) {
        const itemExistente = indicadoresMap.get(indicadorId)!
        
        // Soma os valores dos subindicadores
        itemExistente.meta = (itemExistente.meta || 0) + (produto.meta || 0)
        itemExistente.realizado = (itemExistente.realizado || 0) + (produto.realizado || 0)
        
        // Combina meses (soma valores por mês)
        const mesesMap = new Map<string, { meta: number; realizado: number }>()
        
        // Adiciona meses do item existente
        itemExistente.months.forEach(m => {
          mesesMap.set(m.mes, { meta: m.meta, realizado: m.realizado })
        })
        
        // Adiciona meses do produto atual
        produto.meses.forEach(m => {
          const existing = mesesMap.get(m.mes) || { meta: 0, realizado: 0 }
          mesesMap.set(m.mes, {
            meta: existing.meta + m.meta,
            realizado: existing.realizado + m.realizado
          })
        })
        
        // Reconstrói array de meses
        itemExistente.months = Array.from(mesesMap.entries()).map(([mes, dados]) => ({
          mes,
          meta: dados.meta,
          realizado: dados.realizado,
          atingimento: dados.meta > 0 ? (dados.realizado / dados.meta) * 100 : 0
        }))
        
        // Atualiza monthMeta e monthReal (mês atual)
        const currentMonth = new Date().toISOString().slice(0, 7) // YYYY-MM
        const currentMonthData = itemExistente.months.find(m => m.mes === currentMonth)
        itemExistente.monthMeta = currentMonthData?.meta || 0
        itemExistente.monthReal = currentMonthData?.realizado || 0
        
        // Adiciona subindicador como filho se existir
        if (produto.id_subindicador && produto.subindicador) {
          if (!itemExistente.children) {
            itemExistente.children = []
          }
          
          const subItem: LegacyItem = {
            id: produto.id_subindicador,
            nome: produto.subindicador,
            metrica: produto.metrica,
            peso: produto.peso,
            pontosMeta: produto.peso,
            meta: produto.meta || 0,
            realizado: produto.realizado || 0,
            monthMeta: currentMonthData?.meta || 0,
            monthReal: currentMonthData?.realizado || 0,
            ultimaAtualizacao: produto.ultima_atualizacao,
            months: produto.meses
          }
          
          itemExistente.children.push(subItem)
        }
      } else {
        // Cria novo item para o indicador
        const currentMonth = new Date().toISOString().slice(0, 7)
        const currentMonthData = produto.meses.find(m => m.mes === currentMonth)
        
        const item: LegacyItem = {
          id: indicadorId,
          nome: produto.indicador || 'Indicador',
          metrica: produto.metrica || 'valor',
          peso: produto.peso || 0,
          pontosMeta: produto.peso || 0,
          meta: produto.meta || 0,
          realizado: produto.realizado || 0,
          monthMeta: currentMonthData?.meta || 0,
          monthReal: currentMonthData?.realizado || 0,
          ultimaAtualizacao: produto.ultima_atualizacao,
          months: produto.meses,
          children: []
        }
        
        // Adiciona subindicador como filho se existir
        if (produto.id_subindicador && produto.subindicador) {
          const subItem: LegacyItem = {
            id: produto.id_subindicador,
            nome: produto.subindicador,
            metrica: produto.metrica,
            peso: produto.peso,
            pontosMeta: produto.peso,
            meta: produto.meta || 0,
            realizado: produto.realizado || 0,
            monthMeta: currentMonthData?.meta || 0,
            monthReal: currentMonthData?.realizado || 0,
            ultimaAtualizacao: produto.ultima_atualizacao,
            months: produto.meses
          }
          
          item.children!.push(subItem)
        }
        
        indicadoresMap.set(indicadorId, item)
      }
    })
    
    // Cria um mapa de nomes de família
    const familiaNames = new Map<string, string>()
    produtos.value.forEach(p => {
      if (p.id_familia && !familiaNames.has(p.id_familia)) {
        familiaNames.set(p.id_familia, p.familia || 'Sem Família')
      }
    })
    
    // Calcula valores adicionais para cada item (referência, projeção, etc.)
    const snapshot = getCurrentMonthBusinessSnapshot.value
    const diasTotais = snapshot.total || 1
    const diasDecorridos = snapshot.elapsed || 0
    const diasRestantes = snapshot.remaining || 0
    
    const enrichItem = (item: LegacyItem): LegacyItem => {
      const metaVal = item.meta || 0
      const realVal = item.realizado || 0
      const pesoVal = item.pontosMeta || item.peso || 0
      
      // Referência para hoje: (meta / dias úteis no mês) * dias úteis trabalhados
      const referenciaHoje = diasTotais > 0 
        ? (metaVal / diasTotais) * diasDecorridos 
        : 0
      
      // Falta para a meta
      const faltaParaMeta = Math.max(0, metaVal - realVal)
      
      // Meta diária necessária: (Falta para a meta) / dias úteis que faltam
      const metaDiariaNecessaria = diasRestantes > 0 
        ? faltaParaMeta / diasRestantes 
        : 0
      
      // Projeção (forecast): (Realizado / dias úteis trabalhados) * dias que faltam + o realizado
      const projecao = diasDecorridos > 0 
        ? ((realVal / diasDecorridos) * diasRestantes) + realVal 
        : realVal
      
      // Calcula pontos (limitado pelo peso)
      const pontosBrutos = Math.min(pesoVal, realVal)
      const pontos = Math.max(0, Math.min(pesoVal, pontosBrutos))
      
      // Calcula atingimento (percentual)
      const ating = metaVal > 0 ? (realVal / metaVal) : 0
      
      const enriched: LegacyItem = {
        ...item,
        referenciaHoje,
        projecao,
        metaDiariaNecessaria,
        pontos,
        pontosBrutos,
        ating
      }
      
      // Enriquece também os filhos
      if (item.children && item.children.length > 0) {
        enriched.children = item.children.map(enrichItem)
      }
      
      return enriched
    }
    
    // Converte o mapa em array de seções
    return Array.from(familiasMap.entries()).map(([familiaId, indicadoresMap]) => {
      const items = Array.from(indicadoresMap.values()).map(enrichItem)
      
      // Calcula totais da seção
      const pontosTotal = items.reduce((acc, item) => acc + (item.pontosMeta || 0), 0)
      const pontosHit = items.reduce((acc, item) => acc + (item.pontos || 0), 0)
      const metaTotal = items.reduce((acc, item) => acc + (item.meta || 0), 0)
      const realizadoTotal = items.reduce((acc, item) => acc + (item.realizado || 0), 0)
      const atingPct = metaTotal > 0 ? (realizadoTotal / metaTotal) * 100 : 0
      
      return {
        id: familiaId,
        label: familiaNames.get(familiaId) || 'Sem Família',
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
    produtos,
    produtosPorFamilia,
    loading,
    error,
    loadProdutos: resumo.loadResumo
  }
}

