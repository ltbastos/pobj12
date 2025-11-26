import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'

export interface Produto {
  id: string
  id_familia: string
  familia: string
  id_indicador: string
  indicador: string
  id_subindicador?: string
  subindicador?: string
  metrica: string
  peso: number
  meta?: number
  realizado?: number
  pontos?: number
  pontos_meta?: number
  variavel_meta?: number
  variavel_realizado?: number
  ating?: number
  atingido?: boolean
  ultima_atualizacao?: string
}

export interface ProdutoFilters {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  familia?: string
  indicador?: string
  subindicador?: string
  dataInicio?: string
  dataFim?: string
  status?: string
}

export interface ProdutoMensal {
  id: string
  id_indicador: string
  indicador: string
  id_familia: string
  familia: string
  id_subindicador?: string
  subindicador?: string
  metrica: string
  peso: number
  meta?: number
  realizado?: number
  ating?: number
  atingido?: boolean
  ultima_atualizacao?: string
  meses: Array<{
    mes: string
    meta: number
    realizado: number
    atingimento: number
  }>
}

export async function getProdutos(filters?: ProdutoFilters): Promise<Produto[] | null> {
  const params: Record<string, string> = {}
  
  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerenteGestao) params.gerenteGestao = filters.gerenteGestao
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.familia) params.familia = filters.familia
    if (filters.indicador) params.indicador = filters.indicador
    if (filters.subindicador) params.subindicador = filters.subindicador
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
    if (filters.status) params.status = filters.status
  }
  
  const response = await apiGet<Produto[]>(ApiRoutes.PRODUTOS, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar produtos:', response.error)
  return null
}

export async function getProdutosMensais(filters?: ProdutoFilters): Promise<ProdutoMensal[] | null> {
  const params: Record<string, string> = {}
  
  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerenteGestao) params.gerenteGestao = filters.gerenteGestao
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.familia) params.familia = filters.familia
    if (filters.indicador) params.indicador = filters.indicador
    if (filters.subindicador) params.subindicador = filters.subindicador
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
    if (filters.status) params.status = filters.status
  }
  
  const response = await apiGet<ProdutoMensal[]>('/api/produtos/mensais', params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar produtos mensais:', response.error)
  return null
}

