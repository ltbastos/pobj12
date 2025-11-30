<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useOmega } from '../composables/useOmega'
import omegaTemplate from '../legacy/omega-template.html?raw'
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
const isOpen = ref(false)
const modalRoot = ref<HTMLElement | null>(null)
const omegaTemplateHtml = omegaTemplate

const addedBodyClasses = ['omega-standalone', 'has-omega-open']

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
  
  function tryUpdateVisibility() {
    const modalElement = document.getElementById('omega-modal')
    if (modalElement) {
      if (open) {
        modalElement.removeAttribute('hidden')
        modalElement.hidden = false
        return true
      } else {
        modalElement.setAttribute('hidden', '')
        modalElement.hidden = true
        return true
      }
    }
    return false
  }
  
  // Tenta atualizar imediatamente
  if (!tryUpdateVisibility()) {
    // Se não encontrou, tenta novamente no próximo tick
    nextTick(() => {
      if (!tryUpdateVisibility()) {
        // Se ainda não encontrou, tenta após um pequeno delay
        setTimeout(() => {
          tryUpdateVisibility()
        }, 100)
      }
    })
  }
}

watch(() => props.modelValue, (newValue) => {
  console.log('👀 props.modelValue mudou para:', newValue)
  updateModalVisibility(newValue)
  if (typeof window !== 'undefined') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const globalAny = window as any
    if (globalAny.__omegaModalState) {
      globalAny.__omegaModalState.isOpen.value = newValue
    }
  }
  if (newValue) {
    console.log('✅ Modal aberto, carregando dados...')
    ensureBodyState()
    loadOmegaData()
  } else {
    resetBodyState()
  }
}, { immediate: true })

// Observa mudanças nos dados e re-renderiza
watch(() => omega.tickets.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      const modalElement = document.getElementById('omega-modal')
      if (modalElement && !modalElement.hidden) {
        renderTickets(modalElement)
      }
    })
  }
}, { deep: true })

watch(() => omega.users.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      const modalElement = document.getElementById('omega-modal')
      if (modalElement && !modalElement.hidden) {
        renderUsers(modalElement)
        renderProfile(modalElement)
      }
    })
  }
}, { deep: true })

watch(() => omega.currentUserId.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      const modalElement = document.getElementById('omega-modal')
      if (modalElement && !modalElement.hidden) {
        renderProfile(modalElement)
        renderTickets(modalElement)
      }
    })
  }
})

watch(() => omega.currentView.value, () => {
  if (isOpen.value) {
    nextTick(() => {
      const modalElement = document.getElementById('omega-modal')
      if (modalElement && !modalElement.hidden) {
        renderTickets(modalElement)
      }
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
  console.log('🔄 Carregando dados do Omega...')
  try {
    const data = await omega.loadInit()
    console.log('✅ Dados do Omega carregados:', data)
    if (!data) {
      console.warn('⚠️ Nenhum dado retornado do Omega')
      return
    }
    
    // Aguarda o template HTML ser renderizado
    nextTick(() => {
      setTimeout(() => {
        renderOmegaData()
      }, 100)
    })
  } catch (err) {
    console.error('❌ Erro ao carregar dados do Omega:', err)
  }
}

function renderOmegaData() {
  const modalElement = document.getElementById('omega-modal')
  if (!modalElement || modalElement.hidden) {
    console.warn('⚠️ Modal não encontrado ou está oculto')
    return
  }

  console.log('🎨 Renderizando dados do Omega no template...')
  
  // Renderiza usuários no select
  renderUsers(modalElement)
  
  // Renderiza tickets na tabela
  renderTickets(modalElement)
  
  // Renderiza perfil do usuário atual
  renderProfile(modalElement)
  
  // Renderiza navegação
  renderNavigation(modalElement)
  
  // Renderiza estrutura (departamentos e tipos) nos selects
  renderStructure(modalElement)
  
  console.log('✅ Renderização concluída')
}

function renderUsers(root: HTMLElement) {
  const userSelect = root.querySelector('#omega-user-select') as HTMLSelectElement
  if (!userSelect) return

  const currentUsers = omega.users.value
  userSelect.innerHTML = ''
  
  currentUsers.forEach((user) => {
    const option = document.createElement('option')
    option.value = user.id
    option.textContent = user.name
    if (user.id === omega.currentUserId.value) {
      option.selected = true
    }
    userSelect.appendChild(option)
  })
  
  // Adiciona listener para mudança de usuário
  userSelect.addEventListener('change', (e) => {
    const target = e.target as HTMLSelectElement
    omega.setCurrentUserId(target.value)
    renderProfile(root)
    renderTickets(root)
  })
}

function renderProfile(root: HTMLElement) {
  const currentUser = omega.currentUser.value
  if (!currentUser) return

  const avatarImg = root.querySelector('#omega-avatar') as HTMLImageElement
  const userName = root.querySelector('#omega-user-name')
  
  if (avatarImg) {
    if (currentUser.avatar) {
      avatarImg.src = currentUser.avatar
      avatarImg.removeAttribute('hidden')
    } else {
      avatarImg.hidden = true
    }
  }
  
  if (userName) {
    userName.textContent = currentUser.name
  }
}

function renderNavigation(root: HTMLElement) {
  const navElement = root.querySelector('#omega-nav')
  if (!navElement) return

  const currentUser = omega.currentUser.value
  if (!currentUser) return

  const navItems = omega.getNavItemsForRole(currentUser.role)
  
  navElement.innerHTML = ''
  
  navItems.forEach((item) => {
    const navItem = document.createElement('button')
    navItem.className = 'omega-nav__item'
    navItem.type = 'button'
    navItem.setAttribute('data-omega-view', item.id)
    navItem.innerHTML = `
      <i class="${item.icon}"></i>
      <span>${item.label}</span>
    `
    
    if (item.id === omega.currentView.value) {
      navItem.classList.add('omega-nav__item--active')
    }
    
    navItem.addEventListener('click', () => {
      omega.setCurrentView(item.id as any)
      renderTickets(root)
      // Atualiza estado ativo
      navElement.querySelectorAll('.omega-nav__item').forEach((el) => {
        el.classList.remove('omega-nav__item--active')
      })
      navItem.classList.add('omega-nav__item--active')
    })
    
    navElement.appendChild(navItem)
  })
}

function renderStructure(root: HTMLElement) {
  const structure = omega.structure.value
  if (!structure || structure.length === 0) return

  // Popula selects de departamento
  const departmentSelects = root.querySelectorAll('[id*="department"], [id*="filter-department"]')
  departmentSelects.forEach((select) => {
    const selectEl = select as HTMLSelectElement
    const currentValue = selectEl.value
    
    // Agrupa por departamento
    const departments = new Map<string, string>()
    structure.forEach((item) => {
      if (!departments.has(item.departamento)) {
        departments.set(item.departamento, item.departamento_id || item.departamento)
      }
    })
    
    selectEl.innerHTML = '<option value="">Selecione...</option>'
    departments.forEach((id, name) => {
      const option = document.createElement('option')
      option.value = id
      option.textContent = name
      if (id === currentValue) {
        option.selected = true
      }
      selectEl.appendChild(option)
    })
  })
}

function renderTickets(root: HTMLElement) {
  const tbody = root.querySelector('#omega-ticket-rows')
  if (!tbody) {
    console.warn('⚠️ Tbody #omega-ticket-rows não encontrado')
    return
  }

  const tickets = omega.tickets.value
  const statuses = omega.statuses.value
  const currentUser = omega.currentUser.value
  
  if (!tickets || tickets.length === 0) {
    tbody.innerHTML = '<tr><td colspan="12" class="omega-table__empty">Nenhum chamado encontrado</td></tr>'
    return
  }

  // Filtra tickets baseado na view atual
  let filteredTickets = tickets
  const currentView = omega.currentView.value
  
  if (currentView === 'my' && currentUser) {
    filteredTickets = tickets.filter((t) => t.requesterId === currentUser.id)
  } else if (currentView === 'assigned' && currentUser) {
    filteredTickets = tickets.filter((t) => t.ownerId === currentUser.id)
  } else if (currentView === 'queue' && currentUser) {
    filteredTickets = tickets.filter((t) => 
      currentUser.queues.includes(t.queue)
    )
  }

  tbody.innerHTML = ''
  
  filteredTickets.forEach((ticket) => {
    const row = document.createElement('tr')
    row.className = 'omega-table__row'
    row.setAttribute('data-ticket-id', ticket.id)
    
    const status = statuses.find((s) => s.id === ticket.status) || statuses[0] || { id: 'unknown', label: 'Desconhecido', tone: 'neutral' as const }
    const priorityMeta = omega.getPriorityMeta(ticket.priority)
    
    const openedDate = new Date(ticket.opened)
    const updatedDate = new Date(ticket.updated)
    
    row.innerHTML = `
      <td class="col-select">
        <input type="checkbox" aria-label="Selecionar chamado ${ticket.id}"/>
      </td>
      <td>${ticket.id}</td>
      <td>
        <div class="omega-table__preview">
          <strong>${ticket.subject || 'Sem assunto'}</strong>
          <small>${ticket.requesterName}</small>
        </div>
      </td>
      <td>${ticket.queue || '—'}</td>
      <td>${ticket.category || '—'}</td>
      <td>${ticket.requesterName || '—'}</td>
      <td data-priority="${ticket.priority}">
        <span class="omega-badge omega-badge--${priorityMeta.tone}">
          <i class="${priorityMeta.icon}"></i>
          ${priorityMeta.label}
        </span>
      </td>
      <td>${ticket.product || '—'}</td>
      <td>${ticket.queue || '—'}</td>
      <td>${openedDate.toLocaleDateString('pt-BR')}</td>
      <td>${updatedDate.toLocaleDateString('pt-BR')}</td>
      <td class="col-status">
        <span class="omega-badge omega-badge--${status.tone}">
          ${status.label}
        </span>
      </td>
    `
    
    // Adiciona listener para abrir detalhes do ticket
    row.addEventListener('click', (e) => {
      if ((e.target as HTMLElement).tagName !== 'INPUT') {
        openTicketDetails(ticket.id)
      }
    })
    
    tbody.appendChild(row)
  })
  
  // Atualiza resumo
  const summary = root.querySelector('#omega-summary')
  if (summary) {
    summary.textContent = `${filteredTickets.length} chamado${filteredTickets.length !== 1 ? 's' : ''} encontrado${filteredTickets.length !== 1 ? 's' : ''}`
  }
  
  // Mostra footer da tabela
  const tableFooter = root.querySelector('.omega-table-footer')
  if (tableFooter) {
    tableFooter.removeAttribute('hidden')
  }
  
  const tableRange = root.querySelector('#omega-table-range')
  if (tableRange && filteredTickets.length > 0) {
    tableRange.textContent = `1-${filteredTickets.length} de ${filteredTickets.length}`
  }
}

function openTicketDetails(ticketId: string) {
  console.log('🔍 Abrindo detalhes do ticket:', ticketId)
  // TODO: Implementar abertura do modal de detalhes
}

function openModal() {
  console.log('🚀 Abrindo modal Omega...')
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
  
  console.log('🔧 Registrando funções globais do Omega')
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.__openOmegaFromVue = (detail?: any) => {
    console.log('🔓 __openOmegaFromVue chamado', detail)
    if (!omega.isLoading.value) {
      openModal()
    } else {
      console.warn('⚠️ Omega ainda está carregando.')
    }
  }
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.openOmegaModule = (detail?: any) => {
    console.log('🔓 openOmegaModule chamado', detail)
    openModal()
  }
  
  globalAny.openOmega = globalAny.openOmegaModule
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.closeOmega = () => {
    console.log('🔒 closeOmega chamado')
    closeModal()
  }
  
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  globalAny.closeOmegaModule = () => {
    console.log('🔒 closeOmegaModule chamado')
    closeModal()
  }
  
  console.log('✅ Funções globais registradas:', {
    __openOmegaFromVue: typeof globalAny.__openOmegaFromVue,
    openOmegaModule: typeof globalAny.openOmegaModule,
    openOmega: typeof globalAny.openOmega,
    closeOmega: typeof globalAny.closeOmega,
    closeOmegaModule: typeof globalAny.closeOmegaModule
  })
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

onMounted(() => {
  registerGlobalOpener()
  
  // Aguarda a renderização do template HTML
  nextTick(() => {
    // Aguarda um pouco mais para garantir que o v-html foi processado
    setTimeout(() => {
      const modalElement = document.getElementById('omega-modal')
      if (modalElement) {
        // Remove o atributo hidden inicial se o modal deve estar aberto
        if (props.modelValue || isOpen.value) {
          modalElement.removeAttribute('hidden')
          modalElement.hidden = false
        }
        
        // Adiciona listeners para fechar o modal
        const closeButtons = modalElement.querySelectorAll('[data-omega-close]')
        closeButtons.forEach((btn) => {
          btn.addEventListener('click', closeModal)
        })
        
        const overlay = modalElement.querySelector('.omega-modal__overlay')
        if (overlay) {
          overlay.addEventListener('click', closeModal)
        }
        
        // Adiciona listener para botão de refresh
        const refreshButton = modalElement.querySelector('#omega-refresh')
        if (refreshButton) {
          refreshButton.addEventListener('click', async () => {
            console.log('🔄 Atualizando dados do Omega...')
            await omega.loadInit()
            renderOmegaData()
          })
        }
        
        // Adiciona listener para botão de novo ticket
        const newTicketButton = modalElement.querySelector('#omega-new-ticket')
        if (newTicketButton) {
          newTicketButton.addEventListener('click', () => {
            console.log('➕ Abrindo formulário de novo ticket...')
            const drawer = modalElement.querySelector('#omega-drawer')
            if (drawer) {
              drawer.removeAttribute('hidden')
            }
          })
        }
      }
    }, 50)
  })
  
  if (props.modelValue) {
    openModal()
  }
})

onBeforeUnmount(() => {
  unregisterGlobalOpener()
  resetBodyState()
})
</script>

<template>
  <Teleport to="body">
    <div
      ref="modalRoot"
      class="omega-modal-wrapper"
      v-html="omegaTemplateHtml"
    ></div>
  </Teleport>
</template>

<style scoped>
.omega-modal-wrapper {
  position: fixed;
  inset: 0;
  z-index: 2200;
  pointer-events: none;
}

.omega-modal-wrapper :deep(#omega-modal) {
  position: fixed;
  inset: 0;
  z-index: 2200;
  pointer-events: auto;
}

.omega-modal-wrapper :deep(#omega-modal[hidden]) {
  display: none !important;
  pointer-events: none;
}
</style>

