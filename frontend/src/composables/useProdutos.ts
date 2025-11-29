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

  // Observa mudanças nos filtros e recarrega produtos
  if (filters) {
    watch(filters, (newFilters) => {
      if (newFilters) {
        loadProdutos(newFilters)
      } else {
        loadProdutos()
      }
    }, { immediate: true, deep: true })
  } else {
    // Se não houver filtros, carrega uma vez ao montar
    onMounted(() => {
      loadProdutos()
    })
  }

  // Agrupa produtos por família e indicador (subindicadores são somados no indicador)
  const produtosPorFamilia = computed(() => {
    // Primeiro, agrupa por família e depois por indicador
    const familiasMap = new Map<string, Map<string, ProdutoCard>>()
    
    produtos.value.forEach(produto => {
      const familiaId = produto.id_familia || 'sem-familia'
      const familiaNome = produto.familia || 'Sem Família'
      const indicadorId = produto.id_indicador || produto.id
      
      // Ignora produtos que são apenas subindicadores (sem indicador pai)
      if (!indicadorId) {
        return
      }
      
      // Cria o mapa de família se não existir
      if (!familiasMap.has(familiaId)) {
        familiasMap.set(familiaId, new Map())
      }
      
      const indicadoresMap = familiasMap.get(familiaId)!
      
      // Se o indicador já existe, soma os valores dos subindicadores
      if (indicadoresMap.has(indicadorId)) {
        const cardExistente = indicadoresMap.get(indicadorId)!
        
        // Soma os valores dos subindicadores
        cardExistente.meta = (cardExistente.meta || 0) + (produto.meta || 0)
        cardExistente.realizado = (cardExistente.realizado || 0) + (produto.realizado || 0)
        cardExistente.pontos = (cardExistente.pontos || 0) + (produto.pontos || 0)
        // Para pontosMeta, soma os valores dos subindicadores (cada subindicador tem seu peso)
        cardExistente.pontosMeta = (cardExistente.pontosMeta || 0) + (produto.pontos_meta || produto.peso || 0)
        
        // Atualiza a última atualização se for mais recente
        if (produto.ultima_atualizacao && (
          !cardExistente.ultimaAtualizacao || 
          produto.ultima_atualizacao > cardExistente.ultimaAtualizacao
        )) {
          cardExistente.ultimaAtualizacao = produto.ultima_atualizacao
        }
        
        // Recalcula o atingimento após somar os valores
        const metaVal = cardExistente.meta || 0
        const realizadoVal = cardExistente.realizado || 0
        cardExistente.ating = metaVal > 0 ? (realizadoVal / metaVal) : 0
        cardExistente.atingido = metaVal > 0 ? (realizadoVal / metaVal) >= 1 : false
      } else {
        // Cria novo card para o indicador
        const metaVal = produto.meta || 0
        const realizadoVal = produto.realizado || 0
        const card: ProdutoCard = {
          id: indicadorId,
          nome: produto.indicador || 'Indicador',
          familiaId,
          familiaNome,
          indicadorId,
          indicadorNome: produto.indicador || 'Indicador',
          // Não armazena subindicador no card principal, apenas os valores somados
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
    
    // Converte o mapa em array de seções
    return Array.from(familiasMap.entries()).map(([familiaId, indicadoresMap]) => {
      const items = Array.from(indicadoresMap.values())
      
      // Calcula totais da seção
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

