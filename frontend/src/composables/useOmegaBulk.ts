import { ref } from 'vue'

export function useOmegaBulk(omega: any) {
  const selectedTicketIds = ref<Set<string>>(new Set())
  const bulkPanelOpen = ref(false)
  const bulkStatus = ref('')

  function populateBulkStatusOptions(root: HTMLElement) {
    const select = root.querySelector('#omega-bulk-status-select') as HTMLSelectElement
    if (!select) return

    const statuses = omega.statuses.value
    select.innerHTML = statuses.map((status: any) => {
      return `<option value="${status.id}">${status.label}</option>`
    }).join('')

    const current = bulkStatus.value && statuses.some((s: any) => s.id === bulkStatus.value)
      ? bulkStatus.value
      : (statuses[0]?.id || '')
    select.value = current
    bulkStatus.value = current
  }

  function handleBulkStatusSubmit(status: string) {
    const statuses = omega.statuses.value.map((s: any) => s.id)
    if (!status || !statuses.includes(status)) return

    const selection = Array.from(selectedTicketIds.value)
    if (!selection.length) return

    const currentUser = omega.currentUser.value
    if (!currentUser) return

    const now = new Date().toISOString()
    const statusMeta = omega.statuses.value.find((s: any) => s.id === status) || { label: status }

    // Atualiza tickets selecionados
    selection.forEach((id) => {
      const ticket = omega.tickets.value.find((item: any) => item.id === id)
      if (!ticket) return

      // Atualiza status do ticket
      ticket.status = status
      ticket.updated = now

      // Adiciona ao histórico (se houver função para isso)
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
    })

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
    const bulkBtn = root.querySelector('#omega-bulk-status')
    const bulkClose = root.querySelector('#omega-bulk-close')
    const bulkCancel = root.querySelector('#omega-bulk-cancel')
    const bulkStatusSelect = root.querySelector('#omega-bulk-status-select') as HTMLSelectElement
    const bulkForm = root.querySelector('#omega-bulk-form')

    bulkBtn?.addEventListener('click', () => {
      const currentUser = omega.currentUser.value
      if (!currentUser) return
      if (!selectedTicketIds.value.size) return
      bulkPanelOpen.value = !bulkPanelOpen.value
      onRender()
    })

    bulkClose?.addEventListener('click', () => {
      bulkPanelOpen.value = false
      onRender()
    })

    bulkCancel?.addEventListener('click', () => {
      bulkPanelOpen.value = false
      onRender()
    })

    bulkStatusSelect?.addEventListener('change', (ev) => {
      bulkStatus.value = (ev.target as HTMLSelectElement).value || ''
    })

    bulkForm?.addEventListener('submit', (ev) => {
      ev.preventDefault()
      const status = bulkStatusSelect?.value || ''
      handleBulkStatusSubmit(status)
      onRender()
    })
  }

  return {
    selectedTicketIds,
    bulkPanelOpen,
    bulkStatus,
    populateBulkStatusOptions,
    handleBulkStatusSubmit,
    renderBulkPanel,
    setupBulkControls
  }
}

