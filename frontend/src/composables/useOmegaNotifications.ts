import type { OmegaTicket, OmegaHistoryEntry } from '../types/omega'
import { apiPost } from '../services/api'
import type { ApiResponse } from '../types'

export async function createOmegaNotification(
  ticket: OmegaTicket,
  entry: OmegaHistoryEntry
): Promise<ApiResponse<any>> {
  try {
    const response = await apiPost('/api/omega/notifications', {
      ticketId: ticket.id,
      ticketSubject: ticket.subject,
      date: entry.date || new Date().toISOString(),
      actorId: entry.actorId || '',
      action: entry.action || '',
      comment: entry.comment || '',
      status: entry.status || ticket.status,
      type: 'status_update'
    })
    return response
  } catch (error) {
    console.error('Erro ao criar notificação no Omega:', error)
    return { success: false, error: 'Erro ao criar notificação' }
  }
}

export async function createPobjNotification(
  ticket: OmegaTicket,
  entry: OmegaHistoryEntry
): Promise<ApiResponse<any>> {
  if (ticket.queue?.toLowerCase() !== 'pobj') {
    return { success: true, data: null }
  }

  try {
    const response = await apiPost('/api/pobj/notifications', {
      ticketId: ticket.id,
      ticketSubject: ticket.subject,
      date: entry.date || new Date().toISOString(),
      actorId: entry.actorId || '',
      action: entry.action || '',
      comment: entry.comment || '',
      status: entry.status || ticket.status
    })
    return response
  } catch (error) {
    console.error('Erro ao criar notificação no POBJ:', error)
    return { success: false, error: 'Erro ao criar notificação' }
  }
}
