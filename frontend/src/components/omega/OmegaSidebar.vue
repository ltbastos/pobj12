<script setup lang="ts">
import { computed, watch } from 'vue'
import Icon from '../Icon.vue'
import type { useOmega } from '../../composables/useOmega'

type Props = {
  omega: ReturnType<typeof useOmega>
  collapsed: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:collapsed': [value: boolean]
  'nav-click': [viewId: string]
}>()

const currentUser = computed(() => props.omega.currentUser.value)
const navItems = computed(() => {
  const user = props.omega.currentUser.value
  if (!user) return []
  return props.omega.getNavItemsForRole(user.role)
})

watch(() => props.omega.currentUser.value, (user) => {
  if (user) {
  }
}, { immediate: true, deep: true })

watch(() => props.omega.users.value, (users) => {
  if (users && users.length > 0) {
  }
}, { immediate: true, deep: true })

function toggleCollapse() {
  emit('update:collapsed', !props.collapsed)
}

function handleNavClick(viewId: string) {
  emit('nav-click', viewId)
}

function getIconName(iconClass: string): string {
  if (!iconClass) return 'circle'
  
  return iconClass.replace(/^ti ti-/, '')
}
</script>

<template>
  <aside
    class="omega-sidebar"
    :data-collapsed="collapsed ? 'true' : 'false'"
  >
    <button
      class="omega-sidebar__toggle"
      type="button"
      :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'"
      :aria-pressed="collapsed ? 'true' : 'false'"
      :aria-expanded="collapsed ? 'false' : 'true'"
      :title="collapsed ? 'Expandir menu' : 'Recolher menu'"
      @click="toggleCollapse"
    >
      <span class="sr-only">Alternar menu</span>
      <Icon :name="collapsed ? 'chevron-right' : 'chevron-left'" :size="20" aria-hidden="true" />
    </button>

    <section class="omega-profile" aria-label="Perfil selecionado">
      <figure v-if="currentUser?.avatar" class="omega-avatar" aria-hidden="true">
        <img :src="currentUser.avatar" :alt="currentUser.name" loading="lazy" />
      </figure>
      <strong class="omega-profile__name">{{ currentUser?.name || '—' }}</strong>
    </section>

    <nav class="omega-nav" aria-label="Menu Omega">
      <button
        v-for="item in navItems"
        :key="item.id"
        class="omega-nav__item"
        :class="{ 'is-active': omega.currentView.value === item.id }"
        type="button"
        @click="handleNavClick(item.id)"
      >
        <Icon :name="getIconName(item.icon)" :size="20" aria-hidden="true" />
        <span v-if="!collapsed">{{ item.label }}</span>
      </button>
    </nav>
  </aside>
</template>

<style scoped>
.omega-sidebar {
  width: 240px;
  min-width: 240px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  background: #f8fafc;
  border-right: 1px solid rgba(148, 163, 184, 0.2);
  transition: width 0.3s ease, min-width 0.3s ease;
  position: relative;
}

.omega-sidebar[data-collapsed="true"] {
  width: 80px;
  min-width: 80px;
}

.omega-sidebar__toggle {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(148, 163, 184, 0.3);
  background: #fff;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 20;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  color: var(--text, #0f1424);
}

.omega-sidebar__toggle:hover {
  background: var(--brad-color-primary, #cc092f);
  border-color: var(--brad-color-primary, #cc092f);
  color: #fff;
  box-shadow: 0 4px 8px rgba(204, 9, 47, 0.2);
  transform: translateY(-1px);
}

.omega-sidebar__toggle:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(204, 9, 47, 0.15);
}

.omega-sidebar__toggle:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(204, 9, 47, 0.15), 0 2px 4px rgba(0, 0, 0, 0.08);
}

.omega-sidebar[data-collapsed="true"] .omega-sidebar__toggle {
  right: 12px;
  top: 12px;
}

.omega-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 24px 16px;
  border-radius: 20px;
  background: #f8fafc;
  text-align: center;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.16);
}

.omega-avatar {
  width: 88px;
  height: 88px;
  border-radius: 50%;
  overflow: hidden;
  margin: 0;
  box-shadow: 0 14px 28px rgba(204, 9, 47, 0.18);
}

.omega-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.omega-profile__name {
  font-size: 16px;
  font-weight: 800;
  color: var(--text, #0f1424);
}

.omega-sidebar[data-collapsed="true"] .omega-profile__name {
  display: none;
}

.omega-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.omega-nav__item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--brad-color-gray-dark, #999);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  position: relative;
  overflow: hidden;
  text-align: left;
  width: 100%;
}

.omega-nav__item:hover {
  background: rgba(204, 9, 47, 0.05);
  color: var(--brand, #cc092f);
  border-color: rgba(204, 9, 47, 0.1);
}

.omega-nav__item.is-active {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
  color: var(--brand, #cc092f);
  border-color: var(--brand-light, rgba(204, 9, 47, 0.25));
}

.omega-sidebar[data-collapsed="true"] .omega-nav__item span {
  display: none;
}

.omega-sidebar[data-collapsed="true"] .omega-nav__item {
  justify-content: center;
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
