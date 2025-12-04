<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import Icon from '../components/Icon.vue'
import { useOmega } from '../composables/useOmega'
import { useOmegaFilters } from '../composables/useOmegaFilters'
import { useOmegaBulk } from '../composables/useOmegaBulk'
import { useOmegaRender } from '../composables/useOmegaRender'
import OmegaFilterPanel from '../components/OmegaFilterPanel.vue'
import OmegaHeader from '../components/omega/OmegaHeader.vue'
import OmegaSidebar from '../components/omega/OmegaSidebar.vue'
import OmegaToolbar from '../components/omega/OmegaToolbar.vue'
import OmegaTable from '../components/omega/OmegaTable.vue'
import OmegaBulkPanel from '../components/omega/OmegaBulkPanel.vue'
import OmegaDrawer from '../components/omega/OmegaDrawer.vue'
import OmegaTicketModal from '../components/omega/OmegaTicketModal.vue'
import OmegaTeamManager from '../components/omega/OmegaTeamManager.vue'
import '../assets/omega.css'

const route = useRoute()

const omega = useOmega()
const filters = useOmegaFilters()
const bulk = useOmegaBulk(omega)
const render = useOmegaRender(omega, filters, bulk)

const pageRoot = ref<HTMLElement | null>(null)
const mainContentRef = ref<HTMLElement | null>(null)
const sidebarCollapsed = ref(false)
const searchQuery = ref('')

async function loadOmegaData() {
  
  const pageElement = pageRoot.value
  const container = pageElement?.querySelector('.omega-table-container')
  const isUsingVueComponents = !!container
  
  if (pageElement && !isUsingVueComponents) {
    showLoadingState(pageElement)
  }
  
  try {
    const data = await omega.loadInit()
    if (!data) {
      if (!isUsingVueComponents) {
        hideLoadingState(pageElement)
      }
      return
    }
    
    nextTick(() => {
      setTimeout(() => {
        if (!isUsingVueComponents) {
          hideLoadingState(pageElement)
          renderOmegaData()
        } else {
          
          isLoading.value = false
        }
      }, 100)
    })
  } catch (err) {
    if (!isUsingVueComponents) {
      hideLoadingState(pageElement)
      showErrorState(pageElement)
    }
  }
}

const isLoading = ref(false)

function showLoadingState(root: HTMLElement | null) {
  if (!root) return
  
  const container = root.querySelector('.omega-table-container')
  if (container) {
    
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
  
  const container = pageRoot.value?.querySelector('.omega-table-container')
  const isUsingVueComponents = !!container
  
  if (!isUsingVueComponents && pageRoot.value) {
    render.renderOmegaData()
  }
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

function escapeHTML(value: string): string {
  if (!value) return ''
  const div = document.createElement('div')
  div.textContent = value
  return div.textContent || ''
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

function handleFilterApply() {
  renderOmegaData()
}

function handleFilterClear() {
  renderOmegaData()
}

watch(() => omega.tickets.value, () => {
  nextTick(() => {
    const container = pageRoot.value?.querySelector('.omega-table-container')
    const isUsingVueComponents = !!container
    if (!isUsingVueComponents) {
      renderOmegaData()
    }
  })
}, { deep: true })

watch(() => omega.users.value, () => {
  nextTick(() => {
    const container = pageRoot.value?.querySelector('.omega-table-container')
    const isUsingVueComponents = !!container
    if (!isUsingVueComponents) {
      renderOmegaData()
    }
  })
}, { deep: true })

watch(() => omega.currentUserId.value, () => {
  nextTick(() => {
    const container = pageRoot.value?.querySelector('.omega-table-container')
    const isUsingVueComponents = !!container
    if (!isUsingVueComponents) {
      renderOmegaData()
    }
  })
})

watch(() => omega.currentView.value, () => {
  nextTick(() => {
    const container = pageRoot.value?.querySelector('.omega-table-container')
    const isUsingVueComponents = !!container
    if (!isUsingVueComponents) {
      renderOmegaData()
    }
  })
})

onMounted(() => {
  loadOmegaData()
  
  const params = new URLSearchParams(window.location.search)
  const openDrawer = params.get('openDrawer') === 'true' || params.get('intent') === 'new-ticket'
  const department = params.get('queue') || params.get('preferredQueue') || 'POBJ'
  const observation = params.get('observation') || ''
  
  if (openDrawer) {
    nextTick(() => {
      handleNewTicket({
        department,
        observation
      })
    })
  }
})

onBeforeUnmount(() => {
  
})
</script>

<template>
  <div
    ref="pageRoot"
    id="omega-page"
    class="omega-page"
    data-omega-standalone
  >
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
      :show-close="false"
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

        <div ref="mainContentRef" class="omega-main__content">
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
    
    <OmegaBulkPanel
      :omega="omega"
      :bulk="bulk"
      @close="bulk.bulkPanelOpen.value = false"
      @cancel="handleSelectAll(false)"
      @apply="handleBulkApply"
    />
  </div>
</template>

<style scoped>
.omega-page {
  min-height: calc(100vh - 66px);
  width: 100%;
  display: flex;
  flex-direction: column;
  background: var(--omega-bg, #f6f7fc);
}

.omega-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.omega-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.omega-main__content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}
</style>

