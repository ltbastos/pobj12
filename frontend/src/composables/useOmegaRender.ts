export function useOmegaRender(
  omega: any,
  filters: any,
  bulk: any
) {
  function renderUsers(root: HTMLElement) {
    const userSelect = root.querySelector('#omega-user-select') as HTMLSelectElement
    if (!userSelect) return

    const currentUsers = omega.users.value
    userSelect.innerHTML = ''

    currentUsers.forEach((user: any) => {
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

    navItems.forEach((item: any) => {
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

    // Popula select de departamento (apenas o do filtro)
    const departmentSelect = root.querySelector('#omega-filter-department') as HTMLSelectElement
    if (departmentSelect) {
      const currentValue = departmentSelect.value

      // Remove listeners antigos para evitar duplicação
      const newSelect = departmentSelect.cloneNode(true) as HTMLSelectElement
      departmentSelect.parentNode?.replaceChild(newSelect, departmentSelect)

      // Agrupa por departamento (usando Map para evitar duplicatas)
      const departments = new Map<string, string>()
      structure.forEach((item: any) => {
        if (item.departamento && !departments.has(item.departamento)) {
          departments.set(item.departamento, item.departamento_id || item.departamento)
        }
      })

      newSelect.innerHTML = '<option value="">Selecione...</option>'
      departments.forEach((id, name) => {
        const option = document.createElement('option')
        option.value = id
        option.textContent = name
        if (id === currentValue) {
          option.selected = true
        }
        newSelect.appendChild(option)
      })

      // Adiciona listener para atualizar tipos quando departamento mudar
      newSelect.addEventListener('change', () => {
        renderTypeSelect(root, newSelect.value)
      })
    }

    // Popula select de tipo de chamado
    const deptSelect = root.querySelector('#omega-filter-department') as HTMLSelectElement
    renderTypeSelect(root, deptSelect?.value || '')

    // Renderiza status como checkboxes
    renderStatusOptions(root)

    // Popula select de prioridade
    renderPrioritySelect(root)
  }

  function renderTypeSelect(root: HTMLElement, departmentId: string = '') {
    const structure = omega.structure.value
    if (!structure || structure.length === 0) return

    const typeSelect = root.querySelector('#omega-filter-type') as HTMLSelectElement
    if (!typeSelect) return

    const currentValue = typeSelect.value

    // Filtra tipos baseado no departamento selecionado
    const types = new Set<string>()
    structure.forEach((item: any) => {
      if (item.tipo) {
        // Se há departamento selecionado, filtra por ele
        if (departmentId) {
          const itemDeptId = item.departamento_id || item.departamento
          if (itemDeptId === departmentId) {
            types.add(item.tipo)
          }
        } else {
          // Se não há departamento selecionado, mostra todos os tipos
          types.add(item.tipo)
        }
      }
    })

    typeSelect.innerHTML = '<option value="">Todos os tipos</option>'
    Array.from(types).sort().forEach((tipo) => {
      const option = document.createElement('option')
      option.value = tipo
      option.textContent = tipo
      if (tipo === currentValue) {
        option.selected = true
      }
      typeSelect.appendChild(option)
    })
  }

  function renderStatusOptions(root: HTMLElement) {
    const statuses = omega.statuses.value
    if (!statuses || statuses.length === 0) return

    const statusHost = root.querySelector('#omega-filter-status')
    if (!statusHost) return

    const selectedStatuses = filters.filters.value.statuses || []

    statusHost.innerHTML = ''
    statuses.forEach((status: any) => {
      const option = document.createElement('label')
      option.className = 'omega-filter-status__option'
      const isChecked = selectedStatuses.includes(status.id)
      option.setAttribute('data-checked', isChecked ? 'true' : 'false')

      const checkbox = document.createElement('input')
      checkbox.type = 'checkbox'
      checkbox.value = status.id
      checkbox.checked = isChecked

      const span = document.createElement('span')
      span.textContent = status.label

      option.appendChild(checkbox)
      option.appendChild(span)

      // Adiciona listener para atualizar estado visual
      checkbox.addEventListener('change', () => {
        option.setAttribute('data-checked', checkbox.checked ? 'true' : 'false')
      })

      statusHost.appendChild(option)
    })
  }

  function renderPrioritySelect(root: HTMLElement) {
    const prioritySelect = root.querySelector('#omega-filter-priority') as HTMLSelectElement
    if (!prioritySelect) return

    const currentValue = prioritySelect.value
    const priorities = [
      { value: '', label: 'Todas' },
      { value: 'baixa', label: 'Baixa' },
      { value: 'media', label: 'Média' },
      { value: 'alta', label: 'Alta' },
      { value: 'critica', label: 'Crítica' }
    ]

    prioritySelect.innerHTML = ''
    priorities.forEach((priority) => {
      const option = document.createElement('option')
      option.value = priority.value
      option.textContent = priority.label
      if (priority.value === currentValue) {
        option.selected = true
      }
      prioritySelect.appendChild(option)
    })
  }

  function openTicketDetails(ticketId: string) {
    // TODO: Implementar abertura do modal de detalhes
  }

  function renderTickets(root: HTMLElement) {
    const tbody = root.querySelector('#omega-ticket-rows')
    if (!tbody) {
      return
    }

    let tickets = omega.tickets.value
    const statuses = omega.statuses.value
    const currentUser = omega.currentUser.value

    if (!tickets || tickets.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="omega-empty-state">
            <div class="omega-empty-state__content">
              <i class="ti ti-inbox" style="font-size: 64px; color: var(--brad-color-gray, #c7c7c7); margin-bottom: 20px; opacity: 0.6;"></i>
              <h3>Nenhum chamado encontrado</h3>
              <p>Não há chamados para exibir nesta visualização.</p>
              <button class="omega-btn omega-btn--primary" id="omega-new-ticket-empty" style="margin-top: 20px;">
                <i class="ti ti-plus"></i>
                <span>Registrar novo chamado</span>
              </button>
            </div>
          </td>
        </tr>
      `

      // Adiciona listener ao botão de novo ticket no estado vazio
      const newTicketBtn = tbody.querySelector('#omega-new-ticket-empty')
      if (newTicketBtn) {
        newTicketBtn.addEventListener('click', () => {
          const drawer = root.querySelector('#omega-drawer')
          if (drawer) {
            drawer.removeAttribute('hidden')
          }
        })
      }

      return
    }

    // Filtra tickets baseado na view atual (ANTES de aplicar filtros)
    let filteredTickets = tickets
    const currentView = omega.currentView.value

    if (currentView === 'my' && currentUser) {
      filteredTickets = tickets.filter((t: any) => t.requesterId === currentUser.id)
    } else if (currentView === 'assigned' && currentUser) {
      filteredTickets = tickets.filter((t: any) => t.ownerId === currentUser.id)
    } else if (currentView === 'queue' && currentUser) {
      filteredTickets = tickets.filter((t: any) =>
        currentUser.queues.includes(t.queue)
      )
    }

    // Aplica filtros avançados (DEPOIS de filtrar por view)
    filteredTickets = filters.applyFilters(filteredTickets)

    tbody.innerHTML = ''

    filteredTickets.forEach((ticket: any, index: number) => {
      const row = document.createElement('tr')
      row.className = 'omega-table__row'
      row.setAttribute('data-ticket-id', ticket.id)
      row.style.opacity = '0'
      row.style.transform = 'translateY(10px)'
      row.style.transition = `opacity 0.3s ease ${index * 0.03}s, transform 0.3s ease ${index * 0.03}s`

      const status = statuses.find((s: any) => s.id === ticket.status) || statuses[0] || { id: 'unknown', label: 'Desconhecido', tone: 'neutral' as const }
      const priorityMeta = omega.getPriorityMeta(ticket.priority)

      const openedDate = new Date(ticket.opened)
      const updatedDate = new Date(ticket.updated)

      const isSelected = bulk.selectedTicketIds.value.has(ticket.id)

      row.innerHTML = `
        <td class="col-select">
          <input type="checkbox" data-omega-select value="${ticket.id}" ${isSelected ? 'checked' : ''} aria-label="Selecionar chamado ${ticket.id}"/>
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

      // Adiciona listener para checkbox de seleção
      const checkbox = row.querySelector('input[data-omega-select]') as HTMLInputElement
      if (checkbox) {
        checkbox.addEventListener('click', (e) => {
          e.stopPropagation()
          if (checkbox.checked) {
            bulk.selectedTicketIds.value.add(ticket.id)
          } else {
            bulk.selectedTicketIds.value.delete(ticket.id)
          }
          bulk.renderBulkPanel(root)
        })
      }

      // Adiciona listener para abrir detalhes do ticket
      row.addEventListener('click', (e) => {
        if ((e.target as HTMLElement).tagName === 'INPUT' || (e.target as HTMLElement).closest('input')) {
          return
        }
        // Adiciona efeito de ripple
        const ripple = document.createElement('span')
        const rect = row.getBoundingClientRect()
        const x = (e as MouseEvent).clientX - rect.left
        const y = (e as MouseEvent).clientY - rect.top
        ripple.style.left = `${x}px`
        ripple.style.top = `${y}px`
        ripple.className = 'omega-ripple'
        row.appendChild(ripple)

        setTimeout(() => {
          ripple.remove()
          openTicketDetails(ticket.id)
        }, 300)
      })

      // Adiciona hover effect
      row.addEventListener('mouseenter', () => {
        row.style.transform = 'translateX(4px)'
      })
      row.addEventListener('mouseleave', () => {
        row.style.transform = 'translateX(0)'
      })

      tbody.appendChild(row)

      // Anima entrada
      setTimeout(() => {
        row.style.opacity = '1'
        row.style.transform = 'translateY(0)'
      }, 10)
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

  function renderOmegaData() {
    const modalElement = document.getElementById('omega-modal')
    if (!modalElement || modalElement.hidden) {
      console.warn('⚠️ Modal não encontrado ou está oculto')
      return
    }


    // Verifica se estamos usando componentes Vue (OmegaTable)
    const omegaTableComponent = modalElement.querySelector('.omega-table-container')
    const isUsingVueComponents = !!omegaTableComponent

    // Renderiza usuários no select (ainda necessário para o header)
    renderUsers(modalElement)

    // Renderiza tickets na tabela APENAS se não estiver usando componentes Vue
    if (!isUsingVueComponents) {
      renderTickets(modalElement)
    }

    // Renderiza perfil do usuário atual
    renderProfile(modalElement)

    // Renderiza navegação
    renderNavigation(modalElement)

    // Renderiza estrutura (departamentos e tipos) nos selects
    renderStructure(modalElement)

    // Renderiza select de prioridade
    renderPrioritySelect(modalElement)

    // Renderiza painel bulk APENAS se não estiver usando componentes Vue
    if (!isUsingVueComponents) {
      bulk.renderBulkPanel(modalElement)
    }

    // Atualiza estado do botão de filtros (se existir no DOM)
    const filterToggle = modalElement.querySelector('#omega-filters-toggle')
    if (filterToggle) {
      filterToggle.setAttribute('data-active', filters.hasActiveFilters() ? 'true' : 'false')
    }

  }

  return {
    renderUsers,
    renderProfile,
    renderNavigation,
    renderStructure,
    renderTickets,
    renderOmegaData,
    renderTypeSelect,
    renderStatusOptions,
    renderPrioritySelect,
    openTicketDetails
  }
}

