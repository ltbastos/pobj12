<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

export interface DetailView {
  id: string
  name: string
  columns: string[]
}

const props = defineProps<{
  views: DetailView[]
  activeViewId: string
}>()

const emit = defineEmits<{
  viewChange: [viewId: string]
}>()

const handleViewClick = (viewId: string) => {
  if (viewId !== props.activeViewId) {
    emit('viewChange', viewId)
  }
}
</script>

<template>
  <div class="detail-view-bar">
    <div class="detail-view-bar__left">
      <span class="detail-view-bar__label">Visões da tabela</span>
      <div class="detail-view-chips">
        <button
          v-for="view in views"
          :key="view.id"
          type="button"
          class="detail-chip"
          :class="{ 'is-active': view.id === activeViewId }"
          @click="handleViewClick(view.id)"
        >
          <span>{{ view.name }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.detail-view-bar {
  margin: 12px 0;
  padding: 10px 14px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: var(--radius, 16px);
  background: var(--bg, #f6f7fc);
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  box-sizing: border-box;
}

.detail-view-bar__left {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1 1 auto;
  min-width: 220px;
}

.detail-view-bar__label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted, #64748b);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.detail-view-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.detail-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid var(--stroke, #e7eaf2);
  background: var(--panel, #ffffff);
  color: var(--text, #0f1424);
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
  font-size: 13px;
  box-sizing: border-box;
}

.detail-chip span {
  pointer-events: none;
}

.detail-chip:hover {
  box-shadow: var(--shadow, 0 12px 28px rgba(17, 23, 41, 0.08));
  transform: translateY(-1px);
}

.detail-chip.is-active {
  background: var(--omega-badge-bg, rgba(36, 107, 253, 0.12));
  border-color: var(--info, #246BFD);
  color: var(--info, #246BFD);
}
</style>

