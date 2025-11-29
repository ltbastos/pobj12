<script setup lang="ts">
import { ref } from 'vue'

const notificationsOpen = ref(false)
const userMenuOpen = ref(false)
const submenuOpen = ref<string | null>(null)

const toggleNotifications = (): void => {
  notificationsOpen.value = !notificationsOpen.value
}

const toggleUserMenu = (): void => {
  userMenuOpen.value = !userMenuOpen.value
}

const toggleSubmenu = (submenu: string): void => {
  submenuOpen.value = submenuOpen.value === submenu ? null : submenu
}

const openOmegaModal = (): void => {
  if (typeof window === 'undefined') return
  const globalAny = window as any
  const opener =
    globalAny.__openOmegaFromVue ||
    globalAny.openOmegaModule ||
    globalAny.openOmega ||
    globalAny.launchOmegaStandalone

  if (typeof opener === 'function') {
    opener()
  } else {
    console.warn('Módulo Omega não está disponível.')
  }
}

const handleMenuAction = async (action: string): Promise<void> => {
  if (action === 'omega') {
    openOmegaModal()
    userMenuOpen.value = false
    return
  }

  if (action === 'logout') {
    console.log('Logout')
  }

  console.log('Menu action:', action)
  userMenuOpen.value = false
}
</script>

<template>
  <header class="topbar">
    <div class="topbar__left">
      <div class="logo">
        <span class="logo__mark" aria-hidden="true"></span>
        <span class="logo__text">Bradesco</span>
      </div>
    </div>

    <div class="topbar__right">
      <div class="topbar__notifications">
        <button
          id="btn-topbar-notifications"
          class="topbar__bell"
          type="button"
          aria-haspopup="true"
          :aria-expanded="notificationsOpen"
          aria-controls="topbar-notification-panel"
          @click="toggleNotifications"
        >
          <i class="ti ti-bell" aria-hidden="true"></i>
          <span id="topbar-notification-badge" class="topbar__badge" hidden>0</span>
          <span class="sr-only">Abrir notificações</span>
        </button>
        <div
          id="topbar-notification-panel"
          class="topbar-notification-panel"
          role="menu"
          :aria-hidden="!notificationsOpen"
          :hidden="!notificationsOpen"
        >
          <p class="topbar-notification-panel__empty">Nenhuma notificação no momento.</p>
        </div>
      </div>
      <div class="userbox">
        <button
          class="userbox__trigger"
          id="btn-user-menu"
          type="button"
          aria-haspopup="true"
          :aria-expanded="userMenuOpen"
          @click="toggleUserMenu"
        >
          <img
            class="userbox__avatar"
            src="https://i.pravatar.cc/80?img=12"
            alt="Foto do usuário"
          />
          <span class="userbox__name">X Burguer</span>
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div
          class="userbox__menu"
          :class="{ 'is-open': userMenuOpen }"
          id="user-menu"
          role="menu"
          :aria-hidden="!userMenuOpen"
          :hidden="!userMenuOpen"
        >
          <span class="userbox__menu-title">Links úteis</span>
          <button
            class="userbox__menu-item"
            type="button"
            @click="handleMenuAction('portal')"
          >
            Portal PJ
          </button>
          <div class="userbox__submenu">
            <button
              class="userbox__menu-item userbox__menu-item--has-sub"
              type="button"
              :aria-expanded="submenuOpen === 'manuais'"
              @click="toggleSubmenu('manuais')"
            >
              Manuais
              <i class="ti ti-chevron-right" aria-hidden="true"></i>
            </button>
            <div
              class="userbox__submenu-list"
              :class="{ 'is-open': submenuOpen === 'manuais' }"
              id="user-submenu-manuais"
              :hidden="submenuOpen !== 'manuais'"
            >
              <button
                class="userbox__menu-item"
                type="button"
                @click="handleMenuAction('manual1')"
              >
                Manual 1
              </button>
              <button
                class="userbox__menu-item"
                type="button"
                @click="handleMenuAction('manual2')"
              >
                Manual 2
              </button>
            </div>
          </div>
          <button
            class="userbox__menu-item"
            type="button"
            @click="handleMenuAction('mapao')"
          >
            Mapão de Oportunidades
          </button>
          <button
            class="userbox__menu-item"
            type="button"
            data-action="omega"
            @click="handleMenuAction('omega')"
          >
            Omega
          </button>
          <hr class="userbox__divider" />
          <button
            class="userbox__menu-item userbox__menu-item--logout"
            type="button"
            @click="handleMenuAction('logout')"
          >
            <i class="ti ti-logout-2" aria-hidden="true"></i>
            <span>Sair</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1100;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 8px 12px;
  background: var(--brad-color-primary-gradient);
  color: var(--brad-color-on-bg-primary);
  box-shadow: 0 6px 16px rgba(204, 9, 47, 0.25);
  margin: 0;
  border: none;
}

.topbar__left,
.topbar__right {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.topbar__notifications {
  position: relative;
  display: flex;
  align-items: center;
}

.topbar__bell {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1.5px solid rgba(255, 255, 255, 1);
  background: transparent;
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease;
}

.topbar__bell:hover,
.topbar__bell:focus-visible {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 1);
  outline: none;
}

.topbar__bell i {
  font-size: 18px;
  stroke-width: 1.5;
  color: #fff;
}

.topbar__badge {
  position: absolute;
  top: 6px;
  right: 6px;
  transform: translate(40%, -40%);
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: #f87171;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  box-shadow: 0 0 0 2px rgba(204, 9, 47, 0.35);
}

.topbar__badge[hidden] {
  display: none;
}

.topbar-notification-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 260px;
  max-height: 320px;
  background: #fff;
  color: #1f2937;
  border-radius: 14px;
  box-shadow: 0 18px 38px rgba(15, 20, 36, 0.18);
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 1500;
}

.topbar-notification-panel[aria-hidden='true'] {
  display: none;
}

.topbar-notification-panel__empty {
  margin: 0;
  font-size: 13px;
  color: #475569;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.logo__mark {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #fff;
  background-image: url('/img/bra-logo.png');
  background-size: 70%;
  background-position: center;
  background-repeat: no-repeat;
  flex: 0 0 28px;
}

.logo__text {
  font-weight: 800;
  color: #fff;
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.userbox {
  position: relative;
  display: flex;
  align-items: center;
  min-width: 0;
}

.userbox__trigger {
  display: flex;
  align-items: center;
  gap: 10px;
  background: transparent;
  border: 1.5px solid rgba(255, 255, 255, 1);
  border-radius: 999px;
  padding: 6px 12px;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease;
}

.userbox__trigger:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 1);
}

.userbox__trigger:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.85);
  outline-offset: 2px;
}

.userbox__trigger i {
  font-size: 16px;
  stroke-width: 1.5;
  color: #fff;
}

.userbox__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 1);
  object-fit: cover;
  flex: 0 0 36px;
}

.userbox__name {
  font-family: var(--brad-font-family);
  font-size: 14px;
  font-weight: var(--brad-font-weight-semibold);
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 140px;
}

.userbox__menu {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  background: #fff;
  color: #0f1724;
  border-radius: 14px;
  box-shadow: 0 18px 38px rgba(15, 20, 36, 0.18);
  padding: 12px;
  min-width: 220px;
  display: none;
  z-index: 1400;
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.userbox__menu.is-open {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.userbox__menu-title {
  font-size: 12px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.userbox__menu-item {
  border: none;
  background: transparent;
  color: #0f1724;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 10px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.userbox__menu-item:hover,
.userbox__menu-item:focus-visible {
  background: rgba(204, 9, 47, 0.12);
  color: var(--brand);
  outline: none;
}

.userbox__menu-item--logout {
  color: #b30000;
}

.userbox__menu-item--logout i {
  margin-right: 4px;
}

.userbox__divider {
  width: 100%;
  height: 1px;
  border: none;
  background: #e5e7eb;
  margin: 6px 0;
}

.userbox__submenu {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.userbox__menu-item--has-sub {
  justify-content: space-between;
}

.userbox__menu-item--has-sub i {
  transition: transform 0.2s ease;
}

.userbox__menu-item--has-sub[aria-expanded='true'] i {
  transform: rotate(90deg);
}

.userbox__submenu-list {
  display: none;
  flex-direction: column;
  gap: 4px;
  padding-left: 12px;
}

.userbox__submenu-list.is-open {
  display: flex;
}

.userbox__menu button {
  font-family: inherit;
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

