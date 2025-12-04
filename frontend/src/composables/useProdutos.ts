import { ref, computed, watch, onMounted, type Ref } from 'vue'
import { getProdutos, type Produto, type ProdutoFilters } from '../services/produtosService'

export interface ProdutoCard {
  id: string
  nome: string
  familiaId: string
  familiaNome: string
  indicadorId: string
  indicadorNome: string
  subindicadorId?: string
  subindicadorNome?: string
  metrica: string
  peso: number
  icon?: string
  meta?: number
  realizado?: number
  pontos?: number
  pontosMeta?: number
  atingido?: boolean
  ating?: number
  ultimaAtualizacao?: string
}

export function useProdutos(filters?: Ref<ProdutoFilters | null>) {
  const produtos = ref<Produto[]>([])
  const loading = ref(true)
  const error = ref<string | null>(null)

  const loadProdutos = async (currentFilters?: ProdutoFilters | null) => {
    try {
      loading.value = true
      error.value = null
      const data = await getProdutos(currentFilters || undefined)
      if (data) {
        produtos.value = data
      } else {
        error.value = 'Não foi possível carregar os produtos'
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro desconhecido'
      console.error('Erro ao carregar produtos:', err)
    } finally {
      loading.value = false
    }
  }

  if (filters) {
    watch(filters, (newFilters) => {
      if (newFilters) {
        loadProdutos(newFilters)
      } else {
        loadProdutos()
      }
    }, { immediate: true, deep: true })
  } else {
    onMounted(() => {
      loadProdutos()
    })
  }

  const produtosPorFamilia = computed(() => {
    const familiasMap = new Map<string, Map<string, ProdutoCard>>()
    
    produtos.value.forEach(produto => {
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
        const cardExistente = indicadoresMap.get(indicadorId)!
        
        cardExistente.meta = (cardExistente.meta || 0) + (produto.meta || 0)
        cardExistente.realizado = (cardExistente.realizado || 0) + (produto.realizado || 0)
        cardExistente.pontos = (cardExistente.pontos || 0) + (produto.pontos || 0)
        cardExistente.pontosMeta = (cardExistente.pontosMeta || 0) + (produto.pontos_meta || produto.peso || 0)
        
        if (produto.ultima_atualizacao && (
          !cardExistente.ultimaAtualizacao || 
          produto.ultima_atualizacao > cardExistente.ultimaAtualizacao
        )) {
          cardExistente.ultimaAtualizacao = produto.ultima_atualizacao
        }
        
        const metaVal = cardExistente.meta || 0
        const realizadoVal = cardExistente.realizado || 0
        cardExistente.ating = metaVal > 0 ? (realizadoVal / metaVal) : 0
        cardExistente.atingido = metaVal > 0 ? (realizadoVal / metaVal) >= 1 : false
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
    produtos,
    produtosPorFamilia,
    loading,
    error,
    loadProdutos
  }
}
