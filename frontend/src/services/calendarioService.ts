/**
 * Serviço específico para a API de Calendário
 * Responsável por buscar dados de calendário para seleção de período
 */

import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'
import type { CalendarioItem } from '../types'

// Re-exporta tipos para compatibilidade
export type { CalendarioItem } from '../types'

/**
 * Busca os dados do calendário da API
 * @returns Promise com os dados do calendário ou null em caso de erro
 */
export async function getCalendario(): Promise<CalendarioItem[] | null> {
  const response = await apiGet<CalendarioItem[]>(ApiRoutes.CALENDARIO, {
    _t: Date.now()
  })

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar calendário:', response.error)
  return null
}

/**
 * Obtém o período padrão (último mês)
 */
export function getDefaultPeriod(): { start: string; end: string } {
  const today = new Date()
  const end = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  const start = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())

  const startISO = start.toISOString().split('T')[0] || ''
  const endISO = end.toISOString().split('T')[0] || ''

  return {
    start: startISO,
    end: endISO
  }
}

/**
 * Formata data para formato brasileiro (DD/MM/YYYY)
 */
export function formatBRDate(dateString: string): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
}

