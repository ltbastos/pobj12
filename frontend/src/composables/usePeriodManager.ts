import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { getDefaultPeriod, formatBRDate } from '../services/calendarioService'
import { useCalendarioCache } from './useCalendarioCache'
import type { Period } from '../types'

export function usePeriodManager() {
  const route = useRoute()
  const isSimuladoresPage = computed(() => route.name === 'Simuladores')
  const period = ref<Period>(getDefaultPeriod())
  const { calendarioData, loadCalendario } = useCalendarioCache()

  const updatePeriodLabels = (): void => {
    const labelElement = document.getElementById('lbl-atualizacao')
    if (!labelElement) return

    const startFormatted = formatBRDate(period.value.start)
    const endFormatted = formatBRDate(period.value.end)
    const isDisabled = isSimuladoresPage.value

    labelElement.innerHTML = `
      <div class="period-inline">
        <span class="txt">
          De
          <strong><span id="lbl-periodo-inicio">${startFormatted}</span></strong>
          até
          <strong><span id="lbl-periodo-fim">${endFormatted}</span></strong>
        </span>
        <button id="btn-alterar-data" type="button" class="link-action" style="color: #b30000;" ${isDisabled ? 'disabled' : ''}>
          <i class="ti ti-chevron-down"></i> Alterar data
        </button>
      </div>
    `

    // Re-bind do evento do botão apenas se não estiver desabilitado
    const btn = document.getElementById('btn-alterar-data') as HTMLButtonElement
    if (btn) {
      btn.disabled = isDisabled
      if (!isDisabled) {
        btn.removeEventListener('click', handleOpenDatePopover)
        btn.addEventListener('click', handleOpenDatePopover)
      } else {
        btn.removeEventListener('click', handleOpenDatePopover)
        btn.style.opacity = '0.5'
        btn.style.cursor = 'not-allowed'
      }
    }
  }

  const handleOpenDatePopover = (e: Event): void => {
    e.preventDefault()
    e.stopPropagation()
    openDatePopover(e.currentTarget as HTMLElement)
  }

  const closeDatePopover = (): void => {
    const existing = document.querySelector('#date-popover')
    if (existing) {
      existing.remove()
    }
  }

  const openDatePopover = (anchor: HTMLElement): void => {
    // Remove popover existente se houver
    closeDatePopover()

    const popover = document.createElement('div')
    popover.className = 'date-popover'
    popover.id = 'date-popover'
    popover.innerHTML = `
      <h4>Alterar data</h4>
      <div class="row" style="margin-bottom:8px">
        <input id="inp-start" type="date" value="${period.value.start}" aria-label="Data inicial">
        <input id="inp-end" type="date" value="${period.value.end}" aria-label="Data final">
      </div>
      <div class="actions">
        <button type="button" class="btn-sec" id="btn-cancel-period">Cancelar</button>
        <button type="button" class="btn-pri" id="btn-save-period">Salvar</button>
      </div>
    `

    document.body.appendChild(popover)

    // Posiciona relativo à viewport (o popover é FIXO)
    // Usa requestAnimationFrame para garantir que o elemento seja renderizado
    requestAnimationFrame(() => {
      const r = anchor.getBoundingClientRect()
      const w = popover.offsetWidth || 340
      const h = popover.offsetHeight || 170
      const pad = 12
      const vw = window.innerWidth
      const vh = window.innerHeight

      let top = r.bottom + 8
      let left = r.right - w
      
      // Se não cabe embaixo, coloca em cima
      if (top + h + pad > vh) {
        top = Math.max(pad, r.top - h - 8)
      }
      
      // Ajusta horizontalmente se necessário
      if (left < pad) left = pad
      if (left + w + pad > vw) {
        left = Math.max(pad, vw - w - pad)
      }

      popover.style.top = `${top}px`
      popover.style.left = `${left}px`
    })

    // Event listeners
    const startInput = popover.querySelector('#inp-start') as HTMLInputElement
    const endInput = popover.querySelector('#inp-end') as HTMLInputElement
    const cancelBtn = popover.querySelector('#btn-cancel-period')
    const saveBtn = popover.querySelector('#btn-save-period')

    const closePopover = (): void => {
      closeDatePopover()
      document.removeEventListener('click', handleClickOutside)
    }

    const handleClickOutside = (e: MouseEvent): void => {
      if (!popover.contains(e.target as Node) && !anchor.contains(e.target as Node)) {
        closePopover()
      }
    }

    const savePeriod = (): void => {
      const start = startInput.value
      const end = endInput.value

      if (!start || !end) {
        alert('Período inválido.')
        return
      }

      if (new Date(start) > new Date(end)) {
        alert('Data inicial não pode ser maior que data final.')
        return
      }

      period.value = { start, end }
      updatePeriodLabels()
      closePopover()
    }

    cancelBtn?.addEventListener('click', closePopover)
    saveBtn?.addEventListener('click', savePeriod)
    document.addEventListener('click', handleClickOutside, true)
  }

  onMounted(async () => {
    await loadCalendario()
    updatePeriodLabels()
  })

  // Observa mudanças na rota para atualizar o estado do botão
  watch(() => route.name, () => {
    updatePeriodLabels()
  })

  return {
    period,
    updatePeriodLabels,
    updatePeriod: (newPeriod: Period) => {
      period.value = newPeriod
      updatePeriodLabels()
    }
  }
}

