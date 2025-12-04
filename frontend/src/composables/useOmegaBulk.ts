import { ref, computed } from 'vue'
import { createOmegaNotification, createPobjNotification } from './useOmegaNotifications'

export function useOmegaBulk(omega: any) {
  const selectedTicketIds = ref<Set<string>>(new Set())
  const bulkPanelOpen = ref(false)
  const bulkStatus = ref('')

  const bulkStatusOptions = computed(() => {
    return omega.statuses.value.map((status: any) => ({
      value: status.id,
      label: status.label
    }))
  })

  function populateBulkStatusOptions(root: HTMLElement) {
    const select = root.querySelector('#omega-bulk-status-select') as HTMLSelectElement
    if (!select) return

    const statuses = omega.statuses.value
    
    while (select.firstChild) {
      select.removeChild(select.firstChild)
    }
    
    statuses.forEach((status: any) => {
      const option = document.createElement('option')
      option.value = status.id
      option.textContent = status.label
      select.appendChild(option)
    })

    const current = bulkStatus.value && statuses.some((s: any) => s.id === bulkStatus.value)
      ? bulkStatus.value
      : (statuses[0]?.id || '')
    select.value = current
    bulkStatus.value = current
  }

  async function handleBulkStatusSubmit(status: string) {
    const statuses = omega.statuses.value.map((s: any) => s.id)
    if (!status || !statuses.includes(status)) return

    const selection = Array.from(selectedTicketIds.value)
    if (!selection.length) return

    const currentUser = omega.currentUser.value
    if (!currentUser) return

    const now = new Date().toISOString()
    const statusMeta = omega.statuses.value.find((s: any) => s.id === status) || { label: status }

    for (const id of selection) {
      const ticket = omega.tickets.value.find((item: any) => item.id === id)
      if (!ticket) continue

      ticket.status = status
      ticket.updated = now

      if (Array.isArray(ticket.history)) {
        ticket.history.push({
          date: now,
          actorId: currentUser.id,
          action: `Status atualizado em lote para ${statusMeta.label}`,
          comment: '',
          status,
          attachments: []
        })
      }

      await createOmegaNotification(ticket, {
        date: now,
        actorId: currentUser.id,
        action: `Status atualizado em lote para ${statusMeta.label}`,
        comment: '',
        status
      })

      if (ticket.queue === 'pobj' || ticket.queue?.toLowerCase() === 'pobj') {
        await createPobjNotification(ticket, {
          date: now,
          actorId: currentUser.id,
          action: `Status atualizado em lote para ${statusMeta.label}`,
          comment: '',
          status
        })
      }

      try {
        await omega.updateTicket(id, { status, updated: now })
      } catch (err) {
        console.error(`Erro ao atualizar ticket ${id}:`, err)
      }
    }

    bulkPanelOpen.value = false
    selectedTicketIds.value.clear()
  }

  function renderBulkPanel(root: HTMLElement) {
    const panel = root.querySelector('#omega-bulk-panel')
    if (!panel) return

    const hasSelection = selectedTicketIds.value.size > 0

    if (panel) {
      (panel as HTMLElement).hidden = !bulkPanelOpen.value || !hasSelection
    }

    const bulkBtn = root.querySelector('#omega-bulk-status') as HTMLElement
    if (bulkBtn) {
      (bulkBtn as HTMLButtonElement).disabled = !hasSelection
    }

    if (bulkPanelOpen.value && hasSelection) {
      populateBulkStatusOptions(root)
      const hint = root.querySelector('#omega-bulk-hint')
      if (hint) {
        const count = selectedTicketIds.value.size
        hint.textContent = count === 1 ? '1 chamado selecionado' : `${count} chamados selecionados`
      }
    }
  }

  function setupBulkControls(root: HTMLElement, onRender: () => void) {
    
  }

  return {
    selectedTicketIds,
    bulkPanelOpen,
    bulkStatus,
    bulkStatusOptions,
    populateBulkStatusOptions,
    handleBulkStatusSubmit,
    renderBulkPanel,
    setupBulkControls
  }
}
