<script setup lang="ts">
import { formatBRLReadable } from '../../utils/formatUtils'

type StatusItem = {
  key: string
  label: string
  p_mens: number
  gap?: number
}

type StatusItemLonge = {
  key: string
  label: string
  gap: number
}

const props = defineProps<{
  title: string
  items: StatusItem[] | StatusItemLonge[]
  type: 'hit' | 'quase' | 'longe'
}>()
</script>

<template>
  <div class="exec-panel">
    <div class="exec-h">
      <h3>{{ title }}</h3>
    </div>
    <div id="exec-status-list" class="list-mini">
      <template v-for="(item, index) in items" :key="item.key">
        <div class="list-mini__row">
          <div class="list-mini__name">{{ item.label }}</div>
          <div class="list-mini__val">
            <span v-if="type === 'longe'" class="def-badge def-neg">
              {{ formatBRLReadable((item as StatusItemLonge).gap || 0) }}
            </span>
            <span v-else-if="type === 'hit'" class="att-badge att-ok">
              {{ (item as StatusItem).p_mens.toFixed(1) }}%
            </span>
            <span v-else class="att-badge att-warn">
              {{ (item as StatusItem).p_mens.toFixed(1) }}%
            </span>
          </div>
        </div>
      </template>
      <div 
        v-for="n in Math.max(0, 5 - items.length)" 
        :key="`empty-${n}`"
        class="list-mini__empty"
      >
        <span>Sem dados disponíveis</span>
        <span>—</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.exec-panel {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.exec-panel:hover {
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.1);
}

.exec-h {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.exec-h h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
}

.list-mini {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.list-mini__row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 10px;
  transition: background 0.2s ease, transform 0.15s ease;
  cursor: pointer;
}

.list-mini__row:hover {
  background: var(--bg);
  transform: translateX(2px);
}

.list-mini__name {
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.list-mini__val {
  flex-shrink: 0;
}

.att-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.att-badge.att-ok {
  background: rgba(187, 247, 208, 0.3);
  color: #065f46;
}

.att-badge.att-warn {
  background: rgba(254, 215, 170, 0.3);
  color: #92400e;
}

.def-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.def-badge.def-neg {
  background: rgba(254, 202, 202, 0.3);
  color: #991b1b;
}

.list-mini__empty {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  color: var(--muted);
  font-size: 13px;
}
</style>

