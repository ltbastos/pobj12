<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Teleport } from 'vue'
import SelectInput from './SelectInput.vue'
import SelectSearch from './SelectSearch.vue'
import type { FilterOption } from '../types'
import type { useOmega } from '../composables/useOmega'
import type { useOmegaFilters } from '../composables/useOmegaFilters'

interface Props {
  omega: ReturnType<typeof useOmega>
  filters: ReturnType<typeof useOmegaFilters>
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'apply': []
  'clear': []
}>()

const departmentOptions = computed<FilterOption[]>(() => {
  const structure = props.omega.structure.value
  if (!structure || structure.length === 0) return []
  
  const departments = new Map<string, string>()
  structure.forEach((item) => {
    if (item.departamento && !departments.has(item.departamento)) {
      departments.set(item.departamento, item.departamento_id || item.departamento)
    }
  })
  
  return Array.from(departments.entries()).map(([name, id]) => ({
    id,
    nome: name
  }))
})

const typeOptions = computed<FilterOption[]>(() => {
  const structure = props.omega.structure.value
  const selectedDepartment = props.filters.filters.value.departments[0] || ''
  
  if (!structure || structure.length === 0) return []
  
  const types = new Set<string>()
  structure.forEach((item) => {
    if (item.tipo) {
      if (selectedDepartment) {
        const itemDeptId = item.departamento_id || item.departamento
        if (itemDeptId === selectedDepartment) {
          types.add(item.tipo)
        }
      } else {
        types.add(item.tipo)
      }
    }
  })
  
  return Array.from(types).sort().map(tipo => ({
    id: tipo,
    nome: tipo
  }))
})

const priorityOptions: FilterOption[] = [
  { id: '', nome: 'Todas' },
  { id: 'baixa', nome: 'Baixa' },
  { id: 'media', nome: 'Média' },
  { id: 'alta', nome: 'Alta' },
  { id: 'critica', nome: 'Crítica' }
]

const statusOptions = computed<FilterOption[]>(() => {
  return props.omega.statuses.value.map(status => ({
    id: status.id,
    nome: status.label
  }))
})

const localFilters = ref({
  id: '',
  requester: '',
  department: '',
  type: '',
  priority: '',
  statuses: [] as string[],
  openedFrom: '',
  openedTo: ''
})

// Sincroniza com os filtros do composable
watch(() => props.filters.filters.value, (newFilters) => {
  localFilters.value = {
    id: newFilters.id || '',
    requester: newFilters.requester || '',
    department: newFilters.departments[0] || '',
    type: newFilters.type || '',
    priority: newFilters.priority || '',
    statuses: [...newFilters.statuses],
    openedFrom: newFilters.openedFrom || '',
    openedTo: newFilters.openedTo || ''
  }
}, { immediate: true })

// Quando o departamento muda, limpa o tipo
watch(() => localFilters.value.department, () => {
  localFilters.value.type = ''
})

function handleApply() {
  if (props.filters.applyFiltersFromFormValues) {
    props.filters.applyFiltersFromFormValues({
      id: localFilters.value.id,
      requester: localFilters.value.requester,
      type: localFilters.value.type,
      priority: localFilters.value.priority,
      statuses: localFilters.value.statuses,
      departments: localFilters.value.department ? [localFilters.value.department] : [],
      openedFrom: localFilters.value.openedFrom,
      openedTo: localFilters.value.openedTo
    })
  }
  emit('apply')
  emit('update:open', false)
}

function handleClear() {
  localFilters.value = {
    id: '',
    requester: '',
    department: '',
    type: '',
    priority: '',
    statuses: [],
    openedFrom: '',
    openedTo: ''
  }
  props.filters.resetFilters()
  emit('clear')
}

function handleClose() {
  // Restaura os valores locais para os filtros atuais antes de fechar
  const currentFilters = props.filters.filters.value
  localFilters.value = {
    id: currentFilters.id || '',
    requester: currentFilters.requester || '',
    department: currentFilters.departments[0] || '',
    type: currentFilters.type || '',
    priority: currentFilters.priority || '',
    statuses: [...currentFilters.statuses],
    openedFrom: currentFilters.openedFrom || '',
    openedTo: currentFilters.openedTo || ''
  }
  
  emit('update:open', false)
}

function toggleStatus(statusId: string) {
  const index = localFilters.value.statuses.indexOf(statusId)
  if (index > -1) {
    localFilters.value.statuses.splice(index, 1)
  } else {
    localFilters.value.statuses.push(statusId)
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      id="omega-filter-panel-wrapper"
      class="omega-filter-panel-wrapper"
    >
      <div
        class="omega-filter-panel__overlay"
        @click="handleClose"
      ></div>
      <div
        id="omega-filter-panel"
        class="omega-filter-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="omega-filter-panel-title"
      >
        <header class="omega-filter-panel__header">
          <h3 id="omega-filter-panel-title">Filtros</h3>
          <button
            class="omega-icon-btn"
            type="button"
            aria-label="Fechar filtros"
            @click="handleClose"
          >
            <i class="ti ti-x"></i>
          </button>
        </header>
        <form id="omega-filter-form" class="omega-filter-form" @submit.prevent="handleApply">
      <div class="omega-filter-form__grid">
        <label class="omega-field" for="omega-filter-id">
          <span class="omega-field__label">ID do chamado</span>
          <input
            id="omega-filter-id"
            v-model="localFilters.id"
            class="omega-input"
            type="text"
            placeholder="Ex.: OME-2025-0001"
            autocomplete="off"
          />
        </label>
        <label class="omega-field" for="omega-filter-user">
          <span class="omega-field__label">Usuário</span>
          <input
            id="omega-filter-user"
            v-model="localFilters.requester"
            class="omega-input"
            type="text"
            placeholder="Digite um nome"
            autocomplete="off"
          />
        </label>
        <label class="omega-field" for="omega-filter-department">
          <span class="omega-field__label">Departamento</span>
          <SelectSearch
            id="omega-filter-department"
            v-model="localFilters.department"
            :options="departmentOptions"
            placeholder="Selecione..."
            label="Departamento"
          />
          <small id="omega-filter-department-hint" class="omega-hint">Escolha a fila desejada</small>
        </label>
        <label class="omega-field" for="omega-filter-type">
          <span class="omega-field__label">Tipo de chamado</span>
          <SelectInput
            id="omega-filter-type"
            v-model="localFilters.type"
            :options="typeOptions"
            placeholder="Todos os tipos"
            label="Tipo de chamado"
          />
        </label>
        <label class="omega-field" for="omega-filter-priority">
          <span class="omega-field__label">Prioridade</span>
          <SelectInput
            id="omega-filter-priority"
            v-model="localFilters.priority"
            :options="priorityOptions"
            placeholder="Todas"
            label="Prioridade"
          />
        </label>
        <fieldset class="omega-field omega-filter-form__status" aria-describedby="omega-filter-status-hint">
          <span class="omega-field__label">Status do chamado</span>
          <div id="omega-filter-status" class="omega-filter-status">
            <label
              v-for="status in statusOptions"
              :key="status.id"
              class="omega-filter-status__option"
              :data-checked="localFilters.statuses.includes(status.id)"
            >
              <input
                type="checkbox"
                :value="status.id"
                :checked="localFilters.statuses.includes(status.id)"
                @change="toggleStatus(status.id)"
              />
              <span>{{ status.nome }}</span>
            </label>
          </div>
          <small id="omega-filter-status-hint" class="omega-hint">Selecione um ou mais status</small>
        </fieldset>
        <div class="omega-filter-form__dates">
          <label class="omega-field" for="omega-filter-from">
            <span class="omega-field__label">Abertura desde</span>
            <input
              id="omega-filter-from"
              v-model="localFilters.openedFrom"
              class="omega-input"
              type="date"
            />
          </label>
          <label class="omega-field" for="omega-filter-to">
            <span class="omega-field__label">Até</span>
            <input
              id="omega-filter-to"
              v-model="localFilters.openedTo"
              class="omega-input"
              type="date"
            />
          </label>
        </div>
      </div>
      <footer class="omega-filter-form__actions">
        <button
          id="omega-clear-filters"
          class="omega-btn omega-btn--ghost"
          type="button"
          @click="handleClear"
        >
          Limpar filtros
        </button>
        <button class="omega-btn omega-btn--primary" type="submit">
          <i class="ti ti-check"></i>
          <span>Aplicar</span>
        </button>
      </footer>
    </form>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.omega-filter-panel-wrapper {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  padding: 96px 40px 40px;
  pointer-events: none;
}

.omega-filter-panel__overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 20, 36, 0.15);
  backdrop-filter: blur(2px);
  pointer-events: auto;
}

.omega-filter-panel {
  position: relative;
  min-width: 360px;
  max-width: 520px;
  width: min(520px, calc(100vw - 80px));
  max-height: calc(100vh - 160px);
  background: #fff;
  border-radius: 20px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  box-shadow: 0 32px 70px rgba(15, 23, 42, 0.18);
  padding: 22px;
  overflow: visible;
  isolation: isolate;
  pointer-events: auto;
  display: flex;
  flex-direction: column;
}

.omega-filter-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.omega-filter-panel__header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: var(--text, #0f1424);
}

.omega-icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--muted, #6b7280);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.omega-icon-btn:hover {
  background: rgba(148, 163, 184, 0.12);
  color: var(--text, #0f1424);
}

.omega-filter-form {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  padding-right: 4px;
}

.omega-filter-form::-webkit-scrollbar {
  width: 6px;
}

.omega-filter-form::-webkit-scrollbar-track {
  background: transparent;
}

.omega-filter-form::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 3px;
}

.omega-filter-form::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.5);
}
</style>
