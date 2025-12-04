<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import Icon from '../Icon.vue'
import type { useOmega } from '../../composables/useOmega'
import type { useOmegaFilters } from '../../composables/useOmegaFilters'
import type { useOmegaBulk } from '../../composables/useOmegaBulk'

interface Props {
  omega: ReturnType<typeof useOmega>
  filters: ReturnType<typeof useOmegaFilters>
  bulk: ReturnType<typeof useOmegaBulk>
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'search': [value: string]
  'filter-toggle': []
  'clear-filters': []
  'refresh': []
  'new-ticket': []
  'bulk-status': []
}>()

const searchQuery = ref('')
const isRefreshing = ref(false)

const hasActiveFilters = computed(() => props.filters.hasActiveFilters())
const hasSelection = computed(() => props.bulk.selectedTicketIds.value.size > 0)
const canSelect = computed(() => {
  const user = props.omega.currentUser.value
  if (!user) return false
  const role = user.role
  return ['analista', 'supervisor', 'admin'].includes(role)
})

function handleSearchInput(value: string) {
  searchQuery.value = value
  emit('search', value)
}

function handleFilterToggle() {
  emit('filter-toggle')
}

function handleClearFilters() {
  props.filters.resetFilters()
  emit('clear-filters')
}

async function handleRefresh() {
  isRefreshing.value = true
  emit('refresh')
  // Aguarda um pouco para mostrar o estado de loading
  setTimeout(() => {
    isRefreshing.value = false
  }, 500)
}

function handleNewTicket() {
  emit('new-ticket')
}

function handleBulkStatus() {
  emit('bulk-status')
}

// Watch para sincronizar busca quando filtros mudarem
watch(() => props.filters.filters.value, () => {
  // Se os filtros mudarem, pode precisar atualizar a busca
}, { deep: true })
</script>

<template>
  <div class="omega-toolbar" role="group" aria-label="Filtros da central Omega">
    <label class="omega-search" for="omega-search">
      <Icon name="search" :size="18" />
      <input
        id="omega-search"
        v-model="searchQuery"
        type="search"
        placeholder="Buscar chamado ou usuário"
        autocomplete="off"
        @input="handleSearchInput(($event.target as HTMLInputElement).value)"
      />
    </label>
    <div class="omega-toolbar__actions">
      <div class="omega-toolbar__cluster">
        <div class="omega-filters">
          <button
            id="omega-filters-toggle"
            class="omega-btn omega-btn--ghost"
            type="button"
            :data-active="hasActiveFilters ? 'true' : 'false'"
            aria-haspopup="dialog"
            :aria-expanded="filters.filterPanelOpen.value ? 'true' : 'false'"
            aria-controls="omega-filter-panel"
            @click="handleFilterToggle"
          >
            <Icon name="adjustments-horizontal" :size="18" />
            <span>Filtros</span>
          </button>
          <button
            v-if="hasActiveFilters"
            id="omega-clear-filters-top"
            class="omega-btn omega-btn--ghost"
            type="button"
            @click="handleClearFilters"
          >
            <Icon name="x" :size="18" />
            <span>Limpar filtros</span>
          </button>
          <button
            id="omega-refresh"
            class="omega-btn omega-btn--ghost"
            type="button"
            :data-loading="isRefreshing ? 'true' : 'false'"
            :disabled="isRefreshing"
            @click="handleRefresh"
          >
            <Icon :name="isRefreshing ? 'loader-2' : 'refresh'" :size="18" />
            <span>Atualizar lista</span>
          </button>
        </div>
      </div>
      <button
        v-if="canSelect"
        id="omega-bulk-status"
        class="omega-btn omega-btn--ghost"
        type="button"
        :hidden="!hasSelection"
        :disabled="!hasSelection"
        @click="handleBulkStatus"
      >
        <Icon name="arrows-exchange" :size="18" />
        <span>Alterar status</span>
      </button>
      <button
        id="omega-new-ticket"
        class="omega-btn omega-btn--primary"
        type="button"
        @click="handleNewTicket"
      >
        <Icon name="plus" :size="18" color="white" />
        <span>Registrar chamado</span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.omega-toolbar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
  background: #fff;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.omega-search {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}

.omega-search :deep(svg) {
  position: absolute;
  left: 12px;
  color: var(--muted, #6b7280);
  pointer-events: none;
}

.omega-search input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 10px;
  background: #fff;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  color: var(--text, #0f1424);
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.omega-search input:focus {
  outline: none;
  border: 2px solid var(--brad-color-primary, #cc092f);
  box-shadow: none;
}

.omega-toolbar__actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.omega-toolbar__cluster {
  display: flex;
  align-items: center;
  gap: 8px;
}

.omega-filters {
  display: flex;
  align-items: center;
  gap: 8px;
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

.omega-btn--ghost {
  border-color: var(--stroke, #e7eaf2);
  background: #fff;
}

.omega-btn--ghost:hover:not(:disabled) {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  border-color: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
}

.omega-btn--primary {
  background: var(--brad-color-primary, #cc092f);
  color: #fff !important;
  border-color: var(--brad-color-primary, #cc092f);
}

.omega-btn--primary span {
  color: #fff !important;
}

.omega-btn--primary:hover:not(:disabled) {
  background: var(--brad-color-primary-dark, #a00725);
  border-color: var(--brad-color-primary-dark, #a00725);
  color: #fff !important;
}

.omega-btn--primary:hover:not(:disabled) span {
  color: #fff !important;
}

.omega-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.omega-btn[data-loading="true"] {
  pointer-events: none;
}

.omega-btn[data-active="true"] {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
  border-color: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
}
</style>

