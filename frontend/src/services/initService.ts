import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { InitData } from '../types'

// Re-exporta tipos para compatibilidade
export type { InitData } from '../types'

/**
 * Busca os dados da inicialização da API
 * @returns Promise com os dados da estrutura ou null em caso de erro
 */
export async function getInit(): Promise<InitData | null> {
  const response = await apiGet<InitData>(ApiRoutes.INIT)
  return response.data ?? null
}
