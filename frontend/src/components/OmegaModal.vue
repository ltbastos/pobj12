<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useOmega } from '../composables/useOmega'
import { useOmegaFilters } from '../composables/useOmegaFilters'
import { useOmegaBulk } from '../composables/useOmegaBulk'
import { useOmegaFullscreen } from '../composables/useOmegaFullscreen'
import { useOmegaRender } from '../composables/useOmegaRender'
import OmegaFilterPanel from './OmegaFilterPanel.vue'
import OmegaHeader from './omega/OmegaHeader.vue'
import OmegaSidebar from './omega/OmegaSidebar.vue'
import OmegaToolbar from './omega/OmegaToolbar.vue'
import OmegaTable from './omega/OmegaTable.vue'
import OmegaBulkPanel from './omega/OmegaBulkPanel.vue'
import OmegaDrawer from './omega/OmegaDrawer.vue'
import '../assets/omega.css'

interface Props {
  modelValue?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false
})

const emit = defineEmits<Emits>()

const omega = useOmega()
const filters = useOmegaFilters()
const bulk = useOmegaBulk(omega)
const fullscreen = useOmegaFullscreen()
const isFullscreen = computed(() => {
  const modal = modalRoot.value
  return modal ? modal.classList.contains('omega-modal--fullscreen') : false
})
const render = useOmegaRender(omega, filters, bulk)

const isOpen = ref(false)
const modalRoot = ref<HTMLElement | null>(null)
const mainContentRef = ref<HTMLElement | null>(null)
const sidebarCollapsed = ref(false)
const searchQuery = ref('')

const addedBodyClasses = ['has-omega-open']

// Estado compartilhado globalmente para permitir acesso externo
if (typeof window !== 'undefined') {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const globalAny = window as any
  if (!globalAny.__omegaModalState) {
    globalAny.__omegaModalState = { isOpen: ref(false) }
  }
}

function updateModalVisibility(open: boolean) {
  isOpen.value = open
}

watch(() => props.modelValue, (newValue) => {
  updateModalVisibility(newValue)
  if (typeof window !== 'undefined') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const globalAny = window as any
    if (globalAny.__omegaModalState) {
      globalAny.__omegaModalState.isOpen.value = newValue
    }
  }
  if (newValue) {
    ensureBodyState()
    loadOmegaData()
    nextTick(() => {
      if (isOpen.value) {
        renderMainContent()
      }
    })
  } else {
    resetBodyState()
  }
}, { immediate: true })

// Observa mudanças nos dados e re-renderiza
// Nota: Não precisa re-renderizar tickets/users quando usando componentes Vue (eles são reativos)
watch(() => omega.tickets.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      // Apenas renderiza partes que não são componentes Vue
      renderOmegaData()
    })
  }
}, { deep: true })

watch(() => omega.users.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      // Apenas renderiza partes que não são componentes Vue
      renderOmegaData()
    })
  }
}, { deep: true })

watch(() => omega.currentUserId.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      // Apenas renderiza partes que não são componentes Vue
      renderOmegaData()
    })
  }
})

watch(() => omega.currentView.value, () => {
  if (isOpen.value) {
    // Não precisa chamar renderOmegaData() aqui porque OmegaTable é reativo
    // Apenas renderiza partes que não são componentes Vue
    nextTick(() => {
      renderOmegaData()
    })
  }
})

// Watch o estado global também
if (typeof window !== 'undefined') {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const globalAny = window as any
  if (globalAny.__omegaModalState) {
    watch(globalAny.__omegaModalState.isOpen, (newValue) => {
      if (isOpen.value !== newValue) {
        updateModalVisibility(newValue)
        emit('update:modelValue', newValue)
        if (newValue) {
          ensureBodyState()
          loadOmegaData()
        } else {
          resetBodyState()
        }
      }
    })
  }
}

function ensureBodyState() {
  if (typeof document === 'undefined') return
  addedBodyClasses.forEach((cls) => document.body.classList.add(cls))
}

function resetBodyState() {
  if (typeof document === 'undefined') return
  addedBodyClasses.forEach((cls) => document.body.classList.remove(cls))
}

async function loadOmegaData() {
  
  // Mostra loading state
  const modalElement = modalRoot.value
  if (modalElement) {
    showLoadingState(modalElement)
  }
  
  try {
    const data = await omega.loadInit()
    if (!data) {
      hideLoadingState(modalElement)
      return
    }
    
    // Aguarda o conteúdo ser renderizado
    nextTick(() => {
      setTimeout(() => {
        hideLoadingState(modalElement)
        renderOmegaData()
      }, 100)
    })
  } catch (err) {
    hideLoadingState(modalElement)
    showErrorState(modalElement)
  }
}

function showLoadingState(root: HTMLElement | null) {
  if (!root) return
  
  // Verifica se estamos usando componentes Vue (OmegaTable)
  const omegaTableComponent = root.querySelector('.omega-table-container')
  const isUsingVueComponents = !!omegaTableComponent
  
  // Se estiver usando componentes Vue, não manipula o DOM diretamente
  if (isUsingVueComponents) {
    return
  }
  
  const mainContent = root.querySelector('.omega-main__content')
  if (mainContent) {
    mainContent.classList.add('omega-loading')
    const tbody = root.querySelector('#omega-ticket-rows')
    if (tbody) {
      tbody.innerHTML = `
        ${Array.from({ length: 5 }).map(() => `
          <tr class="omega-skeleton-row">
            <td class="col-select"><div class="omega-skeleton omega-skeleton--checkbox"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text" style="width: 80%"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--badge"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--text"></div></td>
            <td><div class="omega-skeleton omega-skeleton--badge"></div></td>
          </tr>
        `).join('')}
      `
    }
  }
}

function hideLoadingState(root: HTMLElement | null) {
  if (!root) return
  
  const mainContent = root.querySelector('.omega-main__content') || mainContentRef.value?.querySelector('.omega-main__content')
  if (mainContent) {
    mainContent.classList.remove('omega-loading')
  }
}

function showErrorState(root: HTMLElement | null) {
  if (!root) return
  
  const tbody = root.querySelector('#omega-ticket-rows') || mainContentRef.value?.querySelector('#omega-ticket-rows')
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="omega-empty-state">
          <div class="omega-empty-state__content">
            <i class="ti ti-alert-circle" style="font-size: 48px; color: var(--brad-color-error); margin-bottom: 16px;"></i>
            <h3>Erro ao carregar dados</h3>
            <p>Não foi possível carregar os chamados. Tente novamente.</p>
            <button class="omega-btn omega-btn--primary" onclick="location.reload()" style="margin-top: 16px;">
              <i class="ti ti-refresh"></i>
              <span>Recarregar</span>
            </button>
          </div>
        </td>
      </tr>
    `
  }
}

function renderOmegaData() {
  render.renderOmegaData()
  applySidebarState()
}

function setSidebarCollapsed(collapsed: boolean) {
  sidebarCollapsed.value = !!collapsed
  applySidebarState()
}

function applySidebarState() {
  // Sidebar state is now handled by the component itself
}

function handleNavClick(viewId: string) {
  omega.setCurrentView(viewId as any)
  // Não precisa chamar renderOmegaData() porque OmegaTable é reativo
  // Apenas renderiza partes que não são componentes Vue (sidebar, etc)
  nextTick(() => {
    renderOmegaData()
  })
}

function handleSearch(value: string) {
  searchQuery.value = value
  renderOmegaData()
}

function handleFilterToggle() {
  filters.toggleFilterPanel()
}

function handleClearFilters() {
  filters.resetFilters()
  renderOmegaData()
}

function handleRefresh() {
  omega.clearCache()
  omega.loadInit().then(() => {
    renderOmegaData()
  })
}

const drawerOpen = ref(false)
const drawerInitialData = ref<{ department?: string; type?: string; observation?: string } | undefined>(undefined)

function handleNewTicket(initialData?: { department?: string; type?: string; observation?: string }) {
  drawerInitialData.value = initialData
  drawerOpen.value = true
}

// Função para construir observação a partir dos detalhes do nó
function buildObservationFromDetail(detail: any): string {
  if (!detail || !detail.node) return ''
  
  const node = detail.node
  const parts: string[] = []
  
  if (node.label) {
    parts.push(`Item: ${node.label}`)
  }
  
  if (node.level) {
    parts.push(`Nível: ${node.level}`)
  }
  
  if (node.summary) {
    const summary = node.summary
    if (summary.valor_realizado) {
      parts.push(`Realizado: R$ ${summary.valor_realizado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`)
    }
    if (summary.valor_meta) {
      parts.push(`Meta: R$ ${summary.valor_meta.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`)
    }
    if (summary.atingimento_p !== undefined) {
      parts.push(`Atingimento: ${summary.atingimento_p.toFixed(1)}%`)
    }
  }
  
  if (node.detail) {
    const detailInfo = node.detail
    if (detailInfo.gerente) {
      parts.push(`Gerente: ${detailInfo.gerente}`)
    }
    if (detailInfo.canal_venda) {
      parts.push(`Canal: ${detailInfo.canal_venda}`)
    }
  }
  
  return parts.join('\n')
}

// Função exposta globalmente para abrir o modal com dados iniciais
function openOmegaWithData(detail: { department?: string; type?: string; observation?: string; openDrawer?: boolean }) {
  openModal()
  if (detail.openDrawer) {
    nextTick(() => {
      handleNewTicket({
        department: detail.department,
        type: detail.type,
        observation: detail.observation
      })
    })
  }
}

// Função para mostrar feedback no formulário
function showFormFeedback(root: HTMLElement, message: string, tone: 'info' | 'warning' | 'danger' | 'success' = 'info') {
  const feedback = root.querySelector('#omega-form-feedback') as HTMLElement
  if (!feedback) return
  feedback.textContent = message
  feedback.hidden = false
  feedback.className = `omega-feedback omega-feedback--${tone}`
}

// Função para limpar feedback do formulário
function clearFormFeedback(root: HTMLElement) {
  const feedback = root.querySelector('#omega-form-feedback') as HTMLElement
  if (!feedback) return
  feedback.hidden = true
  feedback.textContent = ''
  feedback.className = 'omega-feedback'
}

// Função para mostrar toast
function showOmegaToast(message: string, tone: 'success' | 'info' | 'warning' | 'danger' = 'success') {
  if (!message) return
  const root = modalRoot.value
  if (!root) return
  const container = root.querySelector('#omega-toast-stack') as HTMLElement
  if (!container) return
  
  const icons: Record<string, string> = {
    success: 'ti ti-check',
    info: 'ti ti-info-circle',
    warning: 'ti ti-alert-triangle',
    danger: 'ti ti-alert-circle'
  }
  
  const icon = icons[tone] || icons.info
  const toast = document.createElement('div')
  toast.className = `omega-toast omega-toast--${tone}`
  toast.setAttribute('role', 'status')
  toast.innerHTML = `<i class="${icon}" aria-hidden="true"></i><span>${escapeHTML(message)}</span>`
  container.appendChild(toast)
  
  requestAnimationFrame(() => {
    toast.setAttribute('data-visible', 'true')
  })
  
  const lifetime = 3600
  setTimeout(() => {
    toast.setAttribute('data-visible', 'false')
    setTimeout(() => {
      if (toast.parentElement === container) toast.remove()
    }, 220)
  }, lifetime)
  
  while (container.children.length > 3) {
    const first = container.firstElementChild
    if (!first || first === toast) break
    first.remove()
  }
}

// Função para atualizar o subject do formulário
function updateOmegaFormSubject(root: HTMLElement) {
  const form = root.querySelector('#omega-form') as HTMLFormElement
  if (!form) return
  const subjectInput = form.querySelector('#omega-form-subject') as HTMLInputElement
  if (!subjectInput) return
  
  const typeSelect = form.querySelector('#omega-form-type') as HTMLSelectElement
  const typeValue = typeSelect?.value || ''
  const typeLabel = typeValue ? (typeSelect?.selectedOptions?.[0]?.textContent?.trim() || '') : ''
  const requester = omega.currentUser.value?.name || ''
  
  const parts: string[] = []
  if (typeLabel) parts.push(typeLabel)
  if (requester) parts.push(requester)
  
  subjectInput.value = parts.length ? parts.join(' • ') : 'Chamado Omega'
}

// Função para validar email
function isValidEmail(value: string): boolean {
  if (!value) return false
  const normalized = value.toString().trim()
  if (!normalized) return false
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)
}

// Função principal para criar novo ticket
async function handleNewTicketSubmit(data: any) {
  const user = omega.currentUser.value
  if (!user) {
    showOmegaToast('Usuário não encontrado.', 'danger')
    return
  }
  
  const requesterName = user.name?.trim() || ''
  
  // Constrói o subject
  const subjectParts: string[] = []
  if (data.type) subjectParts.push(data.type)
  if (requesterName) subjectParts.push(requesterName)
  const subject = subjectParts.length ? subjectParts.join(' • ') : 'Chamado Omega'
  
  try {
    const now = new Date().toISOString()
    const newTicket = {
      subject,
      company: requesterName,
      requesterName,
      productId: '',
      product: data.type || '',
      family: '',
      section: '',
      queue: data.department,
      status: 'aberto',
      category: data.type,
      priority: 'media' as const,
      opened: now,
      updated: now,
      dueDate: null,
      requesterId: user.id,
      ownerId: ['analista', 'supervisor', 'admin'].includes(user.role) ? user.id : null,
      teamId: user.teamId || null,
      context: {
        diretoria: '',
        gerencia: '',
        agencia: '',
        ggestao: '',
        gerente: '',
        familia: '',
        secao: '',
        prodsub: data.type || ''
      },
      history: [{
        date: now,
        actorId: user.id,
        action: 'Abertura do chamado',
        comment: data.observation,
        status: 'aberto'
      }],
      attachments: data.attachments?.map((att: any) => att.name) || [],
      flow: data.flow || null
    }
    
    // Chama API para criar ticket
    const { createOmegaTicket } = await import('../services/omegaService')
    const response = await createOmegaTicket(newTicket)
    
    if (response.success && response.data) {
      // Atualiza lista de tickets
      await omega.loadInit()
      
      // Fecha o drawer
      drawerOpen.value = false
      
      // Mostra toast de sucesso
      showOmegaToast('Chamado registrado com sucesso.', 'success')
      
      // Re-renderiza dados
      renderOmegaData()
    } else {
      showOmegaToast(response.error || 'Não foi possível registrar o chamado.', 'danger')
    }
  } catch (err) {
    showOmegaToast('Erro ao registrar o chamado. Tente novamente.', 'danger')
  }
}

function handleBulkStatus() {
  bulk.bulkPanelOpen.value = !bulk.bulkPanelOpen.value
}

function handleTicketClick(ticketId: string) {
  // TODO: Implementar abertura do modal de detalhes do ticket
}

function handleSelectAll(checked: boolean) {
  if (checked) {
    // Seleciona todos os tickets da página atual
    const tickets = omega.tickets.value
    tickets.forEach((ticket) => {
      bulk.selectedTicketIds.value.add(ticket.id)
    })
  } else {
    bulk.selectedTicketIds.value.clear()
  }
}

function handleBulkApply(status: string) {
  bulk.handleBulkStatusSubmit(status)
  renderOmegaData()
}

function handleFullscreenToggle() {
  // Fullscreen is handled by the header component
}

// Funções de renderização movidas para useOmegaRender composable

// Função para atualizar lista (igual ao código antigo)
function refreshTicketList(button: HTMLElement) {
  setButtonLoading(button, true)
  
  // Limpa cache e recarrega
  omega.clearCache()
  omega.loadInit()
    .then(() => {
      renderOmegaData()
    })
    .catch((err) => {
    })
    .finally(() => {
      setButtonLoading(button, false)
    })
}

function setButtonLoading(button: HTMLElement, loading: boolean) {
  if (!button) return
  if (button instanceof HTMLButtonElement || button instanceof HTMLInputElement) {
    button.disabled = !!loading
  }
  if (loading) {
    button.setAttribute('data-loading', 'true')
  } else {
    button.removeAttribute('data-loading')
  }
  const icon = button.querySelector('i')
  if (!icon) return
  if (loading) {
    icon.setAttribute('data-original-icon', icon.className)
    icon.className = 'ti ti-loader-2'
  } else if (icon.getAttribute('data-original-icon')) {
    icon.className = icon.getAttribute('data-original-icon') || 'ti ti-refresh'
    icon.removeAttribute('data-original-icon')
  }
}

// Funções de filtros movidas para useOmegaFilters composable
function setupFilterControls(root: HTMLElement) {
  const filterToggle = root.querySelector('#omega-filters-toggle')
  const filterForm = root.querySelector('#omega-filter-form')
  const clearFiltersBtn = root.querySelector('#omega-clear-filters')
  const clearFiltersTop = root.querySelector('#omega-clear-filters-top')

  filterToggle?.addEventListener('click', () => {
    filters.toggleFilterPanel()
    const modalElement = document.getElementById('omega-modal')
    if (modalElement) {
      let panel = modalElement.querySelector('#omega-filter-panel') as HTMLElement
      // Se o painel não está no body, move para o body para garantir z-index correto acima do header da tabela
      if (panel && panel.parentElement !== document.body) {
        document.body.appendChild(panel)
      }
      const toggle = modalElement.querySelector('#omega-filters-toggle')
      if (panel) panel.hidden = !filters.filterPanelOpen.value
      if (toggle) toggle.setAttribute('aria-expanded', filters.filterPanelOpen.value ? 'true' : 'false')
      if (filters.filterPanelOpen.value) {
        // Re-renderiza estrutura para garantir que os selects estejam populados ANTES de sincronizar
        render.renderStructure(modalElement)
        filters.syncFilterFormState(modalElement)
      }
      if (toggle) {
        toggle.setAttribute('data-active', filters.hasActiveFilters() ? 'true' : 'false')
      }
    }
  })

  filterForm?.addEventListener('submit', (ev) => {
    ev.preventDefault()
    filters.applyFiltersFromForm(root)
    renderOmegaData()
  })

  clearFiltersBtn?.addEventListener('click', () => {
    filters.resetFilters()
    filters.syncFilterFormState(root)
    renderOmegaData()
  })

  clearFiltersTop?.addEventListener('click', () => {
    filters.resetFilters()
    filters.syncFilterFormState(root)
    renderOmegaData()
  })

  // Atualiza estado inicial do botão
  if (filterToggle) {
    filterToggle.setAttribute('data-active', filters.hasActiveFilters() ? 'true' : 'false')
  }
}

function handleFilterApply() {
  renderOmegaData() // Re-renderiza após aplicar filtros
}

function handleFilterClear() {
  renderOmegaData() // Re-renderiza após limpar filtros
}

// Função para obter departamentos disponíveis para o usuário
function getAvailableDepartmentsForUser(user: any): string[] {
  if (!user) return []
  
  // Obtém departamentos únicos da estrutura
  const departments = new Map<string, string>()
  omega.structure.value.forEach((item) => {
    if (item.departamento && !departments.has(item.departamento)) {
      departments.set(item.departamento, item.departamento_id || item.departamento)
    }
  })
  
  const allDepartments = Array.from(departments.keys())
  
  // Se for admin, retorna todos
  if (user.role === 'admin') return allDepartments
  
  // Se for usuário comum, filtra Matriz se não tiver acesso
  if (user.role === 'usuario') {
    return user.matrixAccess ? allDepartments : allDepartments.filter((d) => d !== 'Matriz')
  }
  
  // Para outros roles, retorna todos por enquanto
  return allDepartments
}

// Função para obter tipos de chamado por departamento
function getTicketTypesForDepartment(department: string): string[] {
  if (!department) return []
  
  const types = new Set<string>()
  omega.structure.value.forEach((item) => {
    if (item.departamento === department && item.tipo) {
      types.add(item.tipo)
    }
  })
  
  return Array.from(types).sort()
}

// Função para sincronizar opções de tipo baseado no departamento
function syncTicketTypeOptions(container: HTMLElement, department: string, options: { preserveSelection?: boolean; selectedType?: string } = {}) {
  const typeSelect = container.querySelector('#omega-form-type') as HTMLSelectElement
  if (!typeSelect) return
  
  const types = getTicketTypesForDepartment(department)
  const previousValue = options.preserveSelection ? (options.selectedType || typeSelect.value || '') : (options.selectedType || '')
  
  if (types.length) {
    const placeholder = '<option value="">Selecione o tipo de chamado</option>'
    typeSelect.innerHTML = [
      placeholder,
      ...types.map((type) => `<option value="${escapeHTML(type)}">${escapeHTML(type)}</option>`)
    ].join('')
    
    const nextValue = previousValue && types.includes(previousValue) ? previousValue : ''
    if (nextValue) {
      typeSelect.value = nextValue
    } else {
      typeSelect.value = ''
      typeSelect.selectedIndex = 0
    }
  } else {
    typeSelect.innerHTML = '<option value="" disabled>Selecione um departamento primeiro</option>'
    typeSelect.value = ''
  }
  
  typeSelect.disabled = !types.length
}

// Função para escapar HTML
function escapeHTML(value: string): string {
  if (!value) return ''
  const div = document.createElement('div')
  div.textContent = value
  return div.innerHTML
}

// Função para popular opções do formulário de novo ticket
function populateFormOptions(root: HTMLElement) {
  const form = root.querySelector('#omega-form')
  if (!form) return
  
  const user = omega.currentUser.value
  if (!user) return
  
  const departmentSelect = form.querySelector('#omega-form-department') as HTMLSelectElement
  const requesterDisplay = form.querySelector('#omega-form-requester')
  
  // Atualiza nome do solicitante
  if (requesterDisplay) {
    requesterDisplay.textContent = user.name || '—'
  }
  
  // Popula departamentos
  if (departmentSelect) {
    const departments = getAvailableDepartmentsForUser(user)
    const previous = departmentSelect.value
    
    if (departments.length) {
      const placeholder = '<option value="">Selecione um departamento</option>'
      departmentSelect.innerHTML = [
        placeholder,
        ...departments.map((dept) => `<option value="${escapeHTML(dept)}">${escapeHTML(dept)}</option>`)
      ].join('')
      
      const nextValue = previous && departments.includes(previous) ? previous : ''
      if (nextValue) {
        departmentSelect.value = nextValue
      } else {
        departmentSelect.value = ''
        departmentSelect.selectedIndex = 0
      }
    } else {
      departmentSelect.innerHTML = '<option value="" disabled>Nenhum departamento disponível</option>'
      departmentSelect.value = ''
    }
    
    departmentSelect.disabled = !departments.length
    
    // Remove listeners antigos e adiciona novo listener para mudança de departamento
    const newDeptSelect = departmentSelect.cloneNode(true) as HTMLSelectElement
    departmentSelect.parentNode?.replaceChild(newDeptSelect, departmentSelect)
    
    newDeptSelect.addEventListener('change', () => {
      syncTicketTypeOptions(form as HTMLElement, newDeptSelect.value, { preserveSelection: false })
    })
    
    // Sincroniza tipos iniciais
    syncTicketTypeOptions(form as HTMLElement, newDeptSelect.value, { preserveSelection: true })
  }
}

// Funções de bulk movidas para useOmegaBulk composable

// Funções de fullscreen movidas para useOmegaFullscreen composable

function openModal() {
  isOpen.value = true
  emit('update:modelValue', true)
  if (typeof window !== 'undefined') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const globalAny = window as any
    if (globalAny.__omegaModalState) {
      globalAny.__omegaModalState.isOpen.value = true
    }
  }
  ensureBodyState()
  
  // Força a atualização da visibilidade primeiro
  nextTick(() => {
    updateModalVisibility(true)
    
    // Aplica estado inicial da sidebar
    nextTick(() => {
      applySidebarState()
      
      // Se os dados já estão carregados, renderiza imediatamente
      if (omega.initData.value && omega.tickets.value.length > 0) {
        setTimeout(() => {
          renderOmegaData()
        }, 100)
      } else {
        // Caso contrário, carrega os dados
    loadOmegaData()
      }
    })
  })
}

function closeModal() {
  updateModalVisibility(false)
  emit('update:modelValue', false)
  emit('close')
  if (typeof window !== 'undefined') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const globalAny = window as any
    if (globalAny.__omegaModalState) {
      globalAny.__omegaModalState.isOpen.value = false
    }
  }
  resetBodyState()
}

function registerGlobalOpener() {
  if (typeof window === 'undefined') return
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const globalAny = window as any
  
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.__openOmegaFromVue = (detail?: any) => {
    if (!omega.isLoading.value) {
      if (detail && (detail.openDrawer || detail.intent === 'new-ticket')) {
        openOmegaWithData({
          department: detail.preferredQueue || detail.queue || 'POBJ',
          type: detail.type,
          observation: detail.observation || buildObservationFromDetail(detail),
          openDrawer: true
        })
      } else {
        openModal()
      }
    }
  }
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.openOmegaModule = (detail?: any) => {
    if (!omega.isLoading.value) {
      if (detail && (detail.openDrawer || detail.intent === 'new-ticket')) {
        openOmegaWithData({
          department: detail.preferredQueue || detail.queue || 'POBJ',
          type: detail.type,
          observation: detail.observation || buildObservationFromDetail(detail),
          openDrawer: true
        })
      } else {
        openModal()
      }
    }
  }
  
  globalAny.openOmega = globalAny.openOmegaModule
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.closeOmega = () => {
    closeModal()
  }
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.closeOmegaModule = () => {
    closeModal()
  }
}

function unregisterGlobalOpener() {
  if (typeof window === 'undefined') return
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const globalAny = window as any
  if (globalAny.__openOmegaFromVue) {
    delete globalAny.__openOmegaFromVue
  }
  if (globalAny.openOmegaModule) {
    delete globalAny.openOmegaModule
  }
  if (globalAny.openOmega) {
    delete globalAny.openOmega
  }
  if (globalAny.closeOmega) {
    delete globalAny.closeOmega
  }
  if (globalAny.closeOmegaModule) {
    delete globalAny.closeOmegaModule
  }
}

// Drawer agora é um componente Vue, não precisa mais renderizar HTML estático
function renderMainContent() {
  // Esta função não é mais necessária, mas mantida para compatibilidade
  // O drawer agora é gerenciado pelo componente OmegaDrawer.vue
}

onMounted(() => {
  registerGlobalOpener()
  
  if (props.modelValue) {
    openModal()
  }
})

onBeforeUnmount(() => {
  unregisterGlobalOpener()
  resetBodyState()
  fullscreen.cleanup()
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      id="omega-modal"
      ref="modalRoot"
      class="omega-modal"
      :class="{ 'omega-modal--fullscreen': isFullscreen }"
      data-omega-standalone
    >
      <div class="omega-modal__overlay" @click="closeModal"></div>
      <section class="omega-modal__panel" role="dialog" aria-modal="true" aria-labelledby="omega-title">
        <div id="omega-toast-stack" class="omega-toast-stack" aria-live="polite" aria-atomic="true"></div>
        
        <OmegaHeader
          :omega="omega"
          :fullscreen="fullscreen"
          @close="closeModal"
          @fullscreen-toggle="handleFullscreenToggle"
        />

        <div class="omega-body" :data-sidebar-collapsed="sidebarCollapsed ? 'true' : 'false'">
          <OmegaSidebar
            :omega="omega"
            :collapsed="sidebarCollapsed"
            @update:collapsed="sidebarCollapsed = $event"
            @nav-click="handleNavClick"
          />

          <section class="omega-main">
            <div id="omega-breadcrumb" class="omega-breadcrumb"></div>
            <div id="omega-context" class="omega-context" hidden></div>

            <OmegaToolbar
              :omega="omega"
              :filters="filters"
              :bulk="bulk"
              @search="handleSearch"
              @filter-toggle="handleFilterToggle"
              @clear-filters="handleClearFilters"
              @refresh="handleRefresh"
              @new-ticket="handleNewTicket"
              @bulk-status="handleBulkStatus"
            />

            <div id="omega-summary" class="omega-summary" aria-live="polite"></div>

            <div class="omega-main__content">
              <div id="omega-dashboard" class="omega-dashboard" hidden aria-live="polite"></div>
              <OmegaTable
                :omega="omega"
                :filters="filters"
                :bulk="bulk"
                :search-query="searchQuery"
                @ticket-click="handleTicketClick"
                @select-all="handleSelectAll"
                @new-ticket="handleNewTicket"
              />
            </div>

          </section>
        </div>
      </section>
    </div>
    
    <OmegaDrawer
      :omega="omega"
      :open="drawerOpen"
      :initial-data="drawerInitialData"
      @update:open="drawerOpen = $event"
      @submit="handleNewTicketSubmit"
    />
    
    <OmegaFilterPanel
      :omega="omega"
      :filters="filters"
      :open="filters.filterPanelOpen.value"
      @update:open="filters.toggleFilterPanel($event)"
      @apply="handleFilterApply"
      @clear="handleFilterClear"
    />
  </Teleport>

  <Teleport to="body">
    <OmegaBulkPanel
      :omega="omega"
      :bulk="bulk"
      @close="bulk.bulkPanelOpen.value = false"
      @cancel="handleSelectAll(false)"
      @apply="handleBulkApply"
    />
  </Teleport>
</template>

<style scoped>
/* Garante que o modal sempre seja um overlay fixo, mesmo com omega-standalone */
:deep(#omega-modal) {
  position: fixed !important;
  inset: 0 !important;
  z-index: 2200 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 24px !important;
  pointer-events: auto !important;
}

:deep(#omega-modal[hidden]) {
  display: none !important;
  pointer-events: none !important;
}

/* Garante que o overlay seja visível */
:deep(#omega-modal .omega-modal__overlay) {
  display: block !important;
}

/* Estilos para o modo tela cheia */
:deep(#omega-modal.omega-modal--fullscreen) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  max-width: 100vw !important;
  max-height: 100vh !important;
  padding: 0 !important;
  margin: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}

:deep(#omega-modal.omega-modal--fullscreen .omega-modal__panel) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  max-width: 100vw !important;
  max-height: 100vh !important;
  margin: 0 !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}
</style>

