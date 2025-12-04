export type OmegaRole = 'usuario' | 'analista' | 'supervisor' | 'admin'

export type OmegaView = 'my' | 'assigned' | 'queue' | 'team' | 'team-edit-analyst' | 'team-edit-status' | 'team-graphs' | 'admin'

export type OmegaStatusTone = 'neutral' | 'info' | 'progress' | 'success' | 'warning' | 'danger'

export type OmegaPriority = 'baixa' | 'media' | 'alta' | 'critica'

export type OmegaRoleLabel = {
  usuario: string
  analista: string
  supervisor: string
  admin: string
}

export type OmegaNavItem = {
  id: string
  label: string
  icon: string
  roles: OmegaRole[]
  children?: OmegaNavItem[]
}

export type OmegaStatus = {
  id: string
  label: string
  tone: OmegaStatusTone
  description?: string
  order: number
  departmentId: string
}

export type OmegaPriorityMeta = {
  label: string
  tone: string
  icon: string
}

export type OmegaUser = {
  id: string
  name: string
  role: OmegaRole
  roles: Record<OmegaRole, boolean>
  matrixAccess: boolean
  avatar?: string | null
  position: string
  junction: string
  functional: string
  queues: string[]
  defaultQueue?: string | null | undefined
  teamId: string | null
}

export type OmegaTicket = {
  id: string
  subject: string
  company: string
  requesterName: string
  productId: string
  product: string
  family: string
  section: string
  queue: string
  status: string
  category: string
  priority: OmegaPriority
  opened: string
  updated: string
  dueDate: string | null
  requesterId: string | null
  ownerId: string | null
  teamId: string | null
  context: OmegaTicketContext
  history: OmegaHistoryEntry[]
  credit?: string
  attachments: string[]
  flow?: OmegaFlow | null
}

export type OmegaTicketContext = {
  diretoria: string
  gerencia: string
  agencia: string
  ggestao: string
  gerente: string
  familia: string
  secao: string
  prodsub: string
}

export type OmegaHistoryEntry = {
  date?: string | null | undefined
  actorId?: string | null | undefined
  action?: string | null | undefined
  comment?: string | null | undefined
  status: string
  attachments?: Array<{ id: string; name: string }>
}

export type OmegaFlow = {
  type: string
  targetManager?: {
    name: string
    email: string
    contactId: string
  }
  requesterManager?: {
    name: string
    email: string
    contactId: string
  } | null
  approvals: Array<{
    role: string
    contactId: string
    name: string
    email: string
  }>
}

export type OmegaStructure = {
  departamento: string
  departamento_id?: string
  tipo: string
  ordem_departamento?: number
  ordem_tipo?: number
}

export type OmegaMesuRecord = {
  segment: string
  segmentId: string
  diretoria: string
  diretoriaId: string
  gerencia: string
  gerenciaId: string
  agencyName: string
  agencyId: string
  managerGestaoName: string
  managerGestaoId: string
  managerName: string
  managerId: string
  managerEmail: string
  managerContactId?: string
}

export type OmegaInitData = {
  structure?: OmegaStructure[]
  statuses?: OmegaStatus[]
  users?: OmegaUser[]
  tickets?: OmegaTicket[]
  mesu?: OmegaMesuRecord[]
}

export type OmegaFilters = {
  id: string
  departments: string[]
  type: string
  requester: string
  statuses: string[]
  openedFrom: string
  openedTo: string
  priority: string
}

export type OmegaState = {
  currentUserId: string | null
  view: OmegaView
  search: string
  contextDetail: unknown | null
  selectedTicketId: string | null
  selectedTicketIds: Set<string>
  openNavParents: Set<string>
  drawerOpen: boolean
  sidebarCollapsed: boolean
  manageAnalystOpen: boolean
  manageStatusOpen: boolean
  manageAnalystId: string | null
  manageStatus: {
    departmentId: string
    statusId: string
  }
  pendingNewTicket: unknown | null
  prefillDepartment: string
  ticketModalOpen: boolean
  tablePage: number
  ticketsPerPage: number
  formAttachments: Array<{ id: string; name: string; size: number | null; file: File }>
  formFlow: {
    type: string
    typeValue: string
    targetManagerName: string
    targetManagerEmail: string
    requesterManagerName: string
    requesterManagerEmail: string
  }
  ticketUpdate: {
    comment: string
    status: string
    priority: string
    queue: string
    owner: string
    category: string
    due: string
    attachments: Array<{ id: string; name: string; size: number | null; file: File }>
  }
  ticketUpdateTicketId: string | null
  cancelDialogOpen: boolean
  filters: OmegaFilters
  filterPanelOpen: boolean
  bulkPanelOpen: boolean
  bulkStatus: string
  notifications: Array<{
    id: string
    ticketId: string
    ticketSubject: string
    date: string
    actorId: string
    action: string
    comment: string
    status: string
    type: string
    read: boolean
  }>
  notificationPanelOpen: boolean
}
