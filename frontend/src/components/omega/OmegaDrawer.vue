<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { Teleport } from 'vue'
import SelectInput from '../SelectInput.vue'
import type { FilterOption } from '../../types'
import type { useOmega } from '../../composables/useOmega'

interface Props {
  omega: ReturnType<typeof useOmega>
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:open': [open: boolean]
  'submit': [data: NewTicketData]
}>()

interface NewTicketData {
  department: string
  type: string
  observation: string
  attachments: Attachment[]
  flow?: {
    requesterName: string
    requesterEmail: string
    targetName: string
    targetEmail: string
  }
}

interface Attachment {
  id: string
  name: string
  size: number | null
  file: File
}

const department = ref<string | null>(null)
const type = ref<string | null>(null)
const observation = ref('')
const attachments = ref<Attachment[]>([])
const feedback = ref({ message: '', tone: 'info' as 'info' | 'warning' | 'danger' | 'success', visible: false })

// Fluxo de aprovação (para transferências)
const showFlow = ref(false)
const flowRequesterName = ref('')
const flowRequesterEmail = ref('')
const flowTargetName = ref('')
const flowTargetEmail = ref('')

const currentUser = computed(() => props.omega.currentUser.value)
const requesterName = computed(() => currentUser.value?.name || '—')

// Opções de departamento
const departmentOptions = computed<FilterOption[]>(() => {
  const user = currentUser.value
  if (!user) return []
  
  const departments = new Map<string, string>()
  props.omega.structure.value.forEach((item) => {
    if (item.departamento && !departments.has(item.departamento)) {
      departments.set(item.departamento, item.departamento_id || item.departamento)
    }
  })
  
  const allDepartments = Array.from(departments.keys())
  
  if (user.role === 'admin') return allDepartments.map(d => ({ id: d, nome: d }))
  if (user.role === 'usuario') {
    const filtered = user.matrixAccess ? allDepartments : allDepartments.filter((d) => d !== 'Matriz')
    return filtered.map(d => ({ id: d, nome: d }))
  }
  
  return allDepartments.map(d => ({ id: d, nome: d }))
})

// Opções de tipo baseadas no departamento selecionado
const typeOptions = computed<FilterOption[]>(() => {
  if (!department.value) return []
  
  const types = new Set<string>()
  props.omega.structure.value.forEach((item) => {
    if (item.departamento === department.value && item.tipo) {
      types.add(item.tipo)
    }
  })
  
  return Array.from(types).sort().map(t => ({ id: t, nome: t }))
})

// Verifica se precisa mostrar fluxo de aprovação
watch([department, type], () => {
  const typeValue = type.value || ''
  const normalizedType = typeValue.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  const transferType = 'transferencia - empresas para empresas'.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  showFlow.value = normalizedType === transferType
}, { immediate: true })

// Validação de email
function isValidEmail(value: string): boolean {
  if (!value) return false
  const normalized = value.toString().trim()
  if (!normalized) return false
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)
}

// Gerenciamento de anexos
const fileInputRef = ref<HTMLInputElement | null>(null)

function handleAddFile() {
  fileInputRef.value?.click()
}

function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement
  if (!input.files || input.files.length === 0) return
  
  const newFiles = Array.from(input.files).map((file) => ({
    id: `att-${Date.now()}-${Math.random().toString(36).slice(2)}`,
    name: file.name || 'Arquivo sem nome',
    size: file.size,
    file
  }))
  
  attachments.value = [...attachments.value, ...newFiles]
  
  // Limpa o input
  if (input) {
    try {
      input.value = ''
    } catch (err) {
      // Ignora erros
    }
  }
}

function handleRemoveAttachment(id: string) {
  attachments.value = attachments.value.filter(att => att.id !== id)
}

function formatFileSize(bytes: number | null): string {
  if (!bytes || bytes <= 0) return ''
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex += 1
  }
  return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`
}

// Submit do formulário
function handleSubmit(event: Event) {
  event.preventDefault()
  
  // Validação
  if (!department.value || !type.value || !observation.value.trim()) {
    showFeedback('Preencha todos os campos obrigatórios para registrar o chamado.', 'warning')
    return
  }
  
  // Validação de fluxo se necessário
  if (showFlow.value) {
    if (!flowRequesterName.value.trim() || !flowRequesterEmail.value.trim()) {
      showFeedback('Informe o nome e o e-mail do gerente da agência solicitante para concluir a transferência.', 'warning')
      return
    }
    if (!isValidEmail(flowRequesterEmail.value)) {
      showFeedback('Informe um e-mail corporativo válido para o gerente da agência solicitante.', 'warning')
      return
    }
    if (!flowTargetName.value.trim() || !flowTargetEmail.value.trim()) {
      showFeedback('Informe o nome e o e-mail do gerente da agência cedente para concluir a transferência.', 'warning')
      return
    }
    if (!isValidEmail(flowTargetEmail.value)) {
      showFeedback('Informe um e-mail corporativo válido para o gerente da agência cedente.', 'warning')
      return
    }
  }
  
  const data: NewTicketData = {
    department: department.value,
    type: type.value,
    observation: observation.value.trim(),
    attachments: attachments.value,
    ...(showFlow.value ? {
      flow: {
        requesterName: flowRequesterName.value.trim(),
        requesterEmail: flowRequesterEmail.value.trim(),
        targetName: flowTargetName.value.trim(),
        targetEmail: flowTargetEmail.value.trim()
      }
    } : {})
  }
  
  emit('submit', data)
}

function showFeedback(message: string, tone: 'info' | 'warning' | 'danger' | 'success' = 'info') {
  feedback.value = { message, tone, visible: true }
}

function clearFeedback() {
  feedback.value = { message: '', tone: 'info', visible: false }
}

function handleClose() {
  emit('update:open', false)
}

// Reset do formulário quando fechar
watch(() => props.open, (isOpen) => {
  if (!isOpen) {
    department.value = null
    type.value = null
    observation.value = ''
    attachments.value = []
    showFlow.value = false
    flowRequesterName.value = ''
    flowRequesterEmail.value = ''
    flowTargetName.value = ''
    flowTargetEmail.value = ''
    clearFeedback()
  } else {
    // Define departamento padrão se disponível
    nextTick(() => {
      if (departmentOptions.value.length > 0 && !department.value) {
        const defaultDept = currentUser.value?.defaultQueue
        if (defaultDept && departmentOptions.value.some(d => d.id === defaultDept)) {
          department.value = defaultDept
        } else {
          department.value = departmentOptions.value[0].id
        }
      }
    })
  }
}, { immediate: true })

// Reset tipo quando departamento mudar
watch(department, () => {
  type.value = null
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      id="omega-drawer"
      class="omega-drawer"
      role="dialog"
      aria-modal="true"
      aria-labelledby="omega-drawer-title"
    >
      <div class="omega-drawer__overlay" @click="handleClose"></div>
      <section class="omega-drawer__panel">
        <header class="omega-drawer__header">
          <div>
            <h3 id="omega-drawer-title">Novo chamado</h3>
            <p id="omega-drawer-desc">Preencha os campos abaixo para registrar o chamado com agilidade.</p>
          </div>
          <button
            class="omega-icon-btn"
            type="button"
            aria-label="Fechar formulário"
            @click="handleClose"
          >
            <i class="ti ti-x"></i>
          </button>
        </header>

        <div
          v-if="feedback.visible"
          id="omega-form-feedback"
          class="omega-feedback"
          :class="`omega-feedback--${feedback.tone}`"
          role="alert"
        >
          {{ feedback.message }}
        </div>

        <form id="omega-form" class="omega-form" @submit="handleSubmit">
          <div class="omega-field omega-field--static" aria-live="polite">
            <span class="omega-field__label">Solicitante</span>
            <div class="omega-field__static">{{ requesterName }}</div>
          </div>

          <div class="omega-form__grid">
            <label class="omega-field" for="omega-form-department">
              <span class="omega-field__label">Departamento</span>
              <SelectInput
                id="omega-form-department"
                :model-value="department"
                :options="departmentOptions"
                placeholder="Selecione um departamento"
                :disabled="departmentOptions.length === 0"
                @update:model-value="department = $event"
              />
            </label>

            <label class="omega-field" for="omega-form-type">
              <span class="omega-field__label">Tipo de chamado</span>
              <SelectInput
                id="omega-form-type"
                :model-value="type"
                :options="typeOptions"
                placeholder="Selecione o tipo de chamado"
                :disabled="!department || typeOptions.length === 0"
                @update:model-value="type = $event"
              />
            </label>
          </div>

          <label class="omega-field" for="omega-form-observation">
            <span class="omega-field__label">Observação</span>
            <textarea
              id="omega-form-observation"
              v-model="observation"
              class="omega-textarea"
              rows="6"
              placeholder="Descreva a demanda"
              required
            ></textarea>
          </label>

          <section
            v-if="showFlow"
            id="omega-form-flow"
            class="omega-form-flow"
            aria-hidden="false"
          >
            <header class="omega-form-flow__header">
              <h4>Fluxo de aprovações</h4>
              <p>Esta transferência exige os de acordo dos gerentes listados abaixo.</p>
            </header>
            <div class="omega-form-flow__approvals">
              <article class="omega-flow-approval">
                <div class="omega-flow-approval__icon" aria-hidden="true">
                  <i class="ti ti-user-check"></i>
                </div>
                <div class="omega-flow-approval__body">
                  <span class="omega-flow-approval__label">Gerente da agência solicitante</span>
                  <label class="omega-field" for="omega-flow-requester-name">
                    <span class="omega-field__label">Nome completo</span>
                    <input
                      id="omega-flow-requester-name"
                      v-model="flowRequesterName"
                      class="omega-input"
                      type="text"
                      placeholder="Informe o gerente responsável"
                      required
                    />
                  </label>
                  <label class="omega-field" for="omega-flow-requester-email">
                    <span class="omega-field__label">E-mail corporativo</span>
                    <input
                      id="omega-flow-requester-email"
                      v-model="flowRequesterEmail"
                      class="omega-input"
                      type="email"
                      placeholder="nome.sobrenome@empresa.com"
                      required
                    />
                  </label>
                </div>
              </article>
              <article class="omega-flow-approval">
                <div class="omega-flow-approval__icon" aria-hidden="true">
                  <i class="ti ti-building-bank"></i>
                </div>
                <div class="omega-flow-approval__body">
                  <span class="omega-flow-approval__label">Gerente da agência cedente</span>
                  <label class="omega-field" for="omega-flow-target-name">
                    <span class="omega-field__label">Nome completo</span>
                    <input
                      id="omega-flow-target-name"
                      v-model="flowTargetName"
                      class="omega-input"
                      type="text"
                      placeholder="Informe o gerente responsável"
                      required
                    />
                  </label>
                  <label class="omega-field" for="omega-flow-target-email">
                    <span class="omega-field__label">E-mail corporativo</span>
                    <input
                      id="omega-flow-target-email"
                      v-model="flowTargetEmail"
                      class="omega-input"
                      type="email"
                      placeholder="nome.sobrenome@empresa.com"
                      required
                    />
                  </label>
                </div>
              </article>
            </div>
            <p class="omega-form-flow__hint">
              Após os dois de acordo, o chamado entra automaticamente na fila de Encarteiramento.
            </p>
          </section>

          <div class="omega-field omega-field--attachments">
            <span class="omega-field__label" id="omega-form-file-label">Arquivos</span>
            <input
              ref="fileInputRef"
              id="omega-form-file"
              class="sr-only"
              type="file"
              multiple
              aria-labelledby="omega-form-file-label"
              @change="handleFileChange"
            />
            <div class="omega-attachments">
              <div class="omega-attachments__actions">
                <button
                  class="omega-btn"
                  type="button"
                  @click="handleAddFile"
                >
                  <i class="ti ti-paperclip"></i>
                  <span>Adicionar arquivo</span>
                </button>
              </div>
              <ul id="omega-form-attachments" class="omega-attachments__list" aria-live="polite">
                <li v-if="attachments.length === 0" class="omega-attachments__empty">
                  Nenhum arquivo adicionado
                </li>
                <li
                  v-for="attachment in attachments"
                  :key="attachment.id"
                  class="omega-attachments__item"
                  :data-attachment-id="attachment.id"
                >
                  <div class="omega-attachments__meta">
                    <i class="ti ti-paperclip" aria-hidden="true"></i>
                    <span class="omega-attachments__name">{{ attachment.name }}</span>
                    <span v-if="attachment.size" class="omega-attachments__size">
                      {{ formatFileSize(attachment.size) }}
                    </span>
                  </div>
                  <button
                    type="button"
                    class="omega-attachments__remove"
                    :aria-label="`Remover ${attachment.name}`"
                    @click="handleRemoveAttachment(attachment.id)"
                  >
                    <i class="ti ti-x" aria-hidden="true"></i>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <footer class="omega-form__actions">
            <button
              class="omega-btn"
              type="button"
              @click="handleClose"
            >
              Cancelar
            </button>
            <button
              class="omega-btn omega-btn--primary"
              type="submit"
            >
              <i class="ti ti-device-floppy"></i>
              <span>Salvar</span>
            </button>
          </footer>
        </form>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.omega-drawer {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  z-index: 2500;
}

.omega-drawer__overlay {
  flex: 1;
  background: rgba(15, 20, 36, 0.35);
  backdrop-filter: blur(4px);
}

.omega-drawer__panel {
  width: min(640px, calc(100% - 48px));
  max-width: 640px;
  background: #fff;
  border-radius: 18px 0 0 18px;
  box-shadow: -20px 0 48px rgba(15, 20, 36, 0.2);
  padding: 28px 28px 32px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-height: 100vh;
  overflow: auto;
}

.omega-drawer__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.omega-drawer__header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: var(--text, #0f1424);
}

.omega-drawer__header p {
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--muted, #6b7280);
}

.omega-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
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

.omega-feedback {
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 13px;
  font-weight: 600;
  background: rgba(36, 107, 253, 0.12);
  color: var(--info, #2563eb);
}

.omega-feedback--success {
  background: rgba(22, 163, 74, 0.16);
  color: var(--omega-success, #16a34a);
}

.omega-feedback--warning {
  background: rgba(249, 115, 22, 0.16);
  color: var(--omega-warning, #f97316);
}

.omega-feedback--danger {
  background: rgba(239, 68, 68, 0.16);
  color: var(--omega-danger, #ef4444);
}

.omega-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding-bottom: 16px;
}

.omega-form__grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.omega-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 12px;
}

.omega-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.omega-field__label {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.08em;
  color: var(--muted, #6b7280);
}

.omega-field--static .omega-field__static {
  min-height: 48px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.32);
  font-weight: 600;
  color: var(--text, #0f1424);
}

.omega-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 12px;
  background: #fff;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  color: var(--text, #0f1424);
  resize: vertical;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.omega-textarea:focus {
  outline: none;
  border-color: var(--brad-color-primary, #cc092f);
  box-shadow: 0 0 0 3px var(--brad-color-primary-xlight, rgba(204, 9, 47, 0.12));
}

.omega-form-flow {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 18px 20px;
  border-radius: 20px;
  background: linear-gradient(135deg, rgba(231, 236, 251, 0.65), rgba(208, 219, 255, 0.4));
  box-shadow: inset 0 0 0 1px rgba(71, 85, 105, 0.08);
}

.omega-form-flow__header {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.omega-form-flow__header h4 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #1f2a44;
}

.omega-form-flow__header p {
  margin: 0;
  font-size: 13px;
  color: #475569;
}

.omega-form-flow__approvals {
  display: grid;
  gap: 16px;
}

.omega-flow-approval {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  padding: 12px 0;
  border-top: 1px dashed rgba(148, 163, 184, 0.35);
}

.omega-flow-approval:first-child {
  border-top: none;
  padding-top: 0;
}

.omega-flow-approval:last-child {
  padding-bottom: 0;
}

.omega-flow-approval__icon {
  width: 38px;
  height: 38px;
  border-radius: 14px;
  background: #1f2a44;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  box-shadow: 0 8px 16px rgba(31, 42, 68, 0.2);
}

.omega-flow-approval__body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.omega-flow-approval__label {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.08em;
  color: #334155;
}

.omega-input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 12px;
  background: #fff;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  color: var(--text, #0f1424);
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.omega-input:focus {
  outline: none;
  border-color: var(--brad-color-primary, #cc092f);
  box-shadow: 0 0 0 3px var(--brad-color-primary-xlight, rgba(204, 9, 47, 0.12));
}

.omega-form-flow__hint {
  margin: 0;
  font-size: 12px;
  color: #475569;
}

.omega-field--attachments {
  gap: 10px;
}

.omega-attachments {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.omega-attachments__actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.omega-attachments__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.omega-attachments__empty {
  padding: 12px 14px;
  border: 1px dashed rgba(148, 163, 184, 0.6);
  border-radius: 14px;
  font-size: 13px;
  color: #64748b;
  background: rgba(15, 23, 42, 0.02);
}

.omega-attachments__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: #fff;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.omega-attachments__meta {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  color: #475569;
  font-size: 13px;
}

.omega-attachments__meta i {
  color: #2563eb;
}

.omega-attachments__name {
  font-weight: 700;
  color: var(--text, #0f1424);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.omega-attachments__size {
  font-size: 12px;
  color: #94a3b8;
}

.omega-attachments__remove {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: none;
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.omega-attachments__remove:hover,
.omega-attachments__remove:focus-visible {
  background: rgba(239, 68, 68, 0.18);
  color: #b91c1c;
  outline: none;
}

.omega-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid transparent;
  border-radius: 10px;
  background: transparent;
  color: var(--text, #0f1424);
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-btn--primary {
  background: var(--brad-color-primary, #cc092f);
  color: #fff;
  border-color: var(--brad-color-primary, #cc092f);
}

.omega-btn--primary:hover:not(:disabled) {
  background: var(--brad-color-primary-dark, #a00725);
  border-color: var(--brad-color-primary-dark, #a00725);
}

.omega-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

@media (max-width: 768px) {
  .omega-form__grid {
    grid-template-columns: 1fr;
  }
  .omega-drawer__panel {
    width: 100%;
    border-radius: 18px 18px 0 0;
  }
  .omega-attachments__item {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .omega-attachments__meta {
    width: 100%;
  }
  .omega-attachments__remove {
    align-self: flex-end;
  }
}
</style>

