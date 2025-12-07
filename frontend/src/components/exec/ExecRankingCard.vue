<script setup lang="ts">
import { computed } from 'vue'
import { formatBRLReadable, formatBRL } from '../../utils/formatUtils'
import { useGlobalFilters } from '../../composables/useGlobalFilters'

type RankingItem = {
  key: string
  label: string
  p_mens: number
  real_mens: number
  meta_mens: number
}

const props = defineProps<{
  title: string
  items: RankingItem[]
}>()

const pctBadgeClass = (p: number): string => {
  if (p < 50) return 'att-low'
  if (p < 100) return 'att-warn'
  return 'att-ok'
}
</script>

<template>
  <div class="exec-panel">
    <div class="exec-h">
      <h3>{{ title }}</h3>
    </div>
    <div class="rank-mini">
      <template v-for="(item, index) in items" :key="item.key">
        <div class="rank-mini__row">
          <div class="rank-mini__name">
            <span class="rank-mini__label">{{ item.label }}</span>
          </div>
          <div class="rank-mini__bar">
            <span :style="{ width: `${Math.min(100, Math.max(0, item.p_mens))}%` }"></span>
          </div>
          <div class="rank-mini__pct">
            <span class="att-badge" :class="pctBadgeClass(item.p_mens)">{{ item.p_mens.toFixed(1) }}%</span>
          </div>
          <div class="rank-mini__vals">
            <strong :title="formatBRL(item.real_mens)">{{ formatBRLReadable(item.real_mens) }}</strong>
            <small :title="formatBRL(item.meta_mens)">/ {{ formatBRLReadable(item.meta_mens) }}</small>
          </div>
        </div>
      </template>
      <div 
        v-for="n in Math.max(0, 5 - items.length)" 
        :key="`empty-${n}`"
        class="rank-mini__empty"
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

.rank-mini {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.rank-mini__row {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  gap: 12px;
  align-items: center;
  padding: 12px;
  border-radius: 10px;
  transition: background 0.2s ease, transform 0.15s ease;
  cursor: pointer;
}

.rank-mini__row:hover {
  background: var(--bg);
  transform: translateX(2px);
}

.rank-mini__name {
  min-width: 0;
}

.rank-mini__label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.rank-mini__bar {
  width: 100px;
  height: 8px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  overflow: hidden;
}

.rank-mini__bar span {
  display: block;
  height: 100%;
  background: var(--info);
  border-radius: 999px;
  transition: width 0.3s ease;
}

.rank-mini__pct {
  min-width: 60px;
  text-align: right;
}

.rank-mini__vals {
  min-width: 120px;
  text-align: right;
  font-size: 12px;
  color: var(--text);
}

.rank-mini__vals strong {
  font-weight: 600;
}

.rank-mini__vals small {
  color: var(--muted);
}

.att-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.att-badge.att-low {
  background: rgba(254, 202, 202, 0.3);
  color: #991b1b;
}

.att-badge.att-warn {
  background: rgba(254, 215, 170, 0.3);
  color: #92400e;
}

.att-badge.att-ok {
  background: rgba(187, 247, 208, 0.3);
  color: #065f46;
}

.rank-mini__empty {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  color: var(--muted);
  font-size: 13px;
}
</style>

