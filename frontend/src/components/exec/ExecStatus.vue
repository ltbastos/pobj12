<script setup lang="ts">
import { computed } from 'vue'
import { formatBRLReadable } from '../../utils/formatUtils'
import { useGlobalFilters } from '../../composables/useGlobalFilters'

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

type Status = {
  hit: StatusItem[]
  quase: StatusItem[]
  longe: StatusItemLonge[]
}

const props = defineProps<{
  status: Status
}>()

const { filterState } = useGlobalFilters()

const statusTitle = computed(() => {
  const f = filterState.value
  if (f.gerente && f.gerente !== 'Todos') {
    return 'Status dos Gerentes'
  }
  if (f.ggestao && f.ggestao !== 'Todos') {
    return 'Status dos Gerentes de Gestão'
  }
  if (f.agencia && f.agencia !== 'Todas') {
    return 'Status das Agências'
  }
  if (f.gerencia && f.gerencia !== 'Todas') {
    return 'Status das Regionais'
  }
  if (f.diretoria && f.diretoria !== 'Todas') {
    return 'Status das Diretorias'
  }
  return 'Status das Regionais'
})
</script>

<template>
  <div class="exec-panel">
    <div class="exec-h">
      <h3 id="exec-status-title">{{ statusTitle }}</h3>
    </div>
    <div class="exec-status">
      <div class="status-section">
        <h4>Atingidas</h4>
        <div id="exec-status-hit" class="list-mini">
          <template v-for="(item, index) in status.hit" :key="item.key">
            <div class="list-mini__row">
              <div class="list-mini__name">{{ item.label }}</div>
              <div class="list-mini__val">
                <span class="att-badge att-ok">{{ item.p_mens.toFixed(1) }}%</span>
              </div>
            </div>
          </template>
          <div 
            v-for="n in Math.max(0, 5 - status.hit.length)" 
            :key="`empty-hit-${n}`"
            class="list-mini__empty"
          >
            <span>Sem dados disponíveis</span>
            <span>—</span>
          </div>
        </div>
      </div>
      <div class="status-section">
        <h4>Quase lá</h4>
        <div id="exec-status-quase" class="list-mini">
          <template v-for="(item, index) in status.quase" :key="item.key">
            <div class="list-mini__row">
              <div class="list-mini__name">{{ item.label }}</div>
              <div class="list-mini__val">
                <span class="att-badge att-warn">{{ item.p_mens.toFixed(1) }}%</span>
              </div>
            </div>
          </template>
          <div 
            v-for="n in Math.max(0, 5 - status.quase.length)" 
            :key="`empty-quase-${n}`"
            class="list-mini__empty"
          >
            <span>Sem dados disponíveis</span>
            <span>—</span>
          </div>
        </div>
      </div>
      <div class="status-section">
        <h4>Longe</h4>
        <div id="exec-status-longe" class="list-mini">
          <template v-for="(item, index) in status.longe" :key="item.key">
            <div class="list-mini__row">
              <div class="list-mini__name">{{ item.label }}</div>
              <div class="list-mini__val">
                <span class="def-badge def-neg">{{ formatBRLReadable(item.gap || 0) }}</span>
              </div>
            </div>
          </template>
          <div 
            v-for="n in Math.max(0, 5 - status.longe.length)" 
            :key="`empty-longe-${n}`"
            class="list-mini__empty"
          >
            <span>Sem dados disponíveis</span>
            <span>—</span>
          </div>
        </div>
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

.exec-status {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
}

@media (max-width: 1024px) {
  .exec-status {
    grid-template-columns: 1fr;
  }
}

@media (min-width: 1025px) and (max-width: 1400px) {
  .exec-status {
    grid-template-columns: 1fr 1fr;
  }
}

.status-section h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
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

