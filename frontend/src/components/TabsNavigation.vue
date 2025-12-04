<script setup lang="ts">
import { computed, h } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import Icon from './Icon.vue'
import PeriodoHeader from './PeriodoHeader.vue'
import type { ViewType, TabConfig } from '../types'

const router = useRouter()
const route = useRoute()

const views: TabConfig[] = [
  { id: 'cards', label: 'Resumo', icon: 'dashboard', ariaLabel: 'Resumo', path: '/' },
  { id: 'table', label: 'Detalhamento', icon: 'list-tree', ariaLabel: 'Detalhamento', path: '/detalhes' },
  { id: 'ranking', label: 'Rankings', icon: 'trophy', ariaLabel: 'Rankings', path: '/ranking' },
  { id: 'exec', label: 'Visão executiva', icon: 'chart-line', ariaLabel: 'Visão executiva', path: '/exec' },
  { id: 'simuladores', label: 'Simuladores', icon: 'calculator', ariaLabel: 'Simuladores', path: '/simuladores' },
  { id: 'campanhas', label: 'Campanhas', icon: 'speakerphone', ariaLabel: 'Campanhas', path: '/campanhas' }
]

const activeView = computed<ViewType>(() => {
  const currentPath = route.path
  const currentView = views.find(v => v.path === currentPath)
  return currentView?.id || 'cards'
})

const handleTabClick = (viewId: ViewType): void => {
  const view = views.find(v => v.id === viewId)
  if (view && view.path) {
    router.push(view.path)
  }
}
</script>

<template>
  <section class="tabs" aria-label="Navegação principal">
    <button
      v-for="view in views"
      :key="view.id"
      class="tab"
      :class="{ 'is-active': activeView === view.id }"
      :data-view="view.id"
      :aria-label="view.ariaLabel"
      @click="handleTabClick(view.id)"
    >
      <span class="tab-icon">
        <Icon 
          :name="view.icon" 
          :size="16" 
          :color="activeView === view.id ? 'white' : undefined" 
        />
      </span>
      <span class="tab-label">{{ view.label }}</span>
    </button>
    <div class="tabs__aside">
      <small class="muted">
        <PeriodoHeader />
      </small>
    </div>
  </section>
</template>

<style scoped>
.tabs {
  display: flex;
  align-items: flex-end;
  gap: 4px;
  margin: 16px 0 0;
  padding: 0 4px;
  border-bottom: 2px solid var(--stroke, #e7eaf2);
  position: relative;
  z-index: 1;
  font-family: var(--brad-font-family, inherit);
}

.tab {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 12px 18px;
  border-radius: 12px 12px 0 0;
  font-weight: var(--brad-font-weight-semibold, 600);
  color: var(--muted, #6b7280);
  border-bottom: 3px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  position: relative;
  z-index: 1;
  font-family: var(--brad-font-family, inherit);
  font-size: 14px;
  line-height: 1.4;
  margin-bottom: -2px;
}

.tab::before {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--brand, #cc092f);
  transform: scaleX(0);
  transition: transform 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  border-radius: 2px 2px 0 0;
}

.tab:hover:not(.is-active) {
  color: var(--text, #0f1424);
  background: var(--brand-xlight, rgba(204, 9, 47, 0.05));
}

.tab:hover:not(.is-active) .tab-icon {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.15));
  color: var(--brand, #cc092f);
  transform: scale(1.05);
}

.tab .tab-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: var(--brand-xlight, rgba(204, 9, 47, 0.1));
  color: var(--brand, #cc092f);
  transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.tab .tab-icon i {
  font-size: 16px;
  line-height: 1;
  display: inline-block !important;
  font-style: normal;
  font-variant: normal;
  text-rendering: auto;
  -webkit-font-smoothing: antialiased;
  visibility: visible !important;
}

.tab .tab-label {
  font-size: 14px;
  font-weight: var(--brad-font-weight-semibold, 600);
  letter-spacing: -0.01em;
}

.tab.is-active {
  color: var(--brand, #cc092f);
  font-weight: var(--brad-font-weight-bold, 700);
  background: var(--panel, #fff);
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.04);
}

.tab.is-active::before {
  transform: scaleX(1);
}

.tab.is-active .tab-icon {
  background: var(--brand, #cc092f);
  color: var(--brad-color-on-bg-primary, #fff);
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.25);
  transform: scale(1);
}

.tab.is-active .tab-icon :deep(svg) {
  color: white !important;
  stroke: white !important;
}

.tab.is-active .tab-label {
  font-weight: var(--brad-font-weight-bold, 700);
}

.tabs__aside {
  margin-left: auto;
  color: var(--muted, #6b7280);
  font-size: 13px;
  padding-bottom: 4px;
  font-family: var(--brad-font-family, inherit);
}

@media (max-width: 900px) {
  .tabs {
    border-bottom: none;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px 12px;
    margin: 20px 0 16px;
    justify-items: center;
    padding: 0;
  }

  .tab {
    border: none;
    border-radius: 16px;
    background: var(--panel, #fff);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 10px;
    padding: 16px 12px;
    margin-bottom: 0;
    width: 100%;
    max-width: 120px;
    transition: all 0.25s cubic-bezier(0.25, 0.1, 0.25, 1);
  }

  .tab::before {
    display: none;
  }

  .tab:hover:not(.is-active) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.12);
  }

  .tab .tab-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: var(--brand-xlight, rgba(204, 9, 47, 0.1));
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
    color: var(--brand, #cc092f);
  }

  .tab .tab-icon i {
    font-size: 24px;
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
  }

  .tab .tab-label {
    font-size: 13px;
    font-weight: var(--brad-font-weight-semibold, 600);
    color: var(--text, #0f1424);
    text-align: center;
    white-space: normal;
    line-height: 1.3;
  }

  .tab.is-active {
    background: var(--panel, #fff);
    box-shadow: 0 8px 20px rgba(204, 9, 47, 0.15);
    transform: translateY(-2px);
  }

  .tab.is-active .tab-icon {
    background: var(--brand, #cc092f);
    color: var(--brad-color-on-bg-primary, #fff);
    box-shadow: 0 8px 20px rgba(204, 9, 47, 0.3);
  }

  .tab.is-active .tab-icon :deep(svg) {
    color: white !important;
    stroke: white !important;
  }

  .tab.is-active .tab-label {
    color: var(--brand, #cc092f);
    font-weight: var(--brad-font-weight-bold, 700);
  }

  .tabs__aside {
    grid-column: 1 / -1;
    justify-self: end;
    padding-bottom: 0;
  }
}

@media (max-width: 640px) {
  .tabs {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px 8px;
    margin: 16px 0 20px;
    padding: 0;
  }

  .tab {
    padding: 12px 8px;
    max-width: 100px;
  }

  .tab .tab-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
  }

  .tab .tab-icon i {
    font-size: 20px;
  }

  .tab .tab-label {
    font-size: 12px;
  }

  .tabs__aside {
    grid-column: 1 / -1;
    justify-self: center;
    font-size: 12px;
  }
}
</style>

<style>
.link-action {
  display: inline-flex !important;
  align-items: center;
  gap: 6px;
  background: var(--panel, #fff) !important;
  border: 1px solid var(--stroke, #e7eaf2) !important;
  color: var(--brand, #cc092f) !important;
  font-size: 13px !important;
  font-weight: var(--brad-font-weight-bold, 700) !important;
  cursor: pointer;
  padding: 8px 14px !important;
  border-radius: 10px !important;
  transition: all 0.2s ease;
  box-sizing: border-box;
  font-family: var(--brad-font-family, inherit) !important;
  outline: none;
  text-decoration: none;
  margin: 0;
  line-height: 1.5;
}

.link-action:hover:not(:disabled) {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08)) !important;
  border-color: var(--brand, #cc092f) !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.15);
}

.link-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.link-action i {
  font-size: 16px;
  line-height: 1;
}

.date-popover {
  position: fixed;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 18px 38px rgba(15, 20, 36, 0.18);
  border: 1px solid rgba(15, 23, 42, 0.12);
  padding: 20px;
  z-index: 1500;
  min-width: 320px;
  max-width: 400px;
}

.date-popover h4 {
  margin: 0 0 16px;
  font-size: 16px;
  font-weight: 700;
  color: var(--text, #0f1424);
}

.date-popover .row {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.date-popover input[type='date'] {
  flex: 1;
  padding: 10px 12px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 10px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.date-popover input[type='date']:focus {
  outline: none;
  border-color: var(--brand, #b30000);
  box-shadow: 0 0 0 3px rgba(204, 9, 47, 0.12);
}

.date-popover .actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.date-popover .btn-sec,
.date-popover .btn-pri {
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  border: 1px solid var(--stroke, #e7eaf2);
}

.date-popover .btn-sec {
  background: #fff;
  color: var(--text, #0f1424);
}

.date-popover .btn-sec:hover {
  background: rgba(0, 0, 0, 0.04);
}

.date-popover .btn-pri {
  background: linear-gradient(90deg, #cc092f 40%, #b81570 90%);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.25);
}

.date-popover .btn-pri:hover {
  background: linear-gradient(90deg, #b81570 40%, #cc092f 90%);
  box-shadow: 0 6px 16px rgba(204, 9, 47, 0.35);
  transform: translateY(-1px);
}
</style>

