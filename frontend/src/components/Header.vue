<script setup lang="ts">
import { ref, onMounted, onUnmounted, inject } from 'vue'
import Icon from './Icon.vue'

const userMenuOpen = ref(false)
const submenuOpen = ref<string | null>(null)
const userboxRef = ref<HTMLElement | null>(null)

const toggleUserMenu = (): void => {
  userMenuOpen.value = !userMenuOpen.value
  if (!userMenuOpen.value) {
    submenuOpen.value = null
  }
}

const closeUserMenu = (): void => {
  userMenuOpen.value = false
  submenuOpen.value = null
}

const toggleSubmenu = (submenu: string): void => {
  submenuOpen.value = submenuOpen.value === submenu ? null : submenu
}

const useClickOutside = (callback: () => void) => {
  const handleClick = (event: MouseEvent) => {
    if (userboxRef.value && !userboxRef.value.contains(event.target as Node)) {
      callback()
    }
  }
  
  onMounted(() => {
    document.addEventListener('click', handleClick)
  })
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClick)
  })
}

const useEscape = (callback: () => void) => {
  const handleKey = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
      callback()
    }
  }
  
  onMounted(() => {
    document.addEventListener('keydown', handleKey)
  })
  
  onUnmounted(() => {
    document.removeEventListener('keydown', handleKey)
  })
}

useClickOutside(() => {
  closeUserMenu()
})

useEscape(() => {
  if (userMenuOpen.value) {
    closeUserMenu()
  }
})

const handleMenuAction = async (action: string): Promise<void> => {
  if (action === 'omega') {
    const omegaUrl = `${window.location.origin}${window.location.pathname.replace(/\/[^/]*$/, '')}/omega`
    window.open(omegaUrl, '_blank')
    userMenuOpen.value = false
    return
  }

  if (action === 'logout') {
  }

  userMenuOpen.value = false
}
</script>

<template>
  <header class="topbar" role="banner">
    <nav id="main-navigation" class="topbar__left" role="navigation" aria-label="Navegação principal">
      <a href="/" class="logo" aria-label="Bradesco - Página inicial">
        <span class="logo__mark" aria-hidden="true"></span>
        <span class="logo__text">Bradesco</span>
      </a>
    </nav>

    <nav class="topbar__right" role="navigation" aria-label="Menu do usuário">
      <div ref="userboxRef" class="userbox">
        <button
          class="userbox__trigger"
          id="btn-user-menu"
          type="button"
          aria-haspopup="true"
          :aria-expanded="userMenuOpen"
          aria-controls="user-menu"
          aria-label="Menu do usuário: João da Silva"
          @click="toggleUserMenu"
          @keydown.escape="userMenuOpen && toggleUserMenu()"
        >
          <img
            class="userbox__avatar"
            src="https://i.pravatar.cc/80?img=12"
            alt="Foto do usuário João da Silva"
          />
          <span class="userbox__name">João da Silva</span>
          <Icon 
            name="chevron-down"
            :size="16" 
            class="userbox__chevron" 
            :class="{ 'is-open': userMenuOpen }"
          />
        </button>
        <Transition name="dropdown">
          <div
            v-if="userMenuOpen"
            class="userbox__menu"
            id="user-menu"
            role="menu"
            :aria-hidden="!userMenuOpen"
            aria-label="Menu do usuário"
            @keydown.escape="toggleUserMenu()"
          >
          <div class="userbox__menu-header">
            <span class="userbox__menu-title" role="heading" aria-level="2">Links úteis</span>
          </div>
          <button
            class="userbox__menu-item"
            type="button"
            role="menuitem"
            @click="handleMenuAction('portal')"
          >
            Portal PJ
          </button>
          <div class="userbox__submenu">
            <button
              class="userbox__menu-item userbox__menu-item--has-sub"
              type="button"
              role="menuitem"
              :aria-expanded="submenuOpen === 'manuais'"
              aria-controls="user-submenu-manuais"
              @click="toggleSubmenu('manuais')"
              @keydown.enter.prevent="toggleSubmenu('manuais')"
              @keydown.space.prevent="toggleSubmenu('manuais')"
            >
              Manuais
              <Icon name="chevron-right" :size="16" />
            </button>
            <Transition name="submenu">
              <div
                v-if="submenuOpen === 'manuais'"
                class="userbox__submenu-list"
                id="user-submenu-manuais"
                role="menu"
                aria-label="Submenu Manuais"
              >
              <button
                class="userbox__menu-item"
                type="button"
                role="menuitem"
                @click="handleMenuAction('manual1')"
              >
                Manual 1
              </button>
              <button
                class="userbox__menu-item"
                type="button"
                role="menuitem"
                @click="handleMenuAction('manual2')"
              >
                Manual 2
              </button>
              </div>
            </Transition>
          </div>
          <button
            class="userbox__menu-item"
            type="button"
            role="menuitem"
            @click="handleMenuAction('mapao')"
          >
            Mapão de Oportunidades
          </button>
          <button
            class="userbox__menu-item"
            type="button"
            role="menuitem"
            data-action="omega"
            @click="handleMenuAction('omega')"
          >
            Omega
          </button>
          <hr class="userbox__divider" role="separator" aria-orientation="horizontal" />
          <button
            class="userbox__menu-item userbox__menu-item--logout"
            type="button"
            role="menuitem"
            aria-label="Sair da aplicação"
            @click="handleMenuAction('logout')"
          >
            <Icon name="logout-2" :size="16" />
            <span>Sair</span>
          </button>
          </div>
        </Transition>
      </div>
    </nav>
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
  padding: 10px 16px;
  background: var(--brad-color-primary-gradient);
  color: var(--brad-color-on-bg-primary);
  box-shadow: 0 4px 20px rgba(204, 9, 47, 0.2), 0 2px 8px rgba(204, 9, 47, 0.15);
  margin: 0;
  border: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  animation: slideInDown 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
  backdrop-filter: blur(10px);
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.topbar__left,
.topbar__right {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.dropdown-enter-active {
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.dropdown-leave-active {
  transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
  filter: blur(4px);
}

.dropdown-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
  filter: blur(0);
}

.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
  filter: blur(0);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.96);
  filter: blur(4px);
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  transition: transform 0.2s ease;
  text-decoration: none;
}

.logo:hover {
  transform: scale(1.02);
}

.logo__mark {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #fff;
  background-image: url('/img/bra-logo.png');
  background-size: 70%;
  background-position: center;
  background-repeat: no-repeat;
  flex: 0 0 32px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.logo:hover .logo__mark {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: scale(1.05);
}

.logo__text {
  font-weight: 800;
  color: #fff;
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 16px;
  letter-spacing: -0.01em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
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
  background: rgba(255, 255, 255, 0.1);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  border-radius: 999px;
  padding: 6px 14px 6px 6px;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  transform: scale(1);
  backdrop-filter: blur(10px);
}

.userbox__trigger:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.02);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.userbox__trigger:active {
  transform: scale(0.98);
}

.userbox__trigger[aria-expanded='true'] {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.6);
}

.userbox__trigger:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.85);
  outline-offset: 2px;
}

.userbox__chevron {
  font-size: 16px;
  stroke-width: 1.5;
  color: #fff;
  transition: transform 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
  flex-shrink: 0;
}

.userbox__chevron.is-open {
  transform: rotate(180deg);
}

.userbox__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.9);
  object-fit: cover;
  flex: 0 0 36px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: all 0.2s ease;
}

.userbox__trigger:hover .userbox__avatar {
  border-color: rgba(255, 255, 255, 1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
  box-shadow: 0 18px 38px rgba(15, 20, 36, 0.18), 0 4px 12px rgba(15, 20, 36, 0.08);
  padding: 8px;
  min-width: 240px;
  max-width: 320px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  z-index: 1400;
  border: 1px solid rgba(15, 23, 42, 0.08);
  transform-origin: top right;
  backdrop-filter: blur(10px);
}

.userbox__menu-header {
  padding: 8px 10px 4px;
  margin-bottom: 4px;
}

.userbox__menu-title {
  font-size: 11px;
  font-weight: 800;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.userbox__menu-item {
  border: none;
  background: transparent;
  color: #0f1724;
  font-size: 13px;
  font-weight: 600;
  padding: 10px 12px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
  transform: translateX(0);
  text-align: left;
  width: 100%;
  position: relative;
}

.userbox__menu-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%) scaleX(0);
  width: 3px;
  height: 0;
  background: var(--brand, #cc092f);
  border-radius: 0 2px 2px 0;
  transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.userbox__menu-item:hover,
.userbox__menu-item:focus-visible {
  background: rgba(204, 9, 47, 0.08);
  color: var(--brand, #cc092f);
  outline: none;
  transform: translateX(2px);
}

.userbox__menu-item:hover::before,
.userbox__menu-item:focus-visible::before {
  transform: translateY(-50%) scaleX(1);
  height: 60%;
}

.userbox__menu-item--logout {
  color: #b30000;
  margin-top: 4px;
}

.userbox__menu-item--logout:hover,
.userbox__menu-item--logout:focus-visible {
  background: rgba(179, 0, 0, 0.1);
  color: #8f0000;
}

.userbox__menu-item--logout i {
  margin-right: 4px;
}

.userbox__divider {
  width: calc(100% - 16px);
  height: 1px;
  border: none;
  background: linear-gradient(to right, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
  margin: 8px auto;
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
  transition: transform 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  font-size: 14px;
  color: #64748b;
}

.userbox__menu-item--has-sub[aria-expanded='true'] {
  background: rgba(204, 9, 47, 0.05);
}

.userbox__menu-item--has-sub[aria-expanded='true'] i {
  transform: rotate(90deg);
  color: var(--brand, #cc092f);
}

.userbox__submenu-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding-left: 16px;
  margin-top: 4px;
  margin-bottom: 4px;
  overflow: hidden;
  border-left: 2px solid rgba(204, 9, 47, 0.1);
  padding-top: 4px;
  padding-bottom: 4px;
}

.submenu-enter-active {
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.submenu-leave-active {
  transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.submenu-enter-from {
  opacity: 0;
  max-height: 0;
  transform: translateX(-8px);
  margin-top: 0;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.submenu-enter-to {
  opacity: 1;
  max-height: 200px;
  transform: translateX(0);
}

.submenu-leave-from {
  opacity: 1;
  max-height: 200px;
  transform: translateX(0);
}

.submenu-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateX(-8px);
  margin-top: 0;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.userbox__menu button {
  font-family: inherit;
}

@media (max-width: 640px) {
  .topbar {
    padding: 8px 12px;
  }

  .logo__text {
    display: none;
  }

  .userbox__name {
    display: none;
  }

  .userbox__trigger {
    padding: 6px;
  }

  .userbox__menu {
    right: -8px;
    min-width: 200px;
    max-width: calc(100vw - 32px);
  }
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

