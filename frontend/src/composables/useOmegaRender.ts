export function useOmegaRender(
  omega: any,
  filters: any,
  bulk: any
) {
  function renderUsers(root: HTMLElement) {
    const userSelect = root.querySelector('#omega-user-select') as HTMLSelectElement
    if (!userSelect) return

    const currentUsers = omega.users.value
    
    while (userSelect.firstChild) {
      userSelect.removeChild(userSelect.firstChild)
    }

    currentUsers.forEach((user: any) => {
      const option = document.createElement('option')
      option.value = user.id
      option.textContent = user.name
      if (user.id === omega.currentUserId.value) {
        option.selected = true
      }
      userSelect.appendChild(option)
    })

    userSelect.onchange = (e) => {
      const target = e.target as HTMLSelectElement
      omega.setCurrentUserId(target.value)
      renderProfile(root)
      renderTickets(root)
    }
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

    while (navElement.firstChild) {
      navElement.removeChild(navElement.firstChild)
    }

    navItems.forEach((item: any) => {
      const navItem = document.createElement('button')
      navItem.className = 'omega-nav__item'
      navItem.type = 'button'
      navItem.setAttribute('data-omega-view', item.id)
      
      const icon = document.createElement('i')
      icon.className = item.icon
      const span = document.createElement('span')
      span.textContent = item.label
      navItem.appendChild(icon)
      navItem.appendChild(span)

      if (item.id === omega.currentView.value) {
        navItem.classList.add('omega-nav__item--active')
      }

      navItem.onclick = () => {
        omega.setCurrentView(item.id as any)
        renderTickets(root)
        
        navElement.querySelectorAll('.omega-nav__item').forEach((el) => {
          el.classList.remove('omega-nav__item--active')
        })
        navItem.classList.add('omega-nav__item--active')
      }

      navElement.appendChild(navItem)
    })
  }

  function renderStructure(root: HTMLElement) {
    const structure = omega.structure.value
    if (!structure || structure.length === 0) return

    const departmentSelect = root.querySelector('#omega-filter-department') as HTMLSelectElement
    if (departmentSelect) {
      const currentValue = departmentSelect.value

      const newSelect = departmentSelect.cloneNode(true) as HTMLSelectElement
      departmentSelect.parentNode?.replaceChild(newSelect, departmentSelect)

      const departments = new Map<string, string>()
      structure.forEach((item: any) => {
        if (item.departamento && !departments.has(item.departamento)) {
          departments.set(item.departamento, item.departamento_id || item.departamento)
        }
      })

      while (newSelect.firstChild) {
        newSelect.removeChild(newSelect.firstChild)
      }
      const defaultOption = document.createElement('option')
      defaultOption.value = ''
      defaultOption.textContent = 'Selecione...'
      newSelect.appendChild(defaultOption)
      
      departments.forEach((id, name) => {
        const option = document.createElement('option')
        option.value = id
        option.textContent = name
        if (id === currentValue) {
          option.selected = true
        }
        newSelect.appendChild(option)
      })

      newSelect.onchange = () => {
        renderTypeSelect(root, newSelect.value)
      }
    }

    const deptSelect = root.querySelector('#omega-filter-department') as HTMLSelectElement
    renderTypeSelect(root, deptSelect?.value || '')

    renderStatusOptions(root)

    renderPrioritySelect(root)
  }

  function renderTypeSelect(root: HTMLElement, departmentId: string = '') {
    const structure = omega.structure.value
    if (!structure || structure.length === 0) return

    const typeSelect = root.querySelector('#omega-filter-type') as HTMLSelectElement
    if (!typeSelect) return

    const currentValue = typeSelect.value

    const types = new Set<string>()
    structure.forEach((item: any) => {
      if (item.tipo) {
        
        if (departmentId) {
          const itemDeptId = item.departamento_id || item.departamento
          if (itemDeptId === departmentId) {
            types.add(item.tipo)
          }
        } else {
          
          types.add(item.tipo)
        }
      }
    })

    while (typeSelect.firstChild) {
      typeSelect.removeChild(typeSelect.firstChild)
    }
    const defaultOption = document.createElement('option')
    defaultOption.value = ''
    defaultOption.textContent = 'Todos os tipos'
    typeSelect.appendChild(defaultOption)
    
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

    while (statusHost.firstChild) {
      statusHost.removeChild(statusHost.firstChild)
    }
    
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

      checkbox.onchange = () => {
        option.setAttribute('data-checked', checkbox.checked ? 'true' : 'false')
      }

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

    while (prioritySelect.firstChild) {
      prioritySelect.removeChild(prioritySelect.firstChild)
    }
    
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
    
  }

  function renderTickets(root: HTMLElement) {
    const tbody = root.querySelector('#omega-ticket-rows')
    if (!tbody) {
      return
    }
    
    const container = root.querySelector('.omega-table-container')
    if (container) {
      
      return
    }

    let tickets = omega.tickets.value
    const statuses = omega.statuses.value
    const currentUser = omega.currentUser.value

    if (!tickets || tickets.length === 0) {
      
      while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild)
      }
      
      const row = document.createElement('tr')
      const cell = document.createElement('td')
      cell.colSpan = 12
      cell.className = 'omega-empty-state'
      
      const content = document.createElement('div')
      content.className = 'omega-empty-state__content'
      
      const icon = document.createElement('i')
      icon.className = 'ti ti-inbox'
      icon.style.cssText = 'font-size: 64px; color: var(--brad-color-gray, #c7c7c7); margin-bottom: 20px; opacity: 0.6;'
      
      const h3 = document.createElement('h3')
      h3.textContent = 'Nenhum chamado encontrado'
      
      const p = document.createElement('p')
      p.textContent = 'Não há chamados para exibir nesta visualização.'
      
      const btn = document.createElement('button')
      btn.className = 'omega-btn omega-btn--primary'
      btn.id = 'omega-new-ticket-empty'
      btn.style.cssText = 'margin-top: 20px;'
      
      const btnIcon = document.createElement('i')
      btnIcon.className = 'ti ti-plus'
      const btnSpan = document.createElement('span')
      btnSpan.textContent = 'Registrar novo chamado'
      btn.appendChild(btnIcon)
      btn.appendChild(btnSpan)
      
      btn.onclick = () => {
        const drawer = root.querySelector('#omega-drawer')
        if (drawer) {
          drawer.removeAttribute('hidden')
        }
      }
      
      content.appendChild(icon)
      content.appendChild(h3)
      content.appendChild(p)
      content.appendChild(btn)
      cell.appendChild(content)
      row.appendChild(cell)
      tbody.appendChild(row)

      return
    }

    let filteredTickets = tickets
    const currentView = omega.currentView.value

    if (currentView === 'my' && currentUser) {
      
      filteredTickets = tickets.filter((t: any) => t.requesterId === currentUser.id)
    } else if (currentView === 'assigned' && currentUser) {
      
      filteredTickets = tickets.filter((t: any) => t.ownerId === currentUser.id)
    } else if (currentView === 'queue' && currentUser) {
      
      if (currentUser.queues && currentUser.queues.length > 0) {
        filteredTickets = tickets.filter((t: any) =>
          currentUser.queues.includes(t.queue)
        )
      } else {
        
        if (currentUser.role !== 'admin') {
          filteredTickets = []
        }
      }
    }

    filteredTickets = filters.applyFilters(filteredTickets)

    while (tbody.firstChild) {
      tbody.removeChild(tbody.firstChild)
    }

    filteredTickets.forEach((ticket: any, index: number) => {
      const row = document.createElement('tr')
      row.className = 'omega-table__row'
      row.setAttribute('data-ticket-id', ticket.id)
      row.style.opacity = '0'
      row.style.transform = 'translateY(10px)'
      row.style.transition = `opacity 0.3s ease ${index * 0.03}s, transform 0.3s ease ${index * 0.03}s`

      const status = statuses.find((s: any) => s.id === ticket.status) || statuses[0] || { id: 'unknown', label: 'Desconhecido', tone: 'neutral' as const }
      const priorityMeta = omega.getPriorityMeta(ticket.priority)
      
      const canSelect = currentUser && ['analista', 'supervisor', 'admin'].includes(currentUser.role)
      const showPriority = currentUser && ['analista', 'supervisor', 'admin'].includes(currentUser.role)
      const showOwner = currentUser && ['analista', 'supervisor', 'admin'].includes(currentUser.role)
      const ownerName = ticket.ownerId 
        ? (omega.users.value.find((u: any) => u.id === ticket.ownerId)?.name || '—')
        : 'Sem responsável'

      const openedDate = new Date(ticket.opened)
      const updatedDate = new Date(ticket.updated)

      const isSelected = bulk.selectedTicketIds.value.has(ticket.id)

      const selectCell = canSelect ? `
        <td class="col-select">
          <input type="checkbox" data-omega-select value="${ticket.id}" ${isSelected ? 'checked' : ''} aria-label="Selecionar chamado ${ticket.id}"/>
        </td>
      ` : ''
      
      const priorityCell = showPriority ? `
        <td data-priority="${ticket.priority}">
          <span class="omega-badge omega-badge--${priorityMeta.tone}">
            <i class="${priorityMeta.icon}"></i>
            ${priorityMeta.label}
          </span>
        </td>
      ` : ''
      
      const ownerCell = showOwner ? `
        <td>${ownerName}</td>
      ` : ''

      row.innerHTML = `
        ${selectCell}
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
        ${priorityCell}
        ${ownerCell}
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

      row.addEventListener('click', (e) => {
        if ((e.target as HTMLElement).tagName === 'INPUT' || (e.target as HTMLElement).closest('input')) {
          return
        }
        
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

      row.addEventListener('mouseenter', () => {
        row.style.transform = 'translateX(4px)'
      })
      row.addEventListener('mouseleave', () => {
        row.style.transform = 'translateX(0)'
      })

      tbody.appendChild(row)

      setTimeout(() => {
        row.style.opacity = '1'
        row.style.transform = 'translateY(0)'
      }, 10)
    })

    const summary = root.querySelector('#omega-summary')
    if (summary) {
      summary.textContent = `${filteredTickets.length} chamado${filteredTickets.length !== 1 ? 's' : ''} encontrado${filteredTickets.length !== 1 ? 's' : ''}`
    }

    const tableFooter = root.querySelector('.omega-table-footer')
    if (tableFooter) {
      tableFooter.removeAttribute('hidden')
    }

    const tableRange = root.querySelector('#omega-table-range')
    if (tableRange && filteredTickets.length > 0) {
      tableRange.textContent = `1-${filteredTickets.length} de ${filteredTickets.length}`
    }
  }

  function renderOmegaData(rootRef?: HTMLElement | null) {
    
    let rootElement: HTMLElement | null = rootRef || null
    
    if (!rootElement) {
      rootElement = document.querySelector('#omega-page') as HTMLElement || 
                   document.querySelector('#omega-modal') as HTMLElement
    }
    
    const modalElement = rootElement?.id === 'omega-modal' ? rootElement : null
    if (!rootElement || (modalElement && modalElement.hidden)) {
      console.warn('⚠️ Modal não encontrado ou está oculto')
      return
    }

    const omegaTableComponent = rootElement.querySelector('.omega-table-container')
    const isUsingVueComponents = !!omegaTableComponent

    renderUsers(rootElement)

    if (!isUsingVueComponents) {
      renderTickets(rootElement)
    }

    renderProfile(rootElement)

    renderNavigation(rootElement)

    renderStructure(rootElement)

    renderPrioritySelect(rootElement)

    if (!isUsingVueComponents) {
      bulk.renderBulkPanel(rootElement)
    }

    const filterToggle = rootElement.querySelector('#omega-filters-toggle')
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
