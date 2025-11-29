<script setup lang="ts">
import { ref, computed, watch } from 'vue'

export interface ColumnMeta {
  id: string
  label: string
}

const DETAIL_COLUMNS: ColumnMeta[] = [
  { id: 'realizado', label: 'Realizado no período (R$)' },
  { id: 'meta', label: 'Meta no período (R$)' },
  { id: 'atingimento_p', label: 'Atingimento (%)' },
  { id: 'meta_diaria', label: 'Meta diária total (R$)' },
  { id: 'referencia_hoje', label: 'Referência para hoje (R$)' },
  { id: 'pontos', label: 'Pontos no período (pts)' },
  { id: 'meta_diaria_necessaria', label: 'Meta diária necessária (R$)' },
  { id: 'peso', label: 'Peso (pts)' },
  { id: 'projecao', label: 'Projeção (R$)' },
  { id: 'data', label: 'Data' }
]

const DEFAULT_COLUMNS = ['realizado', 'meta', 'atingimento_p', 'pontos', 'peso', 'data']

interface DetailView {
  id: string
  name: string
  columns: string[]
}

const props = defineProps<{
  modelValue: boolean
  selectedColumns: string[]
  views?: DetailView[]
  activeViewId?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'apply': [columns: string[]]
  'save': [name: string, columns: string[]]
  'load-view': [viewId: string]
  'delete-view': [viewId: string]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const selectedCols = ref<string[]>([...props.selectedColumns])
const viewName = ref('')
const feedback = ref('')

const availableColumns = computed(() => {
  return DETAIL_COLUMNS.filter(col => !selectedCols.value.includes(col.id))
})

const canRemove = computed(() => selectedCols.value.length > 1)

const handleAdd = (colId: string) => {
  if (!selectedCols.value.includes(colId)) {
    selectedCols.value.push(colId)
    feedback.value = ''
  }
}

const handleRemove = (colId: string) => {
  if (selectedCols.value.length <= 1) {
    feedback.value = 'Mantenha pelo menos uma coluna visível.'
    return
  }
  selectedCols.value = selectedCols.value.filter(id => id !== colId)
  feedback.value = ''
}

const handleApply = () => {
  if (selectedCols.value.length === 0) {
    feedback.value = 'Adicione ao menos uma coluna.'
    return
  }
  emit('apply', [...selectedCols.value])
  isOpen.value = false
  feedback.value = ''
}

const handleSave = () => {
  const name = viewName.value.trim()
  if (selectedCols.value.length === 0) {
    feedback.value = 'Adicione ao menos uma coluna antes de salvar.'
    return
  }
  if (name.length < 3) {
    feedback.value = 'Use um nome com pelo menos 3 caracteres.'
    return
  }
  emit('save', name, [...selectedCols.value])
  viewName.value = ''
  feedback.value = 'Visão salva com sucesso.'
}

const handleLoadView = (viewId: string) => {
  const view = props.views?.find(v => v.id === viewId)
  if (view) {
    selectedCols.value = [...view.columns]
    feedback.value = ''
  }
  emit('load-view', viewId)
}

const handleDeleteView = (viewId: string) => {
  if (viewId === 'default') return
  emit('delete-view', viewId)
}

const handleClose = () => {
  isOpen.value = false
  selectedCols.value = [...props.selectedColumns]
  viewName.value = ''
  feedback.value = ''
}

watch(() => props.selectedColumns, (newCols) => {
  selectedCols.value = [...newCols]
}, { immediate: true })

watch(() => props.modelValue, (open) => {
  if (open) {
    selectedCols.value = [...props.selectedColumns]
    viewName.value = ''
    feedback.value = ''
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="detail-designer"
      :class="{ 'is-open': isOpen }"
      @keydown.escape="handleClose"
    >
      <div class="detail-designer__overlay" @click="handleClose"></div>
      <div class="detail-designer__panel" role="dialog" aria-modal="true" aria-labelledby="detail-designer-title">
        <header class="detail-designer__head">
          <div>
            <h4 id="detail-designer-title">Personalizar colunas</h4>
            <p class="detail-designer__subtitle">
              Arraste as colunas para montar a visão da tabela e salve até 5 configurações preferidas. A coluna de ações continua fixa no final da grade.
            </p>
          </div>
          <button
            type="button"
            class="icon-btn detail-designer__close"
            aria-label="Fechar personalização"
            @click="handleClose"
          >
            <i class="ti ti-x"></i>
          </button>
        </header>

        <div v-if="feedback" class="detail-designer__feedback" role="alert">
          {{ feedback }}
        </div>

        <section v-if="props.views && props.views.length > 0" class="detail-designer__views">
          <div class="detail-designer__views-head">
            <span>VISÕES SALVAS</span>
            <span class="detail-designer__views-hint">Carregue para ajustar ou excluir.</span>
          </div>
          <div class="detail-designer__views-list">
            <button
              v-for="view in props.views"
              :key="view.id"
              type="button"
              class="detail-view-chip"
              :class="{ 'is-active': props.activeViewId === view.id }"
              @click="handleLoadView(view.id)"
            >
              {{ view.name }}
            </button>
          </div>
        </section>

        <section class="detail-designer__lists">
          <div class="detail-designer__list detail-designer__list--available">
            <div class="detail-designer__list-head">
              <h5>Colunas disponíveis</h5>
              <p>Arraste para incluir ou clique para adicionar.</p>
            </div>
            <div class="detail-designer__items">
              <div
                v-for="col in availableColumns"
                :key="col.id"
                class="detail-item detail-item--available"
              >
                <span class="detail-item__handle" aria-hidden="true">
                  <i class="ti ti-grip-vertical"></i>
                </span>
                <span class="detail-item__label">{{ col.label }}</span>
                <button
                  type="button"
                  class="detail-item__add"
                  :aria-label="`Adicionar ${col.label}`"
                  @click="handleAdd(col.id)"
                >
                  <i class="ti ti-plus"></i>
                </button>
              </div>
              <p v-if="availableColumns.length === 0" class="detail-designer__empty">
                Todas as colunas já estão na tabela.
              </p>
            </div>
          </div>

          <div class="detail-designer__list detail-designer__list--selected">
            <div class="detail-designer__list-head">
              <h5>Colunas na tabela</h5>
              <p>Arraste para reorganizar ou clique para remover.</p>
            </div>
            <div class="detail-designer__items">
              <div
                v-for="colId in selectedCols"
                :key="colId"
                class="detail-item"
                :class="{ 'is-disabled': !canRemove }"
              >
                <span class="detail-item__handle" aria-hidden="true">
                  <i class="ti ti-grip-vertical"></i>
                </span>
                <span class="detail-item__label">
                  {{ DETAIL_COLUMNS.find(c => c.id === colId)?.label || colId }}
                </span>
                <button
                  type="button"
                  class="detail-item__remove"
                  :class="{ 'is-disabled': !canRemove }"
                  :disabled="!canRemove"
                  :aria-label="`Remover ${DETAIL_COLUMNS.find(c => c.id === colId)?.label || colId}`"
                  @click="handleRemove(colId)"
                >
                  <i class="ti ti-x"></i>
                </button>
              </div>
              <p v-if="selectedCols.length === 0" class="detail-designer__empty">
                Escolha ao menos uma coluna.
              </p>
            </div>
          </div>
        </section>

        <footer class="detail-designer__foot">
          <div class="detail-designer__save">
            <label for="detail-view-name" class="detail-designer__save-label">
              SALVAR NOVA VISÃO
            </label>
            <div class="detail-designer__input-wrapper">
              <input
                id="detail-view-name"
                v-model="viewName"
                type="text"
                class="input input--sm detail-designer__input"
                maxlength="48"
                placeholder="Ex.: Indicadores priorizados"
                @keyup.enter="handleSave"
              />
              <button
                type="button"
                class="btn btn--primary detail-designer__save-btn"
                :disabled="!viewName.trim() || viewName.trim().length < 3"
                @click="handleSave"
              >
                Salvar visão
              </button>
            </div>
            <small class="detail-designer__save-hint">
              Você pode guardar até 5 visões personalizadas.
            </small>
          </div>
          <div class="detail-designer__actions">
            <button 
              type="button" 
              class="btn btn--secondary detail-designer__action-btn" 
              @click="handleClose"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn--primary detail-designer__action-btn"
              :disabled="selectedCols.length === 0"
              @click="handleApply"
            >
              Aplicar
            </button>
          </div>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.detail-designer {
  position: fixed;
  inset: 0;
  display: none;
  z-index: 10000;
  padding: clamp(16px, 6vh, 48px) clamp(12px, 6vw, 40px);
  overflow: auto;
  overscroll-behavior: contain;
}

.detail-designer.is-open {
  display: grid;
  align-items: center;
  justify-content: center;
  place-items: center;
}

.detail-designer__overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(3px);
}

.detail-designer__panel {
  position: relative;
  width: min(860px, 100%);
  max-height: min(90vh, 100%);
  overflow: hidden;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
  padding: 24px 28px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.detail-designer__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
}

.detail-designer__head h4 {
  margin: 0;
  font-size: 20px;
  color: #0f172a;
  font-weight: 800;
}

.detail-designer__subtitle {
  margin: 4px 0 0;
  font-size: 13px;
  color: #475569;
}

.detail-designer__close {
  background: #f1f5f9;
  color: #1f2937;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  display: grid;
  place-items: center;
  cursor: pointer;
}

.detail-designer__feedback {
  margin: 0;
  padding: 10px 12px;
  border-radius: 10px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  font-size: 12.5px;
  color: #1d4ed8;
}

.detail-designer__views {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.detail-designer__views-head {
  display: flex;
  align-items: baseline;
  gap: 10px;
  font-size: 12px;
  color: #475569;
}

.detail-designer__views-head span:first-child {
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #1f2937;
}

.detail-designer__views-hint {
  font-weight: 400;
  text-transform: none;
  letter-spacing: normal;
}

.detail-designer__views-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.detail-view-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid #c7d2fe;
  background: #fff;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.detail-view-chip:hover {
  background: #eef2ff;
  border-color: #93c5fd;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(15, 23, 41, 0.08);
}

.detail-view-chip.is-active {
  background: #eef2ff;
  border-color: #1d4ed8;
  color: #1d4ed8;
  box-shadow: 0 2px 8px rgba(29, 78, 216, 0.15);
}

.detail-designer__lists {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  flex: 1 1 auto;
  min-height: 0;
  align-items: stretch;
}

.detail-designer__list {
  flex: 1 1 360px;
  min-width: 280px;
  min-height: 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-designer__list-head h5 {
  margin: 0;
  font-size: 14px;
  color: #0f172a;
  font-weight: 800;
}

.detail-designer__list-head p {
  margin: 4px 0 0;
  font-size: 12px;
  color: #6b7280;
}

.detail-designer__items {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 120px;
  max-height: clamp(220px, 34vh, 360px);
  overflow: auto;
  padding-right: 4px;
  flex: 1 1 auto;
}

.detail-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  background: #fff;
  border: 1px solid #dbeafe;
  cursor: pointer;
  transition: box-shadow 0.15s ease, transform 0.15s ease;
}

.detail-item:hover {
  box-shadow: 0 4px 12px rgba(15, 23, 41, 0.08);
  transform: translateY(-1px);
}

.detail-item.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.detail-item__handle {
  color: #94a3b8;
  display: flex;
  align-items: center;
  font-size: 16px;
}

.detail-item__label {
  flex: 1 1 auto;
  font-weight: 700;
  color: #1f2937;
  font-size: 13px;
}

.detail-item__add,
.detail-item__remove {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid #dbeafe;
  background: #fff;
  color: #1d4ed8;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.detail-item__add:hover {
  background: #eef2ff;
  border-color: #c7d2fe;
}

.detail-item__remove {
  background: rgba(248, 113, 113, 0.12);
  color: #b91c1c;
  border-color: rgba(248, 113, 113, 0.32);
}

.detail-item__remove:hover:not(.is-disabled) {
  background: rgba(248, 113, 113, 0.2);
}

.detail-item__remove.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.detail-designer__empty {
  margin: 0;
  padding: 18px 12px;
  text-align: center;
  font-size: 12px;
  color: #6b7280;
  border: 1px dashed #cbd5f5;
  border-radius: 12px;
  background: #fff;
}

.detail-designer__foot {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  align-items: flex-end;
  justify-content: space-between;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

.detail-designer__save {
  flex: 1 1 auto;
  min-width: 0;
}

.detail-designer__save {
  flex: 1 1 360px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.detail-designer__save-label {
  font-size: 12px;
  font-weight: 800;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 2px;
}

.detail-designer__input-wrapper {
  display: flex;
  gap: 8px;
  align-items: center;
}

.detail-designer__input {
  flex: 1 1 auto;
  min-width: 0;
  padding: 8px 12px;
  border: 1px solid #e7eaf2;
  border-radius: 10px;
  font-size: 13px;
  transition: all 0.2s ease;
  box-sizing: border-box;
  background: #fff;
  color: #0f1424;
  font-family: inherit;
}

.detail-designer__input:focus {
  outline: none;
  border-color: #b30000;
  box-shadow: 0 0 0 3px rgba(179, 0, 0, 0.12);
}

.detail-designer__save-btn {
  flex: 0 0 auto;
  white-space: nowrap;
}

.detail-designer__save-hint {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
  margin-top: 2px;
}

.detail-designer__actions {
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: flex-end;
  flex-shrink: 0;
}

@media (max-width: 780px) {
  .detail-designer__panel {
    width: min(680px, 100%);
    padding: 20px;
    max-height: min(92vh, 100%);
  }

  .detail-designer__lists {
    flex-direction: column;
  }

  .detail-designer__list {
    min-width: 0;
  }

  .detail-designer__foot {
    flex-direction: column;
    align-items: stretch;
    gap: 20px;
  }

  .detail-designer__save {
    flex: 1 1 auto;
    width: 100%;
  }

  .detail-designer__input-wrapper {
    flex-direction: column;
  }

  .detail-designer__save-btn {
    width: 100%;
    min-width: 0;
  }

  .detail-designer__actions {
    justify-content: stretch;
    width: 100%;
  }

  .detail-designer__action-btn {
    flex: 1 1 auto;
    min-width: 0;
  }
}
</style>

