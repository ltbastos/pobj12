<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import Icon from '../Icon.vue'
import type { OmegaTicket, OmegaUser } from '../../types/omega'
import type { useOmega } from '../../composables/useOmega'
import { createOmegaNotification, createPobjNotification } from '../../composables/useOmegaNotifications'

type Props = {
  omega: ReturnType<typeof useOmega>
  open: boolean
  ticketId: string | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const selectedTicket = computed(() => {
  if (!props.ticketId) return null
  return props.omega.tickets.value.find(t => t.id === props.ticketId) || null
})

const currentUser = computed(() => props.omega.currentUser.value)

const canReply = computed(() => {
  if (!selectedTicket.value || !currentUser.value) return false
  if (currentUser.value.role === 'usuario') {
    return props.omega.canUserReply(selectedTicket.value)
  }
  return true
})

const updateComment = ref('')
const isSubmitting = ref(false)
const updateError = ref<string | null>(null)

function closeModal() {
  emit('update:open', false)
}

function formatDate(dateString: string | null | undefined): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR')
  } catch {
    return '—'
  }
}

function formatDateTime(dateString: string | null | undefined): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR')
  } catch {
    return '—'
  }
}

const statusMeta = computed(() => {
  if (!selectedTicket.value) return { label: '—', tone: 'neutral' }
  const status = props.omega.statuses.value.find(s => s.id === selectedTicket.value?.status)
  return status || { label: selectedTicket.value.status || '—', tone: 'neutral' }
})

function getIconName(iconClass: string): string {
  if (!iconClass) return 'circle'
  return iconClass.replace(/^ti ti-/, '')
}

const priorityMeta = computed(() => {
  if (!selectedTicket.value) return { label: '—', tone: 'neutral', icon: 'circle' }
  const meta = props.omega.getPriorityMeta(selectedTicket.value.priority || 'media')
  return {
    ...meta,
    icon: getIconName(meta.icon)
  }
})

const requesterDisplay = computed(() => {
  if (!selectedTicket.value) return '—'
  return selectedTicket.value.requesterName || '—'
})

const ownerDisplay = computed(() => {
  if (!selectedTicket.value || !selectedTicket.value.ownerId) return 'Sem responsável'
  const owner = props.omega.users.value.find(u => u.id === selectedTicket.value?.ownerId)
  return owner?.name || 'Sem responsável'
})

const description = computed(() => {
  if (!selectedTicket.value) return 'Sem observações registradas.'
  const history = selectedTicket.value.history
  if (Array.isArray(history) && history.length > 0) {
    const firstEntry = history[0]
    if (!firstEntry) return 'Sem observações registradas.'
    const parts: string[] = []
    if (firstEntry.action) parts.push(firstEntry.action)
    if (firstEntry.comment && firstEntry.comment !== firstEntry.action) {
      parts.push(firstEntry.comment)
    }
    return parts.length ? parts.join('\n\n') : 'Sem observações registradas.'
  }
  return 'Sem observações registradas.'
})

const timeline = computed(() => {
  if (!selectedTicket.value) return []
  const history: any = selectedTicket.value.history
  if (Array.isArray(history)) {
    return [...history].sort((a, b) => {
      const dateA = new Date(a.date || '').getTime()
      const dateB = new Date(b.date || '').getTime()
      return dateA - dateB
    })
  }
  if (history && typeof history === 'string') {
    const trimmed = history.trim()
    if (trimmed.startsWith('[')) {
      try {
        const parsed = JSON.parse(history) as any[]
        if (Array.isArray(parsed)) {
          return parsed.sort((a: any, b: any) => {
            const dateA = new Date(a.date || '').getTime()
            const dateB = new Date(b.date || '').getTime()
            return dateA - dateB
          })
        }
      } catch {
      }
    }
  }
  return []
})

function handlePanelClick(e: MouseEvent) {
  e.stopPropagation()
}

function getTimelineIcon(entry: any): string {
  const action = (entry.action || '').toLowerCase()
  if (action.includes('abertura') || action.includes('criado')) return 'plus'
  if (action.includes('comentário') || action.includes('comentario')) return 'message'
  if (action.includes('status') || action.includes('atualizado')) return 'refresh'
  if (action.includes('atribuído') || action.includes('atribuido')) return 'user-check'
  if (action.includes('cancelado')) return 'x'
  if (action.includes('resolvido')) return 'check'
  return 'circle'
}

function getTimelineMarkerClass(entry: any): string {
  const action = (entry.action || '').toLowerCase()
  if (action.includes('abertura') || action.includes('criado')) return 'omega-timeline__marker--created'
  if (action.includes('comentário') || action.includes('comentario')) return 'omega-timeline__marker--comment'
  if (action.includes('status') || action.includes('atualizado')) return 'omega-timeline__marker--updated'
  if (action.includes('atribuído') || action.includes('atribuido')) return 'omega-timeline__marker--assigned'
  if (action.includes('cancelado')) return 'omega-timeline__marker--cancelled'
  if (action.includes('resolvido')) return 'omega-timeline__marker--resolved'
  return 'omega-timeline__marker--default'
}

function getActorName(actorId: string | null | undefined): string {
  if (!actorId) return 'Sistema'
  const user = props.omega.users.value.find(u => u.id === actorId)
  return user?.name || 'Usuário desconhecido'
}

function getActorInitial(actorId: string | null | undefined): string {
  if (!actorId) return 'S'
  const name = getActorName(actorId)
  return name.charAt(0).toUpperCase()
}

async function handleUpdateSubmit(event: Event) {
  event.preventDefault()
  if (!selectedTicket.value || !currentUser.value || !canReply.value) return

  if (!updateComment.value.trim()) {
    updateError.value = 'Por favor, adicione um comentário'
    return
  }

  isSubmitting.value = true
  updateError.value = null

  try {
    const now = new Date().toISOString()
    const historyEntry = {
      date: now,
      actorId: currentUser.value.id,
      action: 'Comentário adicionado',
      comment: updateComment.value.trim(),
      status: selectedTicket.value.status,
      attachments: []
    }

    const currentHistory = Array.isArray(selectedTicket.value.history) 
      ? [...selectedTicket.value.history] 
      : []
    
    currentHistory.push(historyEntry)

    const updatedTicket = await props.omega.updateTicket(selectedTicket.value.id, {
      updated: now,
      history: currentHistory
    })

    if (!updatedTicket) {
      throw new Error('Falha ao atualizar ticket')
    }

    await props.omega.loadTickets()

    try {
      await createOmegaNotification(selectedTicket.value, historyEntry)
      
      if (selectedTicket.value.queue?.toLowerCase() === 'pobj') {
        await createPobjNotification(selectedTicket.value, historyEntry)
      }
    } catch (notifErr) {
      console.warn('Erro ao criar notificação:', notifErr)
    }

    updateComment.value = ''
    updateError.value = null
  } catch (err) {
    updateError.value = err instanceof Error ? err.message : 'Erro ao registrar atualização. Tente novamente.'
    console.error('Erro ao atualizar ticket:', err)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open && selectedTicket"
      id="omega-ticket-modal"
      class="omega-ticket-modal"
      @click="closeModal"
    >
      <div class="omega-ticket-modal__overlay"></div>
      <section
        class="omega-ticket-modal__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="omega-ticket-modal-title"
        @click="handlePanelClick"
      >
        <header class="omega-ticket-modal__header">
          <div class="omega-ticket-modal__titles">
            <h3 id="omega-ticket-modal-title">Detalhe do chamado {{ selectedTicket.id }}</h3>
            <p id="omega-ticket-modal-meta">
              {{ selectedTicket.subject || 'Sem assunto' }}
            </p>
          </div>
          <button
            class="omega-icon-btn"
            type="button"
            title="Fechar detalhamento"
            @click="closeModal"
          >
            <Icon name="x" :size="20" />
          </button>
        </header>
        <div class="omega-ticket-modal__body">
          <section class="omega-ticket-overview" aria-labelledby="omega-ticket-overview-title">
            <h4 id="omega-ticket-overview-title">Informações do chamado</h4>
            <div class="omega-ticket-overview__grid">
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Solicitante</span>
                <strong id="omega-ticket-requester">{{ requesterDisplay }}</strong>
                <small id="omega-ticket-requester-meta">{{ requesterDisplay }}</small>
              </article>
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Responsável</span>
                <strong id="omega-ticket-owner">{{ ownerDisplay }}</strong>
                <small id="omega-ticket-owner-meta">{{ ownerDisplay }}</small>
              </article>
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Produto</span>
                <strong id="omega-ticket-product">{{ selectedTicket.product || '—' }}</strong>
                <small id="omega-ticket-queue">Fila {{ selectedTicket.queue || '—' }}</small>
              </article>
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Abertura</span>
                <strong id="omega-ticket-opened">{{ formatDate(selectedTicket.opened) }}</strong>
                <small id="omega-ticket-opened-time">{{ formatDateTime(selectedTicket.opened) }}</small>
              </article>
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Status</span>
                <strong id="omega-ticket-status">{{ statusMeta.label }}</strong>
                <small id="omega-ticket-updated">
                  Última atualização em {{ formatDateTime(selectedTicket.updated) }}
                </small>
              </article>
              <article class="omega-ticket-card">
                <span class="omega-ticket-card__label">Prioridade</span>
                <strong id="omega-ticket-priority">{{ priorityMeta.label }}</strong>
                <small id="omega-ticket-due">
                  {{ selectedTicket.dueDate ? formatDate(selectedTicket.dueDate) : 'Sem prazo definido' }}
                </small>
              </article>
            </div>
          </section>
          <section class="omega-ticket-description" aria-labelledby="omega-ticket-description-title">
            <div class="omega-ticket-description__header">
              <div>
                <h4 id="omega-ticket-description-title">Descrição do Chamado</h4>
                <small class="omega-ticket-description__subtitle">Informações detalhadas sobre a solicitação</small>
              </div>
              <span id="omega-ticket-id" class="omega-ticket-description__id">{{ selectedTicket.id }}</span>
            </div>
            <div id="omega-ticket-description" class="omega-ticket-description__content">
              <template v-if="description && description !== 'Sem observações registradas.'">
                <p v-for="(line, index) in description.split('\n\n')" :key="index">
                  {{ line }}
                </p>
              </template>
              <p v-else class="omega-ticket-description__empty">
                Sem observações registradas.
              </p>
            </div>
          </section>
          <section class="omega-ticket-timeline" aria-labelledby="omega-ticket-timeline-title">
            <h4 id="omega-ticket-timeline-title">Linha do tempo</h4>
            <ol v-if="timeline.length > 0" id="omega-ticket-timeline" class="omega-timeline">
              <li
                v-for="(entry, index) in timeline"
                :key="index"
                class="omega-timeline__item"
              >
                <div class="omega-timeline__marker-wrapper">
                  <div class="omega-timeline__marker" :class="getTimelineMarkerClass(entry)">
                    <Icon :name="getTimelineIcon(entry)" :size="16" />
                  </div>
                </div>
                <div class="omega-timeline__content">
                  <div class="omega-timeline__card">
                    <div class="omega-timeline__header">
                      <div class="omega-timeline__header-left">
                        <strong class="omega-timeline__action">{{ entry.action || 'Atualização' }}</strong>
                        <div v-if="entry.actorId" class="omega-timeline__actor">
                          <span class="omega-timeline__actor-avatar">
                            {{ getActorInitial(entry.actorId) }}
                          </span>
                          <span class="omega-timeline__actor-name">{{ getActorName(entry.actorId) }}</span>
                        </div>
                      </div>
                      <time class="omega-timeline__date">{{ formatDateTime(entry.date) }}</time>
                    </div>
                    <div v-if="entry.comment" class="omega-timeline__comment">
                      {{ entry.comment }}
                    </div>
                    <div v-if="entry.status" class="omega-timeline__meta">
                      <span class="omega-timeline__status-badge">
                        <Icon name="info-circle" :size="16" />
                        Status alterado para: <strong>{{ props.omega.statuses.value.find(s => s.id === entry.status)?.label || entry.status }}</strong>
                      </span>
                    </div>
                  </div>
                </div>
              </li>
            </ol>
            <div v-else class="omega-timeline-empty">
              <Icon name="clock" :size="48" />
              <p>Nenhuma atualização registrada ainda.</p>
            </div>
          </section>

          <section 
            v-if="canReply" 
            class="omega-ticket-update" 
            aria-labelledby="omega-ticket-update-title"
          >
            <h4 id="omega-ticket-update-title">Adicionar comentário</h4>
            <form 
              class="omega-ticket-update__form" 
              @submit="handleUpdateSubmit"
            >
              <div v-if="updateError" class="omega-ticket-update__error" role="alert">
                {{ updateError }}
              </div>
              <label class="omega-field" for="omega-ticket-update-comment">
                <span class="omega-field__label">Comentário</span>
                <textarea
                  id="omega-ticket-update-comment"
                  v-model="updateComment"
                  class="omega-textarea"
                  rows="4"
                  placeholder="Adicione um comentário ou atualização sobre este chamado"
                  required
                  :disabled="isSubmitting"
                ></textarea>
              </label>
              <footer class="omega-ticket-update__actions">
                <button
                  type="submit"
                  class="omega-btn omega-btn--primary"
                  :disabled="isSubmitting || !updateComment.trim()"
                >
                  <Icon name="message-plus" :size="18" />
                  <span>{{ isSubmitting ? 'Enviando...' : 'Enviar comentário' }}</span>
                </button>
              </footer>
            </form>
          </section>

          <section 
            v-else-if="currentUser?.role === 'usuario' && selectedTicket" 
            class="omega-ticket-update omega-ticket-update--blocked"
          >
            <div class="omega-ticket-update__blocked-message">
              <Icon name="lock" :size="24" />
              <p>
                <strong>Este chamado está {{ statusMeta.label.toLowerCase() }}.</strong>
                <br>
                Chamados com status final não permitem novas réplicas.
              </p>
            </div>
          </section>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.omega-ticket-modal {
  position: fixed;
  inset: 0;
  z-index: 2400;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 28px;
  font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
}

.omega-ticket-modal__overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 20, 36, 0.4);
  backdrop-filter: blur(2px);
  animation: overlayFadeIn 0.15s ease-out;
}

@keyframes overlayFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.omega-ticket-modal__panel {
  position: relative;
  z-index: 1;
  width: min(1180px, 96vw);
  max-height: 92vh;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(15, 20, 36, 0.12);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalSlideIn 0.2s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.omega-ticket-modal__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 24px 32px;
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  background: #fafbfc;
}

.omega-ticket-modal__titles {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.omega-ticket-modal__titles h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: -0.01em;
}

.omega-ticket-modal__titles p {
  margin: 4px 0 0 0;
  font-size: 13px;
  color: #64748b;
  font-weight: 400;
}

.omega-icon-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  background: #fff;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.omega-icon-btn:hover {
  background: #f1f5f9;
  color: #475569;
  border-color: #cbd5e1;
}

.omega-ticket-modal__body {
  flex: 1;
  padding: 24px 32px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  overflow-y: auto;
  overflow-x: hidden;
  background: #fff;
  scrollbar-width: thin;
  scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
}

.omega-ticket-modal__body::-webkit-scrollbar {
  width: 8px;
}

.omega-ticket-modal__body::-webkit-scrollbar-track {
  background: transparent;
}

.omega-ticket-modal__body::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.4);
  border-radius: 4px;
}

.omega-ticket-modal__body::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.6);
}

.omega-ticket-overview {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.omega-ticket-overview h4 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: -0.01em;
}

.omega-ticket-overview__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.omega-ticket-card {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
  border-radius: 8px;
  background: #fafbfc;
  border: 1px solid rgba(226, 232, 240, 0.8);
  transition: border-color 0.15s ease;
}

.omega-ticket-card:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}

.omega-ticket-card__label {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
}

.omega-ticket-card strong {
  font-size: 14px;
  font-weight: 500;
  color: #1e293b;
  line-height: 1.5;
}

.omega-ticket-card small {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 400;
}

.omega-ticket-description {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  border-radius: 8px;
  background: #fafbfc;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.omega-ticket-description__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  margin-bottom: 16px;
}

.omega-ticket-description__header h4 {
  margin: 0 0 4px 0;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
}

.omega-ticket-description__subtitle {
  display: block;
  font-size: 12px;
  font-weight: 400;
  color: #94a3b8;
  margin-top: 2px;
}

.omega-ticket-description__id {
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  font-family: 'Courier New', monospace;
  padding: 4px 8px;
  background: #f1f5f9;
  border-radius: 4px;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.omega-ticket-description__content {
  font-size: 14px;
  line-height: 1.7;
  color: #334155;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.omega-ticket-description__content p {
  margin: 0 0 12px 0;
  padding: 12px 14px;
  background: #fff;
  border-radius: 6px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  line-height: 1.6;
}

.omega-ticket-description__content p:first-child {
  margin-top: 0;
}

.omega-ticket-description__content p:last-child {
  margin-bottom: 0;
}

.omega-ticket-description__empty {
  color: #94a3b8;
  font-style: italic;
  font-size: 13px;
  text-align: center;
  padding: 24px;
  background: #f8fafc;
  border-radius: 6px;
  border: 1px dashed rgba(226, 232, 240, 0.8);
}

.omega-ticket-timeline {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  border-radius: 8px;
  background: #fafbfc;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.omega-ticket-timeline:has(.omega-timeline:empty)::after {
  content: 'Nenhuma atualização registrada ainda.';
  display: block;
  text-align: center;
  padding: 32px;
  color: #94a3b8;
  font-style: italic;
}

.omega-ticket-timeline h4 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
}

.omega-timeline {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
}

.omega-timeline::before {
  content: '';
  position: absolute;
  left: 24px;
  top: 0;
  bottom: 0;
  width: 1px;
  background: #e2e8f0;
  z-index: 0;
}

.omega-timeline__item {
  display: flex;
  gap: 16px;
  position: relative;
  z-index: 1;
}

.omega-timeline__marker-wrapper {
  position: relative;
  flex-shrink: 0;
  width: 48px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 4px;
}

.omega-timeline__marker {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  position: relative;
  z-index: 2;
  background: #fff;
  color: #64748b;
  font-size: 14px;
  transition: all 0.15s ease;
}

.omega-timeline__marker i {
  font-size: 16px;
}

.omega-timeline__marker--created {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #16a34a;
}

.omega-timeline__marker--comment {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #2563eb;
}

.omega-timeline__marker--updated {
  background: #fffbeb;
  border-color: #fde68a;
  color: #d97706;
}

.omega-timeline__marker--assigned {
  background: #f5f3ff;
  border-color: #ddd6fe;
  color: #7c3aed;
}

.omega-timeline__marker--cancelled {
  background: #fef2f2;
  border-color: #fecaca;
  color: #dc2626;
}

.omega-timeline__marker--resolved {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #16a34a;
}

.omega-timeline__marker--default {
  background: #f8fafc;
  border-color: #e2e8f0;
  color: #64748b;
}

.omega-timeline__item:hover .omega-timeline__marker {
  border-color: #cbd5e1;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.omega-timeline__content {
  flex: 1;
  min-width: 0;
}

.omega-timeline__card {
  background: #fff;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 8px;
  padding: 14px 16px;
  transition: border-color 0.15s ease;
}

.omega-timeline__item:hover .omega-timeline__card {
  border-color: #cbd5e1;
}

.omega-timeline__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
}

.omega-timeline__header-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.omega-timeline__action {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
}

.omega-timeline__actor {
  display: flex;
  align-items: center;
  gap: 8px;
}

.omega-timeline__actor-avatar {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  background: #e2e8f0;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  flex-shrink: 0;
}

.omega-timeline__actor-name {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.omega-timeline__date {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
  white-space: nowrap;
  flex-shrink: 0;
}

.omega-timeline__comment {
  margin: 12px 0 0 0;
  padding: 12px 14px;
  background: #f8fafc;
  border-left: 2px solid #e2e8f0;
  border-radius: 6px;
  font-size: 13px;
  line-height: 1.6;
  color: #334155;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.omega-timeline__meta {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(148, 163, 184, 0.15);
}

.omega-timeline__status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #f1f5f9;
  border-radius: 4px;
  font-size: 12px;
  color: #64748b;
  font-weight: 400;
}

.omega-timeline__status-badge i {
  font-size: 12px;
  color: #94a3b8;
}

.omega-timeline__status-badge strong {
  font-weight: 600;
  color: #475569;
}

.omega-timeline-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
  color: #94a3b8;
}

.omega-timeline-empty i {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.omega-timeline-empty p {
  margin: 0;
  font-size: 14px;
  font-style: italic;
}

.omega-ticket-card strong {
  font-size: 16px;
  font-weight: 700;
  color: var(--text, #0f1424);
  display: flex;
  align-items: center;
  gap: 8px;
}

.omega-ticket-card strong::before {
  content: '';
  width: 4px;
  height: 16px;
  background: var(--brad-color-primary, #cc092f);
  border-radius: 2px;
  opacity: 0.6;
}

.omega-ticket-update {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  border-radius: 8px;
  background: #fafbfc;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.omega-ticket-update h4 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
}

.omega-ticket-update__form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.omega-ticket-update__error {
  padding: 12px 16px;
  border-radius: 8px;
  background: #fee2e2;
  color: #991b1b;
  font-size: 14px;
  font-weight: 500;
}

.omega-ticket-update__actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.omega-ticket-update--blocked {
  background: #fef2f2;
  border-color: #fecaca;
}

.omega-ticket-update__blocked-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px;
  text-align: center;
  color: #991b1b;
}

.omega-ticket-update__blocked-message i {
  font-size: 32px;
  opacity: 0.7;
}

.omega-ticket-update__blocked-message p {
  margin: 0;
  font-size: 14px;
  line-height: 1.6;
}

.omega-ticket-update__blocked-message strong {
  display: block;
  margin-bottom: 4px;
  font-weight: 600;
}

.omega-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.omega-field__label {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.08em;
  color: #64748b;
}

.omega-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 8px;
  background: #fff;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  color: #1e293b;
  resize: vertical;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.omega-textarea:focus {
  outline: none;
  border-color: var(--brad-color-primary, #cc092f);
  box-shadow: 0 0 0 3px rgba(204, 9, 47, 0.12);
}

.omega-textarea:disabled {
  background: #f1f5f9;
  cursor: not-allowed;
}

.omega-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: #1e293b;
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

@media (max-width: 768px) {
  .omega-ticket-modal {
    padding: 12px;
  }

  .omega-ticket-modal__panel {
    border-radius: 20px;
    max-height: 94vh;
  }

  .omega-ticket-modal__header {
    padding: 22px 20px 16px;
  }

  .omega-ticket-modal__body {
    padding: 22px 18px 26px;
    gap: 24px;
  }

  .omega-ticket-overview__grid {
    grid-template-columns: 1fr;
  }

  .omega-ticket-card {
    padding: 12px;
  }

  .omega-ticket-description,
  .omega-ticket-timeline,
  .omega-ticket-update {
    padding: 16px;
  }
}
</style>

