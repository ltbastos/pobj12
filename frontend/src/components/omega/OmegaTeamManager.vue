<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Teleport } from 'vue'
import Icon from '../Icon.vue'
import type { OmegaUser } from '../../types/omega'
import type { useOmega } from '../../composables/useOmega'
import { apiPost, apiGet } from '../../services/api'
import type { ApiResponse } from '../../types'

type Props = {
  omega: ReturnType<typeof useOmega>
  open: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const currentUser = computed(() => props.omega.currentUser.value)
const isSupervisor = computed(() => {
  return currentUser.value?.role === 'supervisor' || currentUser.value?.role === 'admin'
})

const teamAnalysts = ref<OmegaUser[]>([])
const availableAnalysts = ref<OmegaUser[]>([])
const isLoading = ref(false)
const error = ref<string | null>(null)

async function loadTeamAnalysts() {
  if (!currentUser.value || !isSupervisor.value) return

  isLoading.value = true
  error.value = null

  try {
    
    const response = await apiGet<OmegaUser[]>(`/api/omega/teams/${currentUser.value.id}/analysts`)
    if (response.success && response.data) {
      teamAnalysts.value = response.data
    }
  } catch (err) {
    error.value = 'Erro ao carregar analistas do time'
    console.error('Erro ao carregar analistas:', err)
  } finally {
    isLoading.value = false
  }
}

async function loadAvailableAnalysts() {
  if (!currentUser.value || !isSupervisor.value) return

  try {
    
    const response = await apiGet<OmegaUser[]>('/api/omega/analysts/available')
    if (response.success && response.data) {
      
      const teamIds = new Set(teamAnalysts.value.map(a => a.id))
      availableAnalysts.value = response.data.filter(a => !teamIds.has(a.id))
    }
  } catch (err) {
    console.error('Erro ao carregar analistas disponíveis:', err)
  }
}

async function addAnalyst(analystId: string) {
  if (!currentUser.value || !isSupervisor.value) return

  isLoading.value = true
  error.value = null

  try {
    const response = await apiPost<OmegaUser>(`/api/omega/teams/${currentUser.value.id}/analysts`, {
      analystId
    })
    
    if (response.success && response.data) {
      teamAnalysts.value.push(response.data)
      await loadAvailableAnalysts()
    } else {
      error.value = response.error || 'Erro ao adicionar analista'
    }
  } catch (err) {
    error.value = 'Erro ao adicionar analista ao time'
    console.error('Erro ao adicionar analista:', err)
  } finally {
    isLoading.value = false
  }
}

async function removeAnalyst(analystId: string) {
  if (!currentUser.value || !isSupervisor.value) return

  isLoading.value = true
  error.value = null

  try {
    const response = await apiPost(`/api/omega/teams/${currentUser.value.id}/analysts/${analystId}/remove`, {})
    
    if (response.success) {
      teamAnalysts.value = teamAnalysts.value.filter(a => a.id !== analystId)
      await loadAvailableAnalysts()
    } else {
      error.value = response.error || 'Erro ao remover analista'
    }
  } catch (err) {
    error.value = 'Erro ao remover analista do time'
    console.error('Erro ao remover analista:', err)
  } finally {
    isLoading.value = false
  }
}

function closeModal() {
  emit('update:open', false)
}

onMounted(() => {
  if (props.open && isSupervisor.value) {
    loadTeamAnalysts()
    loadAvailableAnalysts()
  }
})

watch(() => props.open, (isOpen) => {
  if (isOpen && isSupervisor.value) {
    loadTeamAnalysts()
    loadAvailableAnalysts()
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open && isSupervisor"
      class="omega-team-manager"
      @click.self="closeModal"
    >
      <div class="omega-team-manager__overlay"></div>
      <section class="omega-team-manager__panel">
        <header class="omega-team-manager__header">
          <h3>Gerenciar Analistas do Time</h3>
          <button
            class="omega-icon-btn"
            type="button"
            aria-label="Fechar"
            @click="closeModal"
          >
            <Icon name="x" :size="20" />
          </button>
        </header>

        <div v-if="error" class="omega-team-manager__error">
          {{ error }}
        </div>

        <div class="omega-team-manager__body">
          <section class="omega-team-manager__section">
            <h4>Analistas do Time</h4>
            <div v-if="isLoading" class="omega-team-manager__loading">
              Carregando...
            </div>
            <ul v-else-if="teamAnalysts.length > 0" class="omega-team-manager__list">
              <li
                v-for="analyst in teamAnalysts"
                :key="analyst.id"
                class="omega-team-manager__item"
              >
                <div class="omega-team-manager__item-info">
                  <strong>{{ analyst.name }}</strong>
                  <small>{{ analyst.position || 'Analista' }}</small>
                </div>
                <button
                  class="omega-btn omega-btn--danger"
                  type="button"
                  @click="removeAnalyst(analyst.id)"
                >
                  <Icon name="user-minus" :size="18" />
                  Remover
                </button>
              </li>
            </ul>
            <p v-else class="omega-team-manager__empty">
              Nenhum analista no time ainda.
            </p>
          </section>

          <section class="omega-team-manager__section">
            <h4>Adicionar Analista</h4>
            <ul v-if="availableAnalysts.length > 0" class="omega-team-manager__list">
              <li
                v-for="analyst in availableAnalysts"
                :key="analyst.id"
                class="omega-team-manager__item"
              >
                <div class="omega-team-manager__item-info">
                  <strong>{{ analyst.name }}</strong>
                  <small>{{ analyst.position || 'Analista' }}</small>
                </div>
                <button
                  class="omega-btn omega-btn--primary"
                  type="button"
                  @click="addAnalyst(analyst.id)"
                >
                  <Icon name="user-plus" :size="18" />
                  Adicionar
                </button>
              </li>
            </ul>
            <p v-else class="omega-team-manager__empty">
              Nenhum analista disponível para adicionar.
            </p>
          </section>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.omega-team-manager {
  position: fixed;
  inset: 0;
  z-index: 2600;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 28px;
}

.omega-team-manager__overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 20, 36, 0.4);
  backdrop-filter: blur(2px);
}

.omega-team-manager__panel {
  position: relative;
  z-index: 1;
  width: min(800px, 96vw);
  max-height: 92vh;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(15, 20, 36, 0.12);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.omega-team-manager__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 32px;
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  background: #fafbfc;
}

.omega-team-manager__header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
}

.omega-team-manager__body {
  flex: 1;
  padding: 24px 32px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.omega-team-manager__section h4 {
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f1424;
}

.omega-team-manager__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.omega-team-manager__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px;
  border-radius: 8px;
  background: #fafbfc;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.omega-team-manager__item-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.omega-team-manager__item-info strong {
  font-size: 14px;
  font-weight: 600;
  color: #1e293b;
}

.omega-team-manager__item-info small {
  font-size: 12px;
  color: #64748b;
}

.omega-team-manager__empty {
  margin: 0;
  padding: 24px;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
}

.omega-team-manager__error {
  margin: 16px 32px 0;
  padding: 12px 16px;
  border-radius: 8px;
  background: #fee2e2;
  color: #991b1b;
  font-size: 14px;
}

.omega-team-manager__loading {
  padding: 24px;
  text-align: center;
  color: #64748b;
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

.omega-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: 1px solid transparent;
  border-radius: 8px;
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

.omega-btn--danger {
  background: #fee2e2;
  color: #991b1b;
  border-color: #fecaca;
}

.omega-btn--danger:hover:not(:disabled) {
  background: #fecaca;
  color: #7f1d1d;
}
</style>

