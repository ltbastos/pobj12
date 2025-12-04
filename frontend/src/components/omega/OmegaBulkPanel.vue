<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import SelectInput from '../SelectInput.vue'
import Icon from '../Icon.vue'
import type { FilterOption } from '../../types'
import type { useOmega } from '../../composables/useOmega'
import type { useOmegaBulk } from '../../composables/useOmegaBulk'

type Props = {
  omega: ReturnType<typeof useOmega>
  bulk: ReturnType<typeof useOmegaBulk>
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'close': []
  'cancel': []
  'apply': [status: string]
}>()

const selectedStatus = ref('')

const isOpen = computed(() => {
  return props.bulk.bulkPanelOpen.value && props.bulk.selectedTicketIds.value.size > 0
})

const selectedCount = computed(() => {
  return props.bulk.selectedTicketIds.value.size
})

const statusOptions = computed<FilterOption[]>(() => {
  return props.omega.statuses.value.map((status) => ({
    id: status.id,
    nome: status.label
  }))
})

const hintText = computed(() => {
  const count = selectedCount.value
  return count === 1 ? '1 chamado selecionado' : `${count} chamados selecionados`
})

function handleClose() {
  props.bulk.bulkPanelOpen.value = false
  emit('close')
}

function handleCancel() {
  props.bulk.bulkPanelOpen.value = false
  props.bulk.selectedTicketIds.value.clear()
  selectedStatus.value = ''
  emit('cancel')
}

function handleApply() {
  if (!selectedStatus.value) {
    return
  }
  emit('apply', selectedStatus.value)
}

watch(() => props.bulk.bulkPanelOpen.value, (open) => {
  if (!open) {
    selectedStatus.value = ''
  } else if (statusOptions.value.length > 0 && !selectedStatus.value) {
    selectedStatus.value = statusOptions.value[0]?.id || ''
  }
})

watch(isOpen, (open) => {
  if (open && statusOptions.value.length > 0 && !selectedStatus.value) {
    selectedStatus.value = statusOptions.value[0]?.id || ''
  }
}, { immediate: true })
</script>

<template>
  <div
    id="omega-bulk-panel"
    class="omega-bulk-panel"
    role="dialog"
    aria-modal="true"
    :hidden="!isOpen"
  >
    <form id="omega-bulk-form" class="omega-bulk-form" @submit.prevent="handleApply">
      <header class="omega-bulk-form__header">
        <strong>Alterar status</strong>
        <button
          type="button"
          class="omega-icon-btn"
          id="omega-bulk-close"
          aria-label="Fechar"
          @click="handleClose"
        >
          <Icon name="x" :size="20" />
        </button>
      </header>
      <p id="omega-bulk-hint" class="omega-bulk-form__hint">{{ hintText }}</p>
      <label class="omega-field" for="omega-bulk-status-select">
        <span class="omega-field__label">Novo status</span>
        <SelectInput
          id="omega-bulk-status-select"
          v-model="selectedStatus"
          :options="statusOptions"
          placeholder="Selecione um status"
          label="Novo status"
          required
        />
      </label>
      <footer class="omega-bulk-form__actions">
        <button
          type="button"
          class="omega-btn omega-btn--ghost"
          id="omega-bulk-cancel"
          @click="handleCancel"
        >
          Cancelar
        </button>
        <button
          type="submit"
          class="omega-btn omega-btn--primary"
          :disabled="!selectedStatus"
        >
          <Icon name="check" :size="18" />
          <span>Aplicar</span>
        </button>
      </footer>
    </form>
  </div>
</template>

<style scoped>
.omega-bulk-panel {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  min-width: 400px;
  max-width: 500px;
  background: #fff;
  border-radius: 20px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  box-shadow: 0 32px 70px rgba(15, 23, 42, 0.18);
  z-index: 10000;
}

.omega-bulk-panel[hidden] {
  display: none;
}

.omega-bulk-form {
  padding: 24px;
}

.omega-bulk-form__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.omega-bulk-form__header strong {
  font-size: 18px;
  font-weight: 700;
  color: var(--text, #0f1424);
}

.omega-icon-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  border-radius: 8px;
  color: var(--muted, #6b7280);
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-icon-btn:hover {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  color: var(--brad-color-primary, #cc092f);
}

.omega-bulk-form__hint {
  margin: 0 0 20px;
  font-size: 14px;
  color: var(--muted, #6b7280);
}

.omega-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
}

.omega-field__label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text, #0f1424);
}

.omega-bulk-form__actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid rgba(148, 163, 184, 0.2);
}

.omega-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: 1px solid transparent;
  border-radius: 10px;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-btn--ghost {
  background: transparent;
  border-color: var(--stroke, #e7eaf2);
  color: var(--text, #0f1424);
}

.omega-btn--ghost:hover {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  border-color: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
}

.omega-btn--primary {
  background: var(--brad-color-primary, #cc092f);
  border-color: var(--brad-color-primary, #cc092f);
  color: #fff;
}

.omega-btn--primary:hover:not(:disabled) {
  background: var(--brad-color-primary-dark, #a00725);
  border-color: var(--brad-color-primary-dark, #a00725);
}

.omega-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>

