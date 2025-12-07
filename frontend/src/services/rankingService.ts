import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { RankingFilters, RankingItem } from '../types'

export type { RankingFilters, RankingItem } from '../types'

export async function getRanking(filters?: RankingFilters, nivel?: string, userFuncional?: string): Promise<RankingItem[] | null> {
  const params: Record<string, string> = {}
  
  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerenteGestao) params.gerenteGestao = filters.gerenteGestao
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.grupo) params.grupo = filters.grupo
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
  }
  
  if (nivel) {
    params.nivel = nivel
  }

  // Se userFuncional for fornecido, armazena temporariamente para o header
  if (userFuncional) {
    localStorage.setItem('userFuncional', userFuncional)
  }
  
  const response = await apiGet<RankingItem[]>(ApiRoutes.RANKING, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar ranking:', response.error)
  return null
}
