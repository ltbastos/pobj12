import { ref, type Ref } from 'vue'
import type { OmegaTicket } from '../types/omega'

export type OmegaFilters = {
  id: string
  requester: string
  type: string
  priority: string
  statuses: string[]
  departments: string[]
  openedFrom: string
  openedTo: string
}

export function useOmegaFilters() {
  const filters = ref<OmegaFilters>({
    id: '',
    requester: '',
    type: '',
    priority: '',
    statuses: [],
    departments: [],
    openedFrom: '',
    openedTo: ''
  })

  const filterPanelOpen = ref(false)

  function resetFilters() {
    filters.value = {
      id: '',
      requester: '',
      type: '',
      priority: '',
      statuses: [],
      departments: [],
      openedFrom: '',
      openedTo: ''
    }
  }

  function hasActiveFilters(): boolean {
    const f = filters.value
    if (f.id) return true
    if (f.requester) return true
    if (f.type) return true
    if (f.priority) return true
    if (f.statuses.length) return true
    if (f.openedFrom || f.openedTo) return true
    if (f.departments.length) return true
    return false
  }

  function applyFilters(tickets: OmegaTicket[]): OmegaTicket[] {
    let filtered = [...tickets]
    const f = filters.value

    if (f.id) {
      const idTerm = f.id.toLowerCase().trim()
      filtered = filtered.filter(t => t.id.toLowerCase().includes(idTerm))
    }

    if (f.departments.length) {
      filtered = filtered.filter(t => f.departments.includes(t.queue || ''))
    }

    if (f.type) {
      filtered = filtered.filter(t => t.category === f.type)
    }

    if (f.priority) {
      filtered = filtered.filter(t => t.priority === f.priority)
    }

    if (f.statuses.length) {
      filtered = filtered.filter(t => f.statuses.includes(t.status))
    }

    if (f.requester) {
      const requesterTerm = f.requester.toLowerCase().trim()
      filtered = filtered.filter(t => {
        const requesterName = t.requesterName?.toLowerCase() || ''
        return requesterName.includes(requesterTerm)
      })
    }

    if (f.openedFrom || f.openedTo) {
      filtered = filtered.filter(t => {
        if (!t.opened) return false
        const openedDate = new Date(t.opened)
        if (f.openedFrom) {
          const fromDate = new Date(f.openedFrom + 'T00:00:00')
          if (openedDate < fromDate) return false
        }
        if (f.openedTo) {
          const toDate = new Date(f.openedTo + 'T23:59:59')
          if (openedDate > toDate) return false
        }
        return true
      })
    }

    filtered.sort((a, b) => {
      const aTime = new Date(a.updated || a.opened || 0).getTime()
      const bTime = new Date(b.updated || b.opened || 0).getTime()
      return bTime - aTime
    })

    return filtered
  }

  function applyFiltersFromForm(root: HTMLElement) {
    const form = root.querySelector('#omega-filter-form')
    if (!form) return

    const id = (form.querySelector('#omega-filter-id') as HTMLInputElement)?.value?.trim() || ''
    const requester = (form.querySelector('#omega-filter-user') as HTMLInputElement)?.value?.trim() || ''
    const type = (form.querySelector('#omega-filter-type') as HTMLSelectElement)?.value || ''
    const priority = (form.querySelector('#omega-filter-priority') as HTMLSelectElement)?.value || ''
    const statuses = Array.from(form.querySelectorAll('#omega-filter-status input[type="checkbox"]:checked'))
      .map((input) => (input as HTMLInputElement).value)
      .filter(Boolean)
    const departmentValue = (form.querySelector('#omega-filter-department') as HTMLSelectElement)?.value || ''
    const departments = departmentValue ? [departmentValue] : []
    const openedFrom = (form.querySelector('#omega-filter-from') as HTMLInputElement)?.value || ''
    const openedTo = (form.querySelector('#omega-filter-to') as HTMLInputElement)?.value || ''

    filters.value = {
      id,
      requester,
      type,
      priority,
      statuses,
      departments,
      openedFrom,
      openedTo
    }

    filterPanelOpen.value = false
  }

  function applyFiltersFromFormValues(formValues: OmegaFilters) {
    filters.value = { ...formValues }
    filterPanelOpen.value = false
  }

  function syncFilterFormState(root: HTMLElement) {
    const form = root.querySelector('#omega-filter-form')
    if (!form) return

    const idInput = form.querySelector('#omega-filter-id') as HTMLInputElement
    if (idInput) idInput.value = filters.value.id || ''

    const userInput = form.querySelector('#omega-filter-user') as HTMLInputElement
    if (userInput) userInput.value = filters.value.requester || ''

    const typeSelect = form.querySelector('#omega-filter-type') as HTMLSelectElement
    if (typeSelect) typeSelect.value = filters.value.type || ''

    const prioritySelect = form.querySelector('#omega-filter-priority') as HTMLSelectElement
    if (prioritySelect) prioritySelect.value = filters.value.priority || ''

    const departmentSelect = form.querySelector('#omega-filter-department') as HTMLSelectElement
    if (departmentSelect) {
      departmentSelect.value = filters.value.departments[0] || ''
    }

    const fromInput = form.querySelector('#omega-filter-from') as HTMLInputElement
    if (fromInput) fromInput.value = filters.value.openedFrom || ''

    const toInput = form.querySelector('#omega-filter-to') as HTMLInputElement
    if (toInput) toInput.value = filters.value.openedTo || ''

    const statusHost = form.querySelector('#omega-filter-status')
    if (statusHost) {
      statusHost.querySelectorAll('.omega-filter-status__option').forEach((option) => {
        const input = option.querySelector('input[type="checkbox"]') as HTMLInputElement
        if (!input) return
        const checked = filters.value.statuses.includes(input.value)
        input.checked = checked
        option.setAttribute('data-checked', checked ? 'true' : 'false')
      })
    }
  }

  function toggleFilterPanel(force?: boolean) {
    filterPanelOpen.value = typeof force === 'boolean' ? force : !filterPanelOpen.value
  }

  return {
    filters,
    filterPanelOpen,
    resetFilters,
    hasActiveFilters,
    applyFilters,
    applyFiltersFromForm,
    applyFiltersFromFormValues,
    syncFilterFormState,
    toggleFilterPanel
  }
}
