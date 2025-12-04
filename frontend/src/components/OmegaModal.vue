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
import OmegaTicketModal from './omega/OmegaTicketModal.vue'
import OmegaTeamManager from './omega/OmegaTeamManager.vue'
import '../assets/omega.css'

type Props = {
  modelValue?: boolean
}

type Emits = {
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

watch(() => omega.tickets.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      
      renderOmegaData()
    })
  }
}, { deep: true })

watch(() => omega.users.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      
      renderOmegaData()
    })
  }
}, { deep: true })

watch(() => omega.currentUserId.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      
      renderOmegaData()
    })
  }
})

watch(() => omega.currentView.value, () => {
  if (isOpen.value) {
    
    nextTick(() => {
      renderOmegaData()
    })
  }
})

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

const isLoading = ref(false)

function showLoadingState(root: HTMLElement | null) {
  if (!root) return
  
  const omegaTableComponent = root.querySelector('.omega-table-container')
  const isUsingVueComponents = !!omegaTableComponent
  
  if (isUsingVueComponents) {
    return
  }
  
  isLoading.value = true
  const mainContent = root.querySelector('.omega-main__content')
  if (mainContent) {
    mainContent.classList.add('omega-loading')
  }
}

function hideLoadingState(root: HTMLElement | null) {
  if (!root) return
  
  const mainContent = root.querySelector('.omega-main__content') || mainContentRef.value?.querySelector('.omega-main__content')
  if (mainContent) {
    mainContent.classList.remove('omega-loading')
  }
}

const hasError = ref(false)

function showErrorState(root: HTMLElement | null) {
  if (!root) return
  hasError.value = true
  isLoading.value = false
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
  
}

function handleNavClick(viewId: string) {
  
  if (viewId === 'team-edit-analyst') {
    teamManagerOpen.value = true
    return
  }
  
  omega.setCurrentView(viewId as any)
  
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
  omega.loadInit().then(() => {
    renderOmegaData()
  })
}

const drawerOpen = ref(false)
const drawerInitialData = ref<{ department?: string; type?: string; observation?: string } | undefined>(undefined)
const ticketModalOpen = ref(false)
const selectedTicketId = ref<string | null>(null)
const teamManagerOpen = ref(false)

function handleNewTicket(initialData?: { department?: string; type?: string; observation?: string }) {
  drawerInitialData.value = initialData
  drawerOpen.value = true
}

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

function showFormFeedback(root: HTMLElement, message: string, tone: 'info' | 'warning' | 'danger' | 'success' = 'info') {
  const feedback = root.querySelector('#omega-form-feedback') as HTMLElement
  if (!feedback) return
  feedback.textContent = message
  feedback.hidden = false
  feedback.className = `omega-feedback omega-feedback--${tone}`
}

function clearFormFeedback(root: HTMLElement) {
  const feedback = root.querySelector('#omega-form-feedback') as HTMLElement
  if (!feedback) return
  feedback.hidden = true
  feedback.textContent = ''
  feedback.className = 'omega-feedback'
}

type Toast = {
  id: string
  message: string
  tone: 'success' | 'info' | 'warning' | 'danger'
  visible: boolean
}

const toasts = ref<Toast[]>([])

function showOmegaToast(message: string, tone: 'success' | 'info' | 'warning' | 'danger' = 'success') {
  if (!message) return
  
  const id = `toast-${Date.now()}-${Math.random()}`
  const toast: Toast = {
    id,
    message,
    tone,
    visible: false
  }
  
  toasts.value.push(toast)
  
  if (toasts.value.length > 3) {
    toasts.value.shift()
  }
  
  nextTick(() => {
    toast.visible = true
  })
  
  const lifetime = 3600
  setTimeout(() => {
    toast.visible = false
    setTimeout(() => {
      const index = toasts.value.findIndex(t => t.id === id)
      if (index > -1) {
        toasts.value.splice(index, 1)
      }
    }, 220)
  }, lifetime)
}

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

function isValidEmail(value: string): boolean {
  if (!value) return false
  const normalized = value.toString().trim()
  if (!normalized) return false
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)
}

async function handleNewTicketSubmit(data: any) {
  const user = omega.currentUser.value
  if (!user) {
    showOmegaToast('Usuário não encontrado.', 'danger')
    return
  }
  
  const requesterName = user.name?.trim() || ''
  
  const subjectParts: string[] = []
  if (data.type) subjectParts.push(data.type)
  if (requesterName) subjectParts.push(requesterName)
  const subject = subjectParts.length ? subjectParts.join(' • ') : 'Chamado Omega'
  
  try {
    const now = new Date().toISOString()
    
    const isMatriz = data.department === 'Matriz'
    const ownerId = isMatriz 
      ? null  
      : (['analista', 'supervisor', 'admin'].includes(user.role) ? user.id : null)
    
    const newTicket = {
      subject,
      company: requesterName,
      requesterName,
      productId: '',
      product: data.type || '',
      family: '',
      section: '',
      queue: data.department || 'Matriz',  
      status: 'aberto',
      category: data.type,
      priority: 'media' as const,
      opened: now,
      updated: now,
      dueDate: null,
      requesterId: user.id,
      ownerId: ownerId,
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
    
    const { createOmegaTicket } = await import('../services/omegaService')
    const response = await createOmegaTicket(newTicket)
    
    if (response.success && response.data) {
      
      await omega.loadInit()
      
      drawerOpen.value = false
      
      showOmegaToast('Chamado registrado com sucesso.', 'success')
      
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
  selectedTicketId.value = ticketId
  ticketModalOpen.value = true
}

function handleSelectAll(checked: boolean) {
  if (checked) {
    
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
  
}

function refreshTicketList(button: HTMLElement) {
  setButtonLoading(button, true)
  
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

function handleFilterApply() {
  renderOmegaData() 
}

function handleFilterClear() {
  renderOmegaData() 
}

function getAvailableDepartmentsForUser(user: any): string[] {
  if (!user) return []
  
  const departments = new Map<string, string>()
  omega.structure.value.forEach((item) => {
    if (item.departamento && !departments.has(item.departamento)) {
      departments.set(item.departamento, item.departamento_id || item.departamento)
    }
  })
  
  const allDepartments = Array.from(departments.keys())
  
  if (user.role === 'admin') return allDepartments
  
  if (user.role === 'usuario') {
    return user.matrixAccess ? allDepartments : allDepartments.filter((d) => d !== 'Matriz')
  }
  
  return allDepartments
}

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

function escapeHTML(value: string): string {
  if (!value) return ''
  const div = document.createElement('div')
  div.textContent = value
  return div.textContent || ''
}

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
  
  nextTick(() => {
    updateModalVisibility(true)
    
    nextTick(() => {
      applySidebarState()
      
      if (omega.initData.value && omega.tickets.value.length > 0) {
        setTimeout(() => {
          renderOmegaData()
        }, 100)
      } else {
        
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

function renderMainContent() {
  
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
        <div class="omega-toast-stack" aria-live="polite" aria-atomic="true">
          <TransitionGroup name="toast" tag="div">
            <div
              v-for="toast in toasts"
              :key="toast.id"
              :class="['omega-toast', `omega-toast--${toast.tone}`]"
              :data-visible="toast.visible"
              role="status"
            >
              <i :class="{
                'ti ti-check': toast.tone === 'success',
                'ti ti-info-circle': toast.tone === 'info',
                'ti ti-alert-triangle': toast.tone === 'warning',
                'ti ti-alert-circle': toast.tone === 'danger'
              }" aria-hidden="true"></i>
              <span>{{ toast.message }}</span>
            </div>
          </TransitionGroup>
        </div>
        
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
    
    <OmegaTicketModal
      :omega="omega"
      :open="ticketModalOpen"
      :ticket-id="selectedTicketId"
      @update:open="ticketModalOpen = $event"
    />
    
    <OmegaTeamManager
      :omega="omega"
      :open="teamManagerOpen"
      @update:open="teamManagerOpen = $event"
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


:deep(#omega-modal .omega-modal__overlay) {
  display: block !important;
}


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

