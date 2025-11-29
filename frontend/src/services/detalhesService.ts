import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { DetalhesFilters, DetalhesItem } from '../types'

export type { DetalhesFilters, DetalhesItem } from '../types'

export async function getDetalhes(filters?: DetalhesFilters): Promise<DetalhesItem[] | null> {
  const params: Record<string, string> = {}

  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.familia) params.familia = filters.familia
    if (filters.indicador) params.indicador = filters.indicador
    if (filters.subindicador) params.subindicador = filters.subindicador
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
  }

  const response = await apiGet<DetalhesItem[]>(ApiRoutes.DETALHES, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar detalhes:', response.error)
  return null
}

