<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import SelectInput from '../SelectInput.vue'
import Icon from '../Icon.vue'
import type { FilterOption } from '../../types'
import type { useOmega } from '../../composables/useOmega'

type Props = {
  omega: ReturnType<typeof useOmega>
  showClose?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showClose: true
})

const emit = defineEmits<{
  'close': []
}>()

const notificationOpen = ref(false)
const notificationCount = ref(0)

const userOptions = computed<FilterOption[]>(() => {
  const users = props.omega.users.value
  if (!users || users.length === 0) {
    return []
  }
  return users.map(user => ({
    id: user.id,
    nome: user.name
  }))
})

const currentUserId = computed(() => {
  const userId = props.omega.currentUserId.value
  return userId || ''
})

watch(() => props.omega.users.value, (users) => {
  if (users && users.length > 0 && !currentUserId.value) {
    
    const firstUserId = users[0]?.id
    if (firstUserId) {
      props.omega.setCurrentUserId(firstUserId)
    }
  }
}, { immediate: true, deep: true })

function handleUserChange(userId: string) {
  props.omega.setCurrentUserId(userId)
}

function toggleNotifications() {
  notificationOpen.value = !notificationOpen.value
}
</script>

<template>
  <header class="omega-header">
    <div class="omega-header__left">
      <div class="omega-header__titles">
        <h2 id="omega-title">Central de chamados Omega</h2>
        <p id="omega-subtitle">Registre ocorrências e acompanhe atendimentos.</p>
      </div>
    </div>
    <div class="omega-header__actions">
      <button
        v-if="showClose"
        class="omega-icon-btn omega-header__close"
        type="button"
        aria-label="Fechar central Omega"
        @click="emit('close')"
      >
        <Icon name="x" :size="20" />
      </button>

      <div class="omega-notification-center">
        <button
          id="omega-notifications"
          class="omega-icon-btn"
          type="button"
          title="Notificações"
          :aria-expanded="notificationOpen ? 'true' : 'false'"
          aria-controls="omega-notification-panel"
          @click="toggleNotifications"
        >
          <Icon name="bell" :size="20" />
          <span
            v-if="notificationCount > 0"
            id="omega-notification-badge"
            class="omega-notification-badge"
          >
            {{ notificationCount }}
          </span>
        </button>
        <section
          id="omega-notification-panel"
          class="omega-notification-panel"
          role="dialog"
          aria-modal="false"
          aria-label="Notificações"
          :hidden="!notificationOpen"
        >
          <header class="omega-notification-panel__header">
            <strong>Notificações</strong>
            <button
              id="omega-notification-close"
              class="omega-icon-btn"
              type="button"
              aria-label="Fechar notificações"
              @click="notificationOpen = false"
            >
              <Icon name="x" :size="20" />
            </button>
          </header>
          <div class="omega-notification-panel__body">
            <ul id="omega-notification-list" class="omega-notification-list"></ul>
            <p id="omega-notification-empty" class="omega-notification-empty">
              Nenhuma notificação registrada.
            </p>
          </div>
        </section>
      </div>

      <label class="omega-user-switch" for="omega-user-select">
        <span class="sr-only">Trocar perfil</span>
        <SelectInput
          id="omega-user-select"
          :model-value="currentUserId"
          :options="userOptions"
          placeholder="Selecione um usuário"
          label="Trocar perfil"
          @update:model-value="handleUserChange"
        />
      </label>
    </div>
  </header>
</template>

<style scoped>
.omega-header {
  position: sticky;
  top: 66px; 
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  background: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.omega-header__left {
  flex: 1;
}

.omega-header__titles h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 800;
  color: var(--text, #0f1424);
}

.omega-header__titles p {
  margin: 4px 0 0;
  font-size: 14px;
  color: var(--muted, #6b7280);
}

.omega-header__actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.omega-icon-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  border-radius: 10px;
  color: var(--muted, #6b7280);
  cursor: pointer;
  transition: all 0.2s ease;
}

.omega-icon-btn:hover {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  color: var(--brand, #cc092f);
}

.omega-notification-center {
  position: relative;
}

.omega-notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--brand, #cc092f);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.omega-notification-panel {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 360px;
  max-height: 480px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
  border: 1px solid rgba(148, 163, 184, 0.2);
  z-index: 100;
}

.omega-notification-panel[hidden] {
  display: none;
}

.omega-notification-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.omega-notification-panel__body {
  padding: 8px;
  max-height: 400px;
  overflow-y: auto;
}

.omega-notification-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.omega-notification-empty {
  padding: 24px;
  text-align: center;
  color: var(--muted, #6b7280);
  font-size: 14px;
}

.omega-user-switch {
  display: flex;
  align-items: center;
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
</style>
