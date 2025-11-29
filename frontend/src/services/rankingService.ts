import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { RankingFilters, RankingItem } from '../types'

export type { RankingFilters, RankingItem } from '../types'

export async function getRanking(filters?: RankingFilters): Promise<RankingItem[] | null> {
  const params: Record<string, string> = {}
  
  if (filters && filters.gerenteGestao) {
    params.gerenteGestao = filters.gerenteGestao
  }
  
  const response = await apiGet<RankingItem[]>(ApiRoutes.RANKING, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar ranking:', response.error)
  return null
}

