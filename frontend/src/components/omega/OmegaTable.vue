<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import Icon from '../Icon.vue'
import type { OmegaTicket, OmegaUser } from '../../types/omega'
import type { useOmega } from '../../composables/useOmega'
import type { useOmegaFilters } from '../../composables/useOmegaFilters'
import type { useOmegaBulk } from '../../composables/useOmegaBulk'

type Props = {
  omega: ReturnType<typeof useOmega>
  filters: ReturnType<typeof useOmegaFilters>
  bulk: ReturnType<typeof useOmegaBulk>
  searchQuery?: string
}

const props = withDefaults(defineProps<Props>(), {
  searchQuery: ''
})

const emit = defineEmits<{
  'ticket-click': [ticketId: string]
  'select-all': [checked: boolean]
  'new-ticket': []
}>()

const currentPage = ref(1)
const pageSize = ref(15)

const filteredTickets = computed(() => {
  let tickets = props.omega.tickets.value || []
  const currentUser = props.omega.currentUser.value
  const currentView = props.omega.currentView.value

  if (!tickets || tickets.length === 0) {
    return []
  }

  if (currentView === 'my' && currentUser) {
    
    tickets = tickets.filter((t) => t.requesterId === currentUser.id)
  } else if (currentView === 'assigned' && currentUser) {
    
    tickets = tickets.filter((t) => t.ownerId === currentUser.id)
  } else if (currentView === 'queue' && currentUser) {
    
    if (currentUser.queues && currentUser.queues.length > 0) {
      tickets = tickets.filter((t) => currentUser.queues?.includes(t.queue || ''))
    } else {
      
      if (currentUser.role !== 'admin') {
        tickets = []
      }
    }
  }

  tickets = props.filters.applyFilters(tickets)

  if (props.searchQuery) {
    const query = props.searchQuery.toLowerCase().trim()
    tickets = tickets.filter((ticket) => {
      const searchableText = [
        ticket.id,
        ticket.subject,
        ticket.requesterName,
        ticket.product,
        ticket.queue,
        ticket.category
      ].join(' ').toLowerCase()
      return searchableText.includes(query)
    })
  }
  return tickets
})

const paginatedTickets = computed(() => {
  if (!filteredTickets.value || filteredTickets.value.length === 0) {
    return []
  }
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredTickets.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredTickets.value.length / pageSize.value))
})

const canSelect = computed(() => {
  const user = props.omega.currentUser.value
  if (!user) return false
  const role = user.role
  return ['analista', 'supervisor', 'admin'].includes(role)
})

const showPriority = computed(() => {
  const user = props.omega.currentUser.value
  if (!user) return false
  
  return ['analista', 'supervisor', 'admin'].includes(user.role)
})

const showOwner = computed(() => {
  const user = props.omega.currentUser.value
  if (!user) return false
  
  return ['analista', 'supervisor', 'admin'].includes(user.role)
})

const totalColumns = computed(() => {
  let count = 10 
  if (canSelect.value) count++
  if (showPriority.value) count++
  if (showOwner.value) count++ 
  return count
})

const allSelected = computed(() => {
  if (!canSelect.value || paginatedTickets.value.length === 0) return false
  return paginatedTickets.value.every((ticket) => props.bulk.selectedTicketIds.value.has(ticket.id))
})

const someSelected = computed(() => {
  if (!canSelect.value) return false
  const selectedCount = paginatedTickets.value.filter((ticket) => 
    props.bulk.selectedTicketIds.value.has(ticket.id)
  ).length
  return selectedCount > 0 && selectedCount < paginatedTickets.value.length
})

function formatDate(dateString: string): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR')
  } catch {
    return '—'
  }
}

function getIconName(iconClass: string): string {
  if (!iconClass) return 'circle'
  
  return iconClass.replace(/^ti ti-/, '')
}

function handleTicketClick(ticketId: string) {
  emit('ticket-click', ticketId)
}

function handleSelectAll(checked: boolean) {
  emit('select-all', checked)
}

function handleTicketSelect(ticketId: string, checked: boolean) {
  if (checked) {
    props.bulk.selectedTicketIds.value.add(ticketId)
  } else {
    props.bulk.selectedTicketIds.value.delete(ticketId)
  }
}

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

watch([() => props.filters.filters.value, () => props.searchQuery], () => {
  currentPage.value = 1
}, { deep: true })

watch(() => props.omega.currentView.value, () => {
  currentPage.value = 1
})
</script>

<template>
  <div class="omega-table-container">
    <div
      id="omega-table-wrapper"
      class="omega-table-wrapper"
      role="region"
      aria-labelledby="omega-table-caption"
      :data-selection-enabled="canSelect ? 'true' : 'false'"
    >
      <table
        id="omega-ticket-table"
        class="omega-table"
        :data-priority-visible="showPriority ? 'true' : 'false'"
      >
        <caption id="omega-table-caption" class="sr-only">
          Chamados filtrados pela visão atual
        </caption>
        <thead>
          <tr>
            <th v-if="canSelect" scope="col" class="col-select" aria-label="Selecionar">
              <input
                id="omega-select-all"
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                aria-label="Selecionar todos"
                @change="handleSelectAll(($event.target as HTMLInputElement).checked)"
              />
            </th>
            <th scope="col">ID</th>
            <th scope="col">Prévia</th>
            <th scope="col">Departamento</th>
            <th scope="col">Tipo</th>
            <th scope="col">Usuário</th>
            <th v-if="showPriority" scope="col" data-priority-column>Prioridade</th>
            <th v-if="showOwner" scope="col">Atendente</th>
            <th scope="col">Produto</th>
            <th scope="col">Fila</th>
            <th scope="col">Abertura</th>
            <th scope="col">Atualização</th>
            <th scope="col" class="col-status">Status</th>
          </tr>
        </thead>
        <tbody id="omega-ticket-rows">
          <template v-if="filteredTickets.length === 0">
            <tr class="omega-empty-row">
              <td :colspan="totalColumns" class="omega-empty-state">
                <div class="omega-empty-state__content">
                  <Icon name="inbox" :size="48" />
                  <h3>Nenhum chamado encontrado</h3>
                  <p>Não há chamados para exibir nesta visualização.</p>
                  <button
                    class="omega-btn omega-btn--primary"
                    type="button"
                    @click="$emit('new-ticket')"
                  >
                    <Icon name="plus" :size="18" color="white" />
                    <span class="omega-btn--primary-text">Registrar novo chamado</span>
                  </button>
                </div>
              </td>
            </tr>
          </template>
          <template v-else>
            <tr
              v-for="ticket in paginatedTickets"
              :key="ticket.id"
              class="omega-table__row"
              :data-ticket-id="ticket.id"
              @click="handleTicketClick(ticket.id)"
            >
            <td v-if="canSelect" class="col-select" @click.stop>
              <input
                type="checkbox"
                :checked="bulk.selectedTicketIds.value.has(ticket.id)"
                :value="ticket.id"
                :aria-label="`Selecionar chamado ${ticket.id}`"
                @change="handleTicketSelect(ticket.id, ($event.target as HTMLInputElement).checked)"
              />
            </td>
            <td>
              <span class="omega-ticket__id">
                <Icon name="ticket" :size="18" />
                {{ ticket.id }}
              </span>
            </td>
            <td>
              <div class="omega-table__preview">
                <strong>{{ ticket.subject || 'Sem assunto' }}</strong>
                <small>{{ ticket.requesterName || '—' }}</small>
              </div>
            </td>
            <td>{{ ticket.queue || '—' }}</td>
            <td>{{ ticket.category || '—' }}</td>
            <td>{{ ticket.requesterName || '—' }}</td>
            <td v-if="showPriority" :data-priority="ticket.priority">
              <span
                class="omega-badge"
                :class="`omega-badge--${props.omega.getPriorityMeta(ticket.priority).tone}`"
              >
                <Icon :name="getIconName(props.omega.getPriorityMeta(ticket.priority).icon)" :size="14" />
                {{ props.omega.getPriorityMeta(ticket.priority).label }}
              </span>
            </td>
            <td v-if="showOwner">
              {{ ticket.ownerId ? (props.omega.users.value.find(u => u.id === ticket.ownerId)?.name || '—') : 'Sem responsável' }}
            </td>
            <td>{{ ticket.product || '—' }}</td>
            <td>{{ ticket.queue || '—' }}</td>
            <td>{{ formatDate(ticket.opened) }}</td>
            <td>{{ formatDate(ticket.updated) }}</td>
            <td class="col-status">
              <span
                class="omega-badge omega-badge--status"
                :class="`omega-badge--${(props.omega.statuses.value.find(s => s.id === ticket.status) || { tone: 'neutral' }).tone}`"
              >
                {{ (props.omega.statuses.value.find(s => s.id === ticket.status) || { label: ticket.status }).label }}
              </span>
            </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <footer v-if="filteredTickets.length > 0" class="omega-table-footer" aria-live="polite">
      <span id="omega-table-range" class="omega-table-range">
        {{ (currentPage - 1) * pageSize + 1 }} – {{ Math.min(currentPage * pageSize, filteredTickets.length) }} de {{ filteredTickets.length }} chamados
      </span>
      <nav id="omega-pagination" class="omega-pagination" aria-label="Paginação de chamados">
        <button
          class="omega-pagination__button"
          type="button"
          :disabled="currentPage <= 1"
          aria-label="Página anterior"
          @click="goToPage(currentPage - 1)"
        >
          Anterior
        </button>
        <template v-for="page in totalPages" :key="page">
          <button
            v-if="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
            class="omega-pagination__button"
            :class="{ 'omega-pagination__button--current': page === currentPage }"
            type="button"
            :aria-current="page === currentPage ? 'page' : undefined"
            :aria-label="`Ir para a página ${page}`"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
          <span
            v-else-if="page === currentPage - 2 || page === currentPage + 2"
            class="omega-pagination__gap"
          >
            …
          </span>
        </template>
        <button
          class="omega-pagination__button"
          type="button"
          :disabled="currentPage >= totalPages"
          aria-label="Próxima página"
          @click="goToPage(currentPage + 1)"
        >
          Próxima
        </button>
      </nav>
    </footer>
  </div>
</template>

<style scoped>
.omega-table-container {
  width: 100%;
}

.omega-table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: #fff;
  box-shadow: none;
}

.omega-table {
  width: 100%;
  border-collapse: collapse;
  font-family: var(--brad-font-family, inherit);
}

.omega-table thead {
  background: #f8fafc;
  border-bottom: 2px solid rgba(148, 163, 184, 0.2);
  position: sticky;
  top: 0;
  z-index: 2;
}

.omega-table th {
  padding: 12px 16px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--muted, #6b7280);
  white-space: nowrap;
}

.omega-table tbody tr {
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  transition: background-color 0.2s ease;
  cursor: pointer;
}

.omega-table tbody tr:hover {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.04));
}

.omega-table tbody tr.omega-empty-row {
  cursor: default;
}

.omega-table tbody tr.omega-empty-row:hover {
  background: transparent;
}

.omega-table td {
  padding: 12px 16px;
  font-size: 14px;
  color: var(--text, #0f1424);
}

.col-select {
  width: 48px;
  text-align: center;
}

.col-status {
  width: 120px;
}

.omega-ticket__id {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: var(--brad-color-primary, #cc092f);
}

.omega-table__preview {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.omega-table__preview strong {
  font-weight: 600;
  color: var(--text, #0f1424);
}

.omega-table__preview small {
  font-size: 12px;
  color: var(--muted, #6b7280);
}

.omega-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.omega-badge--neutral {
  background: #f1f5f9;
  color: #475569;
}

.omega-badge--progress {
  background: #dbeafe;
  color: #1e40af;
}

.omega-badge--warning {
  background: #fef3c7;
  color: #92400e;
}

.omega-badge--danger {
  background: #fee2e2;
  color: #991b1b;
}

.omega-badge--success {
  background: #d1fae5;
  color: #065f46;
}

.omega-empty-state {
  padding: 48px 24px;
  text-align: center;
}

.omega-empty-state__content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.omega-empty-state__content i {
  font-size: 64px;
  color: var(--muted, #6b7280);
  opacity: 0.6;
}

.omega-empty-state__content h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text, #0f1424);
}

.omega-empty-state__content p {
  margin: 0;
  font-size: 14px;
  color: var(--muted, #6b7280);
}

.omega-table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-top: 1px solid rgba(148, 163, 184, 0.2);
  background: #fff;
}

.omega-table-range {
  font-size: 14px;
  color: var(--muted, #6b7280);
}

.omega-pagination {
  display: flex;
  align-items: center;
  gap: 4px;
}

.omega-pagination__button {
  min-width: 36px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 8px;
  background: #fff;
  color: var(--text, #0f1424);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-pagination__button:hover:not(:disabled) {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  border-color: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
}

.omega-pagination__button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.omega-pagination__button--current {
  background: var(--brad-color-primary, #cc092f);
  border-color: var(--brad-color-primary, #cc092f);
  color: #fff;
}

.omega-pagination__button--current:hover {
  background: var(--brad-color-primary-dark, #a00725);
  border-color: var(--brad-color-primary-dark, #a00725);
}

.omega-pagination__gap {
  padding: 0 8px;
  color: var(--muted, #6b7280);
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

.omega-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid transparent;
  border-radius: 10px;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-btn--primary {
  background: var(--brad-color-primary, #cc092f);
  color: #fff !important;
  border-color: var(--brad-color-primary, #cc092f);
}

.omega-btn--primary span,
.omega-btn--primary-text {
  color: #fff !important;
}

.omega-btn--primary:hover:not(:disabled) {
  background: var(--brad-color-primary-dark, #a00725);
  border-color: var(--brad-color-primary-dark, #a00725);
  color: #fff !important;
}

.omega-btn--primary:hover:not(:disabled) span,
.omega-btn--primary:hover:not(:disabled) .omega-btn--primary-text {
  color: #fff !important;
}
</style>

