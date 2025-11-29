/**
 * Serviço específico para a API de Estrutura
 * Responsável por buscar e processar dados da estrutura organizacional
 */

import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { EstruturaData } from '../types'

// Re-exporta tipos para compatibilidade
export type { EstruturaData } from '../types'

/**
 * Busca os dados da estrutura organizacional da API
 * @returns Promise com os dados da estrutura ou null em caso de erro
 */
export async function getEstrutura(): Promise<EstruturaData | null> {
  const response = await apiGet<EstruturaData>(ApiRoutes.ESTRUTURA)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar estrutura:', response.error)
  return null
}

