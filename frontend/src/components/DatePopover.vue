<template>
  <teleport to="body">
    <div
      v-if="open"
      ref="popover"
      class="date-popover"
      :style="{ top: `${coords.top}px`, left: `${coords.left}px` }"
    >
      <h4>Alterar data</h4>

      <div class="row" style="margin-bottom: 8px;">
        <input 
          type="date" 
          v-model="localStart" 
          aria-label="Data inicial"
        />
        <input 
          type="date" 
          v-model="localEnd" 
          aria-label="Data final"
        />
      </div>

      <div class="actions">
        <button class="btn-sec" @click="emit('close')">Cancelar</button>
        <button class="btn-pri" @click="save">Salvar</button>
      </div>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import type { Period } from '../types'

const props = defineProps<{
  open: boolean
  anchor: HTMLElement | null
  modelValue: Period
}>()

const emit = defineEmits<{
  close: []
  'update:modelValue': [value: Period]
}>()

const popover = ref<HTMLElement | null>(null)
const coords = ref({ top: 0, left: 0 })

const localStart = ref(props.modelValue.start)
const localEnd = ref(props.modelValue.end)

const positionPopover = () => {
  if (!props.anchor || !popover.value) return

  const r = props.anchor.getBoundingClientRect()
  const w = popover.value.offsetWidth || 340
  const h = popover.value.offsetHeight || 170

  let top = r.bottom + 8
  let left = r.right - w

  const pad = 12
  const vw = window.innerWidth
  const vh = window.innerHeight

  if (top + h + pad > vh) top = Math.max(pad, r.top - h - 8)
  if (left < pad) left = pad
  if (left + w + pad > vw) left = Math.max(pad, vw - w - pad)

  coords.value = { top, left }
}

watch(
  () => props.open,
  async (open) => {
    if (!open) {
      cleanup()
      return
    }

    localStart.value = props.modelValue.start
    localEnd.value = props.modelValue.end

    await nextTick()
    positionPopover()
    
    setTimeout(() => {
      document.addEventListener('click', handleOutside, { capture: false, once: true })
      document.addEventListener('keydown', handleEscape, { once: true })
    }, 100)
  }
)

watch(
  () => props.modelValue,
  (newValue) => {
    if (props.open) {
      localStart.value = newValue.start
      localEnd.value = newValue.end
    }
  },
  { deep: true }
)

const handleOutside = (e: MouseEvent) => {
  if (!popover.value || !props.open) return

  const target = e.target as HTMLElement
  
  const isInput = target instanceof HTMLInputElement
  if (
    popover.value.contains(target) ||
    props.anchor?.contains(target) ||
    (isInput && target.type === 'date')
  ) return

  cleanup()
  emit('close')
}

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.open) {
    cleanup()
    emit('close')
  }
}

const cleanup = () => {
  document.removeEventListener('click', handleOutside)
  document.removeEventListener('keydown', handleEscape)
}

onMounted(() => {
  window.addEventListener('resize', positionPopover)
})

onUnmounted(() => {
  cleanup()
  window.removeEventListener('resize', positionPopover)
})

const save = () => {
  if (!localStart.value || !localEnd.value) {
    alert('Período inválido.')
    return
  }

  if (new Date(localStart.value) > new Date(localEnd.value)) {
    alert('Data inicial não pode ser maior que a final.')
    return
  }

  emit('update:modelValue', {
    start: localStart.value,
    end: localEnd.value,
  })

  cleanup()
  emit('close')
}
</script>

<style scoped>
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
  visibility: visible;
}

.date-popover h4 {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text, #0f1424);
  font-family: var(--brad-font-family, inherit);
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
  font-family: var(--brad-font-family, inherit);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  background: var(--panel, #fff);
  color: var(--text, #0f1424);
}

.date-popover input[type="date"]:focus {
  outline: none;
  border-color: var(--brand, #cc092f);
  box-shadow: 0 0 0 3px var(--brand-xlight, rgba(204, 9, 47, 0.12));
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
  font-weight: var(--brad-font-weight-semibold, 600);
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: var(--brad-font-family, inherit);
  border: 1px solid var(--stroke, #e7eaf2);
}

.date-popover .btn-sec {
  background: var(--panel, #fff);
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

