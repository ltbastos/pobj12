import { computed } from 'vue'
import type { ProdutoMensal } from '../types'
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
  pontosBackend?: number // Pontos vindos do backend
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

export function useProdutosLegacy() {
  const { filterState, period } = useGlobalFilters()
  const resumo = useResumoData(filterState, period)
  const produtos = resumo.produtosMensais
  const loading = resumo.loading
  const error = resumo.error
  const getCurrentMonthBusinessSnapshot = resumo.businessSnapshot

  const produtosPorFamilia = computed<LegacySection[]>(() => {
    const familiasMap = new Map<string, Map<string, LegacyItem>>()
    
    produtos.value.forEach((produto: ProdutoMensal) => {
      const familiaId = produto.id_familia || 'sem-familia'
      const indicadorId = produto.id_indicador || produto.id
      
      if (!indicadorId) {
        return
      }
      
      if (!familiasMap.has(familiaId)) {
        familiasMap.set(familiaId, new Map())
      }
      
      const indicadoresMap = familiasMap.get(familiaId)!
      
      if (indicadoresMap.has(indicadorId)) {
        const itemExistente = indicadoresMap.get(indicadorId)!
        
        if (!itemExistente.peso && produto.peso) {
          itemExistente.peso = Number(produto.peso) || 0
          itemExistente.pontosMeta = Number(produto.pontos_meta ?? produto.peso) || 0
        }
        
        itemExistente.meta = (itemExistente.meta || 0) + (produto.meta || 0)
        itemExistente.realizado = (itemExistente.realizado || 0) + (produto.realizado || 0)
        
        const mesesMap = new Map<string, { meta: number; realizado: number }>()
        
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
        
        itemExistente.months = Array.from(mesesMap.entries()).map(([mes, dados]) => ({
          mes,
          meta: dados.meta,
          realizado: dados.realizado,
          atingimento: dados.meta > 0 ? (dados.realizado / dados.meta) * 100 : 0
        }))
        
        const currentMonth = new Date().toISOString().slice(0, 7)
        const currentMonthData = itemExistente.months.find(m => m.mes === currentMonth)
        itemExistente.monthMeta = currentMonthData?.meta || 0
        itemExistente.monthReal = currentMonthData?.realizado || 0
        
        if (produto.id_subindicador && produto.subindicador) {
          if (!itemExistente.children) {
            itemExistente.children = []
          }
          
          const subItem: LegacyItem = {
            id: produto.id_subindicador,
            nome: produto.subindicador,
            metrica: produto.metrica,
            peso: Number(produto.peso) || 0,
            pontosMeta: Number(produto.pontos_meta ?? produto.peso) || 0,
            pontosBackend: produto.pontos ? Number(produto.pontos) : undefined,
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
        const currentMonth = new Date().toISOString().slice(0, 7)
        const currentMonthData = produto.meses.find(m => m.mes === currentMonth)
        
        const item: LegacyItem = {
          id: indicadorId,
          nome: produto.indicador || 'Indicador',
          metrica: produto.metrica || 'valor',
          peso: Number(produto.peso) || 0,
          pontosMeta: Number(produto.pontos_meta ?? produto.peso) || 0,
          pontosBackend: produto.pontos ? Number(produto.pontos) : undefined,
          meta: produto.meta || 0,
          realizado: produto.realizado || 0,
          monthMeta: currentMonthData?.meta || 0,
          monthReal: currentMonthData?.realizado || 0,
          ultimaAtualizacao: produto.ultima_atualizacao,
          months: produto.meses,
          children: []
        }
        
        if (produto.id_subindicador && produto.subindicador) {
          const subItem: LegacyItem = {
            id: produto.id_subindicador,
            nome: produto.subindicador,
            metrica: produto.metrica,
            peso: Number(produto.peso) || 0,
            pontosMeta: Number(produto.pontos_meta ?? produto.peso) || 0,
            pontosBackend: produto.pontos ? Number(produto.pontos) : undefined,
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
    
    const familiaNames = new Map<string, string>()
    produtos.value.forEach(p => {
      if (p.id_familia && !familiaNames.has(p.id_familia)) {
        familiaNames.set(p.id_familia, p.familia || 'Sem Família')
      }
    })
    
    const snapshot = getCurrentMonthBusinessSnapshot.value
    const diasTotais = snapshot.total || 1
    const diasDecorridos = snapshot.elapsed || 0
    const diasRestantes = snapshot.remaining || 0
    
    const enrichItem = (item: LegacyItem): LegacyItem => {
      const metaVal = item.meta || 0
      const realVal = item.realizado || 0
      const pesoVal = item.pontosMeta || item.peso || 0
      
      const referenciaHoje = diasTotais > 0 
        ? (metaVal / diasTotais) * diasDecorridos 
        : 0
      
      const faltaParaMeta = Math.max(0, metaVal - realVal)
      
      const metaDiariaNecessaria = diasRestantes > 0 
        ? faltaParaMeta / diasRestantes 
        : 0
      
      const projecao = diasDecorridos > 0 
        ? ((realVal / diasDecorridos) * diasRestantes) + realVal 
        : realVal
      
      let pontos: number
      let pontosBrutos: number
      if (item.pontosBackend !== undefined) {
        pontos = item.pontosBackend
        pontosBrutos = pontos
      } else {
        pontosBrutos = Math.min(pesoVal, realVal)
        pontos = Math.max(0, Math.min(pesoVal, pontosBrutos))
      }
      
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
      
      if (item.children && item.children.length > 0) {
        enriched.children = item.children.map(enrichItem)
      }
      
      return enriched
    }
    
    return Array.from(familiasMap.entries()).map(([familiaId, indicadoresMap]) => {
      const items = Array.from(indicadoresMap.values()).map(enrichItem)
      
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

