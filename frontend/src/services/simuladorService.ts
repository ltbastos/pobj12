import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'

export interface SimuladorProduct {
  id: string
  label: string
  sectionId: string
  sectionLabel: string
  metric: 'qtd' | 'valor'
  meta: number
  realizado: number
  variavelMeta: number
  variavelReal: number
  pontosMeta: number
  pontosBrutos: number
  pontos: number
  ultimaAtualizacao: string | null
}

export interface SimuladorFilters {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  dataInicio?: string
  dataFim?: string
}

export async function getSimuladorProducts(filters?: SimuladorFilters): Promise<SimuladorProduct[] | null> {
  const params: Record<string, string> = {}
  
  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerenteGestao) params.gerenteGestao = filters.gerenteGestao
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
  }
  
  const response = await apiGet<SimuladorProduct[]>(ApiRoutes.SIMULADOR, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar produtos para simulador:', response.error)
  return null
}

