import { ref, computed, type Ref } from 'vue'
import {
  getOmegaInit,
  getOmegaUsers,
  getOmegaTickets,
  getOmegaStatuses,
  getOmegaStructure,
  getOmegaMesu,
  createOmegaTicket,
  updateOmegaTicket
} from '../services/omegaService'
import type { OmegaUser } from '../types/omega'
import type {
  OmegaInitData,
  OmegaTicket,
  OmegaStatus,
  OmegaStructure,
  OmegaMesuRecord,
  OmegaView,
  OmegaRole
} from '../types/omega'

const OMEGA_ROLE_LABELS = {
  usuario: 'Usuário',
  analista: 'Analista',
  supervisor: 'Supervisor',
  admin: 'Administrador'
} as const

// Menus diferentes para cada role
const OMEGA_NAV_ITEMS = [
  // Menu do usuário
  { id: 'my', label: 'Meus chamados', icon: 'ti ti-user', roles: ['usuario', 'analista', 'supervisor', 'admin'] as OmegaRole[] },
  
  // Menu do analista
  { id: 'assigned', label: 'Meus atendimentos', icon: 'ti ti-clipboard-check', roles: ['analista', 'supervisor', 'admin'] as OmegaRole[] },
  { id: 'queue', label: 'Fila da equipe', icon: 'ti ti-inbox', roles: ['analista', 'supervisor', 'admin'] as OmegaRole[] },
  
  // Menu do supervisor
  {
    id: 'team',
    label: 'Visão da supervisão',
    icon: 'ti ti-users',
    roles: ['supervisor', 'admin'] as OmegaRole[],
    children: [
      { id: 'team-edit-analyst', label: 'Gerenciar analistas', icon: 'ti ti-user-cog', roles: [] as OmegaRole[] },
      { id: 'team-edit-status', label: 'Editar status', icon: 'ti ti-adjustments-alt', roles: [] as OmegaRole[] },
      { id: 'team-graphs', label: 'Gráficos', icon: 'ti ti-chart-arcs', roles: [] as OmegaRole[] }
    ]
  },
  
  // Menu do admin
  { id: 'admin', label: 'Administração', icon: 'ti ti-shield-lock', roles: ['admin'] as OmegaRole[] }
] as const

const OMEGA_PRIORITY_META = {
  baixa: { label: 'Baixa', tone: 'neutral', icon: 'ti ti-arrow-down' },
  media: { label: 'Média', tone: 'progress', icon: 'ti ti-arrows-up-down' },
  alta: { label: 'Alta', tone: 'warning', icon: 'ti ti-arrow-up' },
  critica: { label: 'Crítica', tone: 'danger', icon: 'ti ti-alert-octagon' }
} as const

const OMEGA_DEFAULT_STATUSES: OmegaStatus[] = [
  { id: 'aberto', label: 'Aberto', tone: 'neutral', order: 1, departmentId: '0' },
  { id: 'aguardando', label: 'Aguardando', tone: 'warning', order: 2, departmentId: '0' },
  { id: 'em_atendimento', label: 'Em atendimento', tone: 'progress', order: 3, departmentId: '0' },
  { id: 'resolvido', label: 'Resolvido', tone: 'success', order: 4, departmentId: '0' },
  { id: 'cancelado', label: 'Cancelado', tone: 'danger', order: 5, departmentId: '0' }
]

// Status finais que não permitem réplica do usuário
const OMEGA_FINAL_STATUSES = ['resolvido', 'cancelado', 'finalizado']

function isFinalStatus(statusId: string): boolean {
  return OMEGA_FINAL_STATUSES.includes(statusId.toLowerCase())
}

const initData = ref<OmegaInitData | null>(null)
const users = ref<OmegaUser[]>([])
const tickets = ref<OmegaTicket[]>([])
const statuses = ref<OmegaStatus[]>([])
const structure = ref<OmegaStructure[]>([])
const mesu = ref<OmegaMesuRecord[]>([])

const isLoading = ref(false)
const error = ref<string | null>(null)
const currentUserId = ref<string | null>(null)
const currentView = ref<OmegaView>('my')

let initPromise: Promise<OmegaInitData | null> | null = null

export function useOmega() {
  const loadInit = async (): Promise<OmegaInitData | null> => {
    
    if (initData.value) {
      return initData.value
    }

    if (initPromise) {
      return initPromise
    }

    isLoading.value = true
    error.value = null

    initPromise = getOmegaInit()
      .then((data) => {
        if (data) {
          initData.value = data

          if (Array.isArray(data.structure)) {
            structure.value = data.structure
          }

          if (Array.isArray(data.statuses)) {
            statuses.value = data.statuses.length > 0 ? data.statuses : OMEGA_DEFAULT_STATUSES
          } else {
            statuses.value = OMEGA_DEFAULT_STATUSES
          }

          if (Array.isArray(data.users)) {
            users.value = data.users
            if (users.value.length > 0 && !currentUserId.value) {
              currentUserId.value = users.value[0]?.id || null
            }
          }

          if (Array.isArray(data.tickets)) {
            tickets.value = data.tickets
          }

          if (Array.isArray(data.mesu)) {
            mesu.value = data.mesu
          }
        }
        return data
      })
      .catch((err) => {
        error.value = err instanceof Error ? err.message : 'Erro ao carregar dados do Omega'
        console.error('❌ Erro ao carregar dados de inicialização do Omega:', err)
        return null
      })
      .finally(() => {
        isLoading.value = false
        initPromise = null
      })

    return initPromise
  }

  const loadUsers = async (): Promise<OmegaUser[] | null> => {
    if (users.value.length > 0) {
      return users.value
    }

    isLoading.value = true
    error.value = null

    try {
      const data = await getOmegaUsers()
      if (data) {
        users.value = data
        if (users.value.length > 0 && !currentUserId.value) {
          currentUserId.value = users.value[0]?.id || null
        }
        return data
      }
      return null
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao carregar usuários'
      console.error('Erro ao carregar usuários do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const loadTickets = async (): Promise<OmegaTicket[] | null> => {
    isLoading.value = true
    error.value = null

    try {
      const data = await getOmegaTickets()
      if (data) {
        tickets.value = data
        return data
      }
      return null
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao carregar tickets'
      console.error('Erro ao carregar tickets do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const loadStatuses = async (): Promise<OmegaStatus[] | null> => {
    if (statuses.value.length > 0) {
      return statuses.value
    }

    isLoading.value = true
    error.value = null

    try {
      const data = await getOmegaStatuses()
      if (data) {
        statuses.value = data.length > 0 ? data : OMEGA_DEFAULT_STATUSES
        return statuses.value
      }
      statuses.value = OMEGA_DEFAULT_STATUSES
      return statuses.value
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao carregar status'
      console.error('Erro ao carregar status do Omega:', err)
      statuses.value = OMEGA_DEFAULT_STATUSES
      return statuses.value
    } finally {
      isLoading.value = false
    }
  }

  const loadStructure = async (): Promise<OmegaStructure[] | null> => {
    if (structure.value.length > 0) {
      return structure.value
    }

    isLoading.value = true
    error.value = null

    try {
      const data = await getOmegaStructure()
      if (data) {
        structure.value = data
        return data
      }
      return null
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao carregar estrutura'
      console.error('Erro ao carregar estrutura do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const loadMesu = async (): Promise<OmegaMesuRecord[] | null> => {
    if (mesu.value.length > 0) {
      return mesu.value
    }

    isLoading.value = true
    error.value = null

    try {
      const data = await getOmegaMesu()
      if (data) {
        mesu.value = data
        return data
      }
      return null
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao carregar MESU'
      console.error('Erro ao carregar MESU do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const createTicket = async (ticket: Partial<OmegaTicket>): Promise<OmegaTicket | null> => {
    isLoading.value = true
    error.value = null

    try {
      const response = await createOmegaTicket(ticket)
      if (response.success && response.data) {
        const newTicket = Array.isArray(response.data) ? response.data[0] : response.data
        if (newTicket) {
          tickets.value.unshift(newTicket)
          return newTicket
        }
      }
      throw new Error(response.error || 'Erro ao criar ticket')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao criar ticket'
      console.error('Erro ao criar ticket do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const updateTicket = async (ticketId: string, updates: Partial<OmegaTicket>): Promise<OmegaTicket | null> => {
    isLoading.value = true
    error.value = null

    try {
      const response = await updateOmegaTicket(ticketId, updates)
      if (response.success && response.data) {
        // Recarrega os tickets para garantir que temos a versão mais atualizada
        await loadTickets()
        
        // Retorna o ticket atualizado da lista
        const updatedTicket = tickets.value.find((t) => t.id === ticketId)
        return updatedTicket || null
      }
      throw new Error(response.error || 'Erro ao atualizar ticket')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Erro ao atualizar ticket'
      console.error('Erro ao atualizar ticket do Omega:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  const currentUser = computed<OmegaUser | null>(() => {
    if (!users.value.length || !currentUserId.value) {
      return null
    }
    return users.value.find((u) => u.id === currentUserId.value) || users.value[0] || null
  })

  const getNavItemsForRole = (role: OmegaRole) => {
    return OMEGA_NAV_ITEMS.filter((item) => item.roles.includes(role))
  }

  const getRoleLabel = (role: OmegaRole): string => {
    return OMEGA_ROLE_LABELS[role] || role
  }

  const getPriorityMeta = (priority: string) => {
    return OMEGA_PRIORITY_META[priority as keyof typeof OMEGA_PRIORITY_META] || OMEGA_PRIORITY_META.media
  }

  const setCurrentUserId = (userId: string | null): void => {
    currentUserId.value = userId
  }

  const setCurrentView = (view: OmegaView): void => {
    currentView.value = view
  }

  const clearCache = (): void => {
    initData.value = null
    users.value = []
    tickets.value = []
    statuses.value = []
    structure.value = []
    mesu.value = []
    currentUserId.value = null
    currentView.value = 'my'
  }

  const canUserReply = (ticket: OmegaTicket): boolean => {
    // Status finais não permitem réplica do usuário
    return !isFinalStatus(ticket.status)
  }

  return {
    initData: computed(() => initData.value),
    users: computed(() => users.value),
    tickets: computed(() => tickets.value),
    statuses: computed(() => statuses.value),
    structure: computed(() => structure.value),
    mesu: computed(() => mesu.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    currentUserId: computed(() => currentUserId.value),
    currentView: computed(() => currentView.value),
    currentUser,
    loadInit,
    loadUsers,
    loadTickets,
    loadStatuses,
    loadStructure,
    loadMesu,
    createTicket,
    updateTicket,
    getNavItemsForRole,
    getRoleLabel,
    getPriorityMeta,
    setCurrentUserId,
    setCurrentView,
    clearCache,
    canUserReply,
    isFinalStatus,
    constants: {
      ROLE_LABELS: OMEGA_ROLE_LABELS,
      NAV_ITEMS: OMEGA_NAV_ITEMS,
      PRIORITY_META: OMEGA_PRIORITY_META,
      DEFAULT_STATUSES: OMEGA_DEFAULT_STATUSES,
      FINAL_STATUSES: OMEGA_FINAL_STATUSES
    }
  }
}

