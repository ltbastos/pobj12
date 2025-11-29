<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { getCalendario, getDefaultPeriod, formatBRDate, type CalendarioItem } from '../services/calendarioService'

interface Props {
  modelValue?: { start: string; end: string }
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: undefined
})

const emit = defineEmits<{
  'update:modelValue': [value: { start: string; end: string }]
}>()

const period = ref<{ start: string; end: string }>(props.modelValue || getDefaultPeriod())
const calendarioData = ref<CalendarioItem[]>([])
const buttonRef = ref<HTMLElement | null>(null)
const datePopover = ref<HTMLElement | null>(null)

// Sincroniza com prop externa
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    period.value = { ...newValue }
  }
}, { deep: true })

const loadCalendario = async (): Promise<void> => {
  try {
    const data = await getCalendario()
    if (data) {
      calendarioData.value = data
    }
  } catch (error) {
    console.error('Erro ao carregar calendário:', error)
  }
}

onMounted(() => {
  loadCalendario()
  if (!props.modelValue) {
    const defaultPeriod = getDefaultPeriod()
    period.value = defaultPeriod
    emit('update:modelValue', defaultPeriod)
  }
})

const closeDatePopover = (): void => {
  if (datePopover.value?.parentNode) {
    datePopover.value.parentNode.removeChild(datePopover.value)
  }
  datePopover.value = null
}

const openDatePopover = (anchor: HTMLElement): void => {
  closeDatePopover()

  const pop = document.createElement('div')
  pop.className = 'date-popover'
  pop.id = 'date-popover'
  // Inicialmente posiciona fora da tela para calcular dimensões
  pop.style.position = 'fixed'
  pop.style.visibility = 'hidden'
  pop.style.top = '0'
  pop.style.left = '0'
  pop.innerHTML = `
    <h4>Alterar data</h4>
    <div class="row" style="margin-bottom:8px">
      <input id="inp-start" type="date" value="${period.value.start}" aria-label="Data inicial">
      <input id="inp-end"   type="date" value="${period.value.end}"   aria-label="Data final">
    </div>
    <div class="actions">
      <button type="button" class="btn-sec" id="btn-cancelar">Cancelar</button>
      <button type="button" class="btn-pri" id="btn-salvar">Salvar</button>
    </div>
  `
  document.body.appendChild(pop)

  // Usa requestAnimationFrame para garantir que o DOM foi atualizado
  requestAnimationFrame(() => {
    // Força o cálculo das dimensões reais
    const w = pop.offsetWidth || 340
    const h = pop.offsetHeight || 170

    // Agora calcula a posição correta
    const r = anchor.getBoundingClientRect()
    const pad = 12
    const vw = window.innerWidth
    const vh = window.innerHeight

    let top = r.bottom + 8
    let left = r.right - w
    if (top + h + pad > vh) top = Math.max(pad, r.top - h - 8)
    if (left < pad) left = pad
    if (left + w + pad > vw) left = Math.max(pad, vw - w - pad)

    // Aplica a posição e torna visível
    pop.style.top = `${top}px`
    pop.style.left = `${left}px`
    pop.style.visibility = 'visible'
  })

  pop.querySelector('#btn-cancelar')?.addEventListener('click', closeDatePopover)
  pop.querySelector('#btn-salvar')?.addEventListener('click', () => {
    const startInput = document.getElementById('inp-start') as HTMLInputElement
    const endInput = document.getElementById('inp-end') as HTMLInputElement
    const s = startInput?.value
    const e = endInput?.value

    if (!s || !e || new Date(s) > new Date(e)) {
      alert('Período inválido.')
      return
    }

    period.value = { start: s, end: e }
    emit('update:modelValue', { start: s, end: e })
    closeDatePopover()
  })

  const outside = (ev: MouseEvent): void => {
    const target = ev.target as HTMLElement
    if (target === pop || pop.contains(target) || target === anchor) return
    closeDatePopover()
  }

  const esc = (ev: KeyboardEvent): void => {
    if (ev.key === 'Escape') closeDatePopover()
  }

  document.addEventListener('mousedown', outside, { once: true })
  document.addEventListener('keydown', esc, { once: true })

  datePopover.value = pop
}

onUnmounted(() => {
  closeDatePopover()
})
</script>

<template>
  <div class="period-selector">
    <div class="period-inline">
      <span class="txt">
        De
        <strong>{{ formatBRDate(period.start) }}</strong>
        até
        <strong>{{ formatBRDate(period.end) }}</strong>
      </span>
      <button
        ref="buttonRef"
        id="btn-alterar-data"
        type="button"
        class="link-action"
        @click="buttonRef && openDatePopover(buttonRef)"
      >
        <i class="ti ti-chevron-down"></i> Alterar data
      </button>
    </div>
  </div>
</template>

<style scoped>
.period-selector {
  position: relative;
}

.period-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.period-inline .txt {
  font-size: 13px;
  color: var(--muted, #6b7280);
}

.period-inline strong {
  color: var(--text, #0f1424);
  font-weight: 700;
}

.link-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  color: var(--brand, #b30000);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.link-action:hover {
  background: rgba(204, 9, 47, 0.08);
}
</style>

<style>
.date-popover {
  position: fixed;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 18px 38px rgba(15, 20, 36, 0.18);
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 20px;
  z-index: 1500;
  min-width: 320px;
  max-width: 400px;
}

.date-popover h4 {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 700;
  color: var(--text, #0f1424);
}

.date-popover .row {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.date-popover input[type="date"] {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 10px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.date-popover input[type="date"]:focus {
  outline: none;
  border-color: var(--brand, #b30000);
  box-shadow: 0 0 0 3px rgba(204, 9, 47, 0.12);
}

.date-popover .actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.date-popover .btn-sec,
.date-popover .btn-pri {
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  border: 1px solid var(--stroke, #e7eaf2);
}

.date-popover .btn-sec {
  background: #fff;
  color: var(--text, #0f1424);
}

.date-popover .btn-sec:hover {
  background: rgba(0, 0, 0, 0.04);
}

.date-popover .btn-pri {
  background: linear-gradient(90deg, #cc092f 40%, #b81570 90%);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.25);
}

.date-popover .btn-pri:hover {
  background: linear-gradient(90deg, #b81570 40%, #cc092f 90%);
  box-shadow: 0 6px 16px rgba(204, 9, 47, 0.35);
  transform: translateY(-1px);
}
</style>
