import { apiGet, apiPost } from './api'
import type { ApiResponse } from '../types'
import type {
  OmegaInitData,
  OmegaUser,
  OmegaTicket,
  OmegaStatus,
  OmegaStructure,
  OmegaMesuRecord,
  OmegaHistoryEntry,
  OmegaTicketContext,
  OmegaRole
} from '../types/omega'

// Tipos para dados brutos da API
interface ApiUser {
  id: string
  nome: string
  funcional: string
  cargo: string
  usuario: boolean
  analista: boolean
  supervisor: boolean
  admin: boolean
  encarteiramento?: boolean
  meta?: boolean
  orcamento?: boolean
  pobj?: boolean
  matriz?: boolean
  outros?: boolean
}

interface ApiTicket {
  id: string
  subject: string
  company: string
  product_id: string
  product_label: string
  family: string
  section: string
  queue: string
  category: string
  status: string
  priority: string
  opened: string
  updated: string
  due_date: string | null
  requester_id: string | null
  owner_id: string | null
  team_id: string | null
  history: string
  diretoria?: string | null
  gerencia?: string | null
  agencia?: string | null
  gerente_gestao?: string | null
  gerente?: string | null
  credit?: string | null
  attachment?: string | null
}

interface ApiStatus {
  id: string
  label: string
  tone: string
  descricao?: string
  ordem: number
  departamento_id: string
}

interface ApiInitData {
  structure?: OmegaStructure[]
  statuses?: ApiStatus[]
  users?: ApiUser[]
  tickets?: ApiTicket[]
  mesu?: OmegaMesuRecord[]
}

function mapApiUserToOmegaUser(apiUser: ApiUser): OmegaUser {
  // Determina o role principal (prioridade: admin > supervisor > analista > usuario)
  let role: OmegaRole = 'usuario'
  if (apiUser.admin) role = 'admin'
  else if (apiUser.supervisor) role = 'supervisor'
  else if (apiUser.analista) role = 'analista'

  // Constrói objeto de roles
  const roles: Record<OmegaRole, boolean> = {
    usuario: apiUser.usuario || false,
    analista: apiUser.analista || false,
    supervisor: apiUser.supervisor || false,
    admin: apiUser.admin || false
  }

  // Constrói array de filas baseado nos booleanos
  const queues: string[] = []
  if (apiUser.encarteiramento) queues.push('encarteiramento')
  if (apiUser.meta) queues.push('Metas')
  if (apiUser.orcamento) queues.push('Orçamento')
  if (apiUser.pobj) queues.push('pobj')
  if (apiUser.matriz) queues.push('Matriz')
  if (apiUser.outros) queues.push('Outros')

  return {
    id: apiUser.id,
    name: apiUser.nome,
    role,
    roles,
    matrixAccess: false, // Não vem da API, usar padrão
    avatar: null,
    position: apiUser.cargo,
    junction: '', // Não vem da API
    functional: apiUser.funcional,
    queues,
    defaultQueue: queues.length > 0 ? queues[0] || null : null,
    teamId: null // Não vem da API diretamente
  }
}

function parseHistory(historyString: string): OmegaHistoryEntry[] {
  if (!historyString || typeof historyString !== 'string') {
    return []
  }

  const entries: OmegaHistoryEntry[] = []
  const parts = historyString.split('||')

  for (const part of parts) {
    const segments = part.split('::')
    if (segments.length >= 5) {
      try {
        entries.push({
          date: segments[0] || '',
          actorId: segments[1] || '',
          action: segments[2] || '',
          comment: segments[3] || '',
          status: segments[4] || '',
          attachments: segments[5] ? (typeof segments[5] === 'string' ? JSON.parse(segments[5]) : segments[5]) : undefined
        })
      } catch (err) {
        // Tenta criar entrada mesmo com erro
        entries.push({
          date: segments[0] || '',
          actorId: segments[1] || '',
          action: segments[2] || '',
          comment: segments[3] || '',
          status: segments[4] || ''
        })
      }
    }
  }

  return entries
}

function mapApiTicketToOmegaTicket(apiTicket: ApiTicket, users: OmegaUser[]): OmegaTicket {
  // Encontra o nome do solicitante
  const requester = users.find(u => u.id === apiTicket.requester_id)
  const requesterName = requester?.name || 'Desconhecido'

  // Mapeia contexto
  const context: OmegaTicketContext = {
    diretoria: apiTicket.diretoria || '',
    gerencia: apiTicket.gerencia || '',
    agencia: apiTicket.agencia || '',
    ggestao: apiTicket.gerente_gestao || '',
    gerente: apiTicket.gerente || '',
    familia: apiTicket.family || '',
    secao: apiTicket.section || '',
    prodsub: apiTicket.product_id || ''
  }

  // Processa anexos
  const attachments: string[] = []
  if (apiTicket.attachment) {
    attachments.push(apiTicket.attachment)
  }

  return {
    id: apiTicket.id,
    subject: apiTicket.subject,
    company: apiTicket.company,
    requesterName,
    productId: apiTicket.product_id,
    product: apiTicket.product_label,
    family: apiTicket.family,
    section: apiTicket.section,
    queue: apiTicket.queue,
    status: apiTicket.status,
    category: apiTicket.category,
    priority: apiTicket.priority as any,
    opened: apiTicket.opened,
    updated: apiTicket.updated,
    dueDate: apiTicket.due_date,
    requesterId: apiTicket.requester_id,
    ownerId: apiTicket.owner_id,
    teamId: apiTicket.team_id,
    context,
    history: parseHistory(apiTicket.history),
    credit: apiTicket.credit || undefined,
    attachments,
    flow: null
  }
}

function mapApiStatusToOmegaStatus(apiStatus: ApiStatus): OmegaStatus {
  return {
    id: apiStatus.id,
    label: apiStatus.label,
    tone: apiStatus.tone as any,
    description: apiStatus.descricao,
    order: apiStatus.ordem,
    departmentId: apiStatus.departamento_id
  }
}

export async function getOmegaInit(): Promise<OmegaInitData | null> {
  const response = await apiGet<ApiInitData>('/api/omega/init')
  if (response.success && response.data) {
    const apiData = response.data
    
    // Mapeia usuários
    const users: OmegaUser[] = apiData.users
      ? apiData.users.map(mapApiUserToOmegaUser)
      : []

    // Mapeia status
    const statuses: OmegaStatus[] = apiData.statuses
      ? apiData.statuses.map(mapApiStatusToOmegaStatus)
      : []

    // Mapeia tickets (precisa dos usuários para buscar nomes)
    const tickets: OmegaTicket[] = apiData.tickets
      ? apiData.tickets.map(t => mapApiTicketToOmegaTicket(t, users))
      : []

    return {
      structure: apiData.structure || [],
      statuses,
      users,
      tickets,
      mesu: apiData.mesu || []
    }
  }
  return null
}

export async function getOmegaUsers(): Promise<OmegaUser[] | null> {
  const response = await apiGet<ApiUser[]>('/api/omega/users')
  if (response.success && response.data) {
    return response.data.map(mapApiUserToOmegaUser)
  }
  return null
}

export async function getOmegaTickets(): Promise<OmegaTicket[] | null> {
  const response = await apiGet<ApiTicket[]>('/api/omega/tickets')
  if (response.success && response.data) {
    // Precisa buscar usuários para mapear nomes
    const users = await getOmegaUsers()
    const usersList = users || []
    return response.data.map(t => mapApiTicketToOmegaTicket(t, usersList))
  }
  return null
}

export async function getOmegaStatuses(): Promise<OmegaStatus[] | null> {
  const response = await apiGet<ApiStatus[]>('/api/omega/statuses')
  if (response.success && response.data) {
    return response.data.map(mapApiStatusToOmegaStatus)
  }
  return null
}

export async function getOmegaStructure(): Promise<OmegaStructure[] | null> {
  const response = await apiGet<OmegaStructure[]>('/api/omega/structure')
  if (response.success && response.data) {
    return response.data
  }
  return null
}

export async function getOmegaMesu(): Promise<OmegaMesuRecord[] | null> {
  const response = await apiGet<OmegaMesuRecord[]>('/api/omega/mesu')
  if (response.success && response.data) {
    return response.data
  }
  return null
}

export async function createOmegaTicket(
  ticket: Partial<OmegaTicket>
): Promise<ApiResponse<OmegaTicket | OmegaTicket[]>> {
  return apiPost<OmegaTicket | OmegaTicket[]>('/api/omega/tickets', ticket)
}

export async function updateOmegaTicket(
  ticketId: string,
  updates: Partial<OmegaTicket>
): Promise<ApiResponse<OmegaTicket>> {
  return apiPost<OmegaTicket>(`/api/omega/tickets/${ticketId}`, updates)
}

