<script setup lang="ts">
import { ref, computed } from 'vue'
import { formatBRL } from '../../utils/formatUtils'
import type { ExecHeatmap } from '../../services/execService'

const props = defineProps<{
  heatmap: ExecHeatmap
}>()

const heatmapMode = ref<'secoes' | 'metas'>('secoes')

const sections = computed(() => {
  // No modo seções, mostrar todas as famílias (excluir apenas META que é usado no modo metas)
  return props.heatmap.sectionsFamilia.filter(s => s.id !== 'META')
})

const hierarchyUnits = computed(() => {
  // No modo metas, mostrar agregados (DIR_ALL, REG_ALL, etc.) e unidades individuais de hierarquia (REG_*, AG_*, GG_*, G_*)
  if (heatmapMode.value === 'metas') {
    return props.heatmap.units.filter(unit => 
      unit.value === 'DIR_ALL' ||
      unit.value === 'REG_ALL' ||
      unit.value === 'AG_ALL' ||
      unit.value === 'GG_ALL' ||
      unit.value === 'G_ALL' ||
      unit.value.startsWith('REG_') || 
      unit.value.startsWith('AG_') || 
      unit.value.startsWith('GG_') || 
      unit.value.startsWith('G_')
    )
  }
  // No modo seções, mostrar todas as unidades retornadas pelo backend (já filtradas corretamente)
  // Excluir apenas as unidades de hierarquia do modo metas
  return props.heatmap.units.filter(unit => 
    unit.value !== 'DIR_ALL' &&
    unit.value !== 'REG_ALL' &&
    unit.value !== 'AG_ALL' &&
    unit.value !== 'GG_ALL' &&
    unit.value !== 'G_ALL' &&
    !unit.value.startsWith('REG_') && 
    !unit.value.startsWith('AG_') && 
    !unit.value.startsWith('GG_') && 
    !unit.value.startsWith('G_')
  )
})

const getData = (unit: string, section: string): { real: number; meta: number } | null => {
  const key = `${unit}|${section}`
  return props.heatmap.dataFamilia[key] || null
}

const getDataMensal = (unit: string, section: string, mes: string): { real: number; meta: number } | null => {
  const key = `${unit}|${section}|${mes}`
  return props.heatmap.dataFamiliaMensal[key] || null
}

const getAtingimentoValue = (unit: string, section: string): { pct: number | null; text: string } => {
  const bucket = getData(unit, section)
  if (!bucket) return { pct: null, text: '—' }
  
  if (bucket.meta > 0) {
    const pct = (bucket.real / bucket.meta) * 100
    return { pct, text: `${Math.round(pct)}%` }
  }
  
  // Se não tem meta mas tem realizado, mostrar o realizado
  if (bucket.real > 0) {
    return { pct: null, text: formatBRL(bucket.real) }
  }
  
  return { pct: null, text: '—' }
}

const getAtingimentoMensal = (unit: string, section: string, mes: string): { pct: number | null; text: string } => {
  const bucket = getDataMensal(unit, section, mes)
  if (!bucket) return { pct: null, text: formatBRL(0) }
  
  if (bucket.meta > 0) {
    const pct = (bucket.real / bucket.meta) * 100
    return { pct, text: `${Math.round(pct)}%` }
  }
  
  return { pct: null, text: formatBRL(bucket.real || 0) }
}

const getVariacaoValue = (unit: string, section: string, monthIndex: number): { delta: number | null; text: string; class: string } => {
  
  if (monthIndex === 0) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const currentMonth = props.heatmap.months[monthIndex]
  const prevMonth = props.heatmap.months[monthIndex - 1]
  
  if (!currentMonth || !prevMonth) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const currentData = getDataMensal(unit, section, currentMonth.key)
  const prevData = getDataMensal(unit, section, prevMonth.key)

  if (!currentData || !prevData) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const currentMeta = currentData.meta
  const prevMeta = prevData.meta

  if (prevMeta === 0) {
    if (currentMeta === 0) {
      return { delta: 0, text: '0.0%', class: 'hm-ok' }
    }
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const delta = ((currentMeta - prevMeta) / prevMeta) * 100

  let className = 'hm-ok'
  if (delta < 0) {
    className = 'hm-down'
  } else if (delta >= 0 && delta <= 5) {
    className = 'hm-ok'
  } else if (delta > 5 && delta <= 10) {
    className = 'hm-warn'
  } else {
    className = 'hm-alert'
  }

  const sign = delta > 0 ? '+' : ''
  return { delta, text: `${sign}${delta.toFixed(1)}%`, class: className }
}

const getDataMetaMensal = (unit: string, mes: string): { real: number; meta: number } | null => {
  // Para o modo de metas, buscar dados agregados usando seção especial "META"
  const key = `${unit}|META|${mes}`
  return props.heatmap.dataFamiliaMensal[key] || null
}

const getVariacaoMetaValue = (unit: string, monthIndex: number): { delta: number | null; text: string; class: string } => {
  if (monthIndex === 0) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const currentMonth = props.heatmap.months[monthIndex]
  const prevMonth = props.heatmap.months[monthIndex - 1]
  
  if (!currentMonth || !prevMonth) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  // Buscar dados agregados diretamente (sem seções)
  const currentData = getDataMetaMensal(unit, currentMonth.key)
  const prevData = getDataMetaMensal(unit, prevMonth.key)

  if (!currentData || !prevData) {
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const currentMeta = currentData.meta
  const prevMeta = prevData.meta

  if (prevMeta === 0) {
    if (currentMeta === 0) {
      return { delta: 0, text: '0.0%', class: 'hm-ok' }
    }
    return { delta: null, text: '—', class: 'hm-empty' }
  }

  const delta = ((currentMeta - prevMeta) / prevMeta) * 100

  let className = 'hm-ok'
  if (delta < 0) {
    className = 'hm-down'
  } else if (delta >= 0 && delta <= 5) {
    className = 'hm-ok'
  } else if (delta > 5 && delta <= 10) {
    className = 'hm-warn'
  } else {
    className = 'hm-alert'
  }

  const sign = delta > 0 ? '+' : ''
  return { delta, text: `${sign}${delta.toFixed(1)}%`, class: className }
}

const getHeatmapCellClass = (pct: number | null): string => {
  if (pct === null) return 'hm-empty'
  if (pct < 50) return 'hm-bad'
  if (pct < 100) return 'hm-warn'
  return 'hm-ok'
}

const getCellTitleMeta = (unit: string, monthIndex: number): string => {
  const unitLabel = props.heatmap.units.find(u => u.value === unit)?.label || unit
  const currentMonth = props.heatmap.months[monthIndex]
  const prevMonth = monthIndex > 0 ? props.heatmap.months[monthIndex - 1] : null
  
  if (!currentMonth) return unitLabel
  
  const currentData = getDataMetaMensal(unit, currentMonth.key)
  const prevData = prevMonth ? getDataMetaMensal(unit, prevMonth.key) : null
  
  const currentMeta = currentData?.meta || 0
  const prevMeta = prevData?.meta || 0
  
  let title = `Meta ${currentMonth.label}: ${formatBRL(currentMeta)}`
  if (prevMonth) {
    title += `\nAnterior (${prevMonth.label}): ${formatBRL(prevMeta)}`
    const variacao = getVariacaoMetaValue(unit, monthIndex)
    if (variacao.delta !== null) {
      title += `\nVariação: ${variacao.text}`
    }
  }
  return title
}

const getCellTitle = (unit: string, section: string, monthIndex?: number, monthKey?: string): string => {
  const unitLabel = props.heatmap.units.find(u => u.value === unit)?.label || unit
  const sectionLabel = sections.value.find(s => s.id === section)?.label || section

  if (monthKey) {
    
    const month = props.heatmap.months.find(m => m.key === monthKey)
    const data = getDataMensal(unit, section, monthKey)
    const atingimento = getAtingimentoMensal(unit, section, monthKey)
    
    let title = `${unitLabel} × ${sectionLabel}`
    if (month) {
      title += ` - ${month.label}`
    }
    title += `\n\nAtingimento: ${atingimento.text}`
    if (data) {
      title += `\nReal: ${formatBRL(data.real || 0)}`
      title += `\nMeta: ${formatBRL(data.meta || 0)}`
    }
    return title
  } else {
    
    const data = getData(unit, section)
    const atingimento = getAtingimentoValue(unit, section)
    let title = `${unitLabel} × ${sectionLabel}\n\n`
    title += `Atingimento: ${atingimento.text}`
    if (data) {
      title += `\nReal: ${formatBRL(data.real || 0)}`
      title += `\nMeta: ${formatBRL(data.meta || 0)}`
    }
    return title
  }
}

const hasData = computed(() => {
  return props.heatmap.sectionsFamilia.length > 0 && (
    Object.keys(props.heatmap.dataFamilia).length > 0 || 
    Object.keys(props.heatmap.dataFamiliaMensal).length > 0
  )
})

const hasMonthlyData = computed(() => {
  return Object.keys(props.heatmap.dataFamiliaMensal).length > 0
})
</script>

<template>
  <div class="exec-panel">
    <div class="exec-h">
      <h3 id="exec-heatmap-title">
        <span v-if="heatmapMode === 'secoes'">Heatmap — GR × Seções</span>
        <span v-else>Heatmap — Variação da meta (mês a mês)</span>
      </h3>
      <div class="exec-controls">
        <div id="exec-heatmap-mode-toggle" class="seg-mini segmented">
          <button 
            class="seg-btn" 
            :class="{ 'is-active': heatmapMode === 'secoes' }"
            @click="heatmapMode = 'secoes'"
            title="Visualizar por Seções"
          >
            Seções
          </button>
          <button 
            class="seg-btn" 
            :class="{ 'is-active': heatmapMode === 'metas' }"
            @click="heatmapMode = 'metas'"
            title="Visualizar Variação de Meta"
          >
            Meta
          </button>
        </div>
      </div>
    </div>

    <div v-if="!hasData" class="hm-empty-state">
      <p>Nenhum dado disponível para os filtros selecionados.</p>
    </div>

    <div v-else id="exec-heatmap" class="exec-heatmap">
      
      <div 
        class="hm-row hm-head" 
        :style="`--hm-cols: ${heatmapMode === 'metas' ? heatmap.months.length : sections.length}; --hm-first: 200px; --hm-cell: ${heatmapMode === 'metas' ? '100px' : '140px'}`"
      >
        <div class="hm-cell hm-corner">
          <span class="hm-corner-label">{{ heatmapMode === 'metas' ? 'Hierarquia' : 'GR \\ Família' }}</span>
          <span class="hm-corner-sublabel">\ {{ heatmapMode === 'metas' ? 'Mês' : 'Seções' }}</span>
        </div>
        <template v-if="heatmapMode === 'metas'">
          <div 
            v-for="month in heatmap.months" 
            :key="month.key"
            class="hm-cell hm-col"
            :title="month.label"
          >
            {{ month.label }}
          </div>
        </template>
        <template v-else>
          <div 
            v-for="section in sections" 
            :key="section.id"
            class="hm-cell hm-col"
            :title="section.label"
          >
            {{ section.label }}
          </div>
          <div 
            v-if="sections.length === 0"
            class="hm-cell hm-col"
          >
            Meta Total
          </div>
        </template>
      </div>
      
      
      <div 
        v-for="unit in hierarchyUnits" 
        :key="unit.value"
        class="hm-row"
        :style="`--hm-cols: ${heatmapMode === 'metas' ? heatmap.months.length : sections.length}; --hm-first: 200px; --hm-cell: ${heatmapMode === 'metas' ? '100px' : '140px'}`"
      >
        <div class="hm-cell hm-rowh" :title="unit.label">
          <span class="hm-rowh-text">{{ unit.label }}</span>
        </div>
        
        <template v-if="heatmapMode === 'metas'">
          <div 
            v-for="(month, monthIndex) in heatmap.months" 
            :key="month.key"
            class="hm-cell hm-val"
            :class="getVariacaoMetaValue(unit.value, monthIndex).class"
            :title="getCellTitleMeta(unit.value, monthIndex)"
          >
            <span class="hm-val-text">{{ getVariacaoMetaValue(unit.value, monthIndex).text }}</span>
          </div>
        </template>
        <template v-else>
          <div 
            v-for="section in sections" 
            :key="section.id"
            class="hm-cell hm-val"
            :class="getHeatmapCellClass(getAtingimentoValue(unit.value, section.id).pct)"
            :title="getCellTitle(unit.value, section.id)"
          >
            <span class="hm-val-text">{{ getAtingimentoValue(unit.value, section.id).text }}</span>
          </div>
        </template>
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
  padding: 24px;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.exec-panel:hover {
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.1);
}

.exec-h {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.exec-h h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
}

.exec-controls {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.exec-heatmap {
  overflow-x: auto;
  border: 1px solid var(--stroke);
  border-radius: 8px;
  background: var(--bg);
}

.hm-empty-state {
  padding: 40px 20px;
  text-align: center;
  color: var(--muted);
  font-size: 14px;
}

.hm-row {
  display: grid;
  grid-template-columns: var(--hm-first) repeat(var(--hm-cols), var(--hm-cell));
  gap: 1px;
  background: var(--stroke);
  min-width: fit-content;
}

.hm-row.hm-head {
  background: var(--bg);
  border-bottom: 2px solid var(--stroke);
  position: sticky;
  top: 0;
  z-index: 10;
}

.hm-cell {
  padding: 12px 10px;
  background: var(--panel);
  font-size: 12px;
  font-weight: 600;
  color: var(--text);
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  transition: all 0.15s ease;
}

.hm-cell.hm-corner {
  text-align: left;
  justify-content: flex-start;
  font-weight: 700;
  color: var(--text);
  background: var(--bg);
  position: sticky;
  left: 0;
  z-index: 5;
  border-right: 2px solid var(--stroke);
}

.hm-corner-label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: var(--text);
}

.hm-corner-sublabel {
  display: block;
  font-size: 10px;
  font-weight: 500;
  color: var(--muted);
  margin-top: 2px;
}

.hm-cell.hm-col {
  font-weight: 700;
  color: var(--text);
  background: var(--bg);
  padding: 10px 8px;
}

.hm-col-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: center;
}

.hm-col-section {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  color: var(--text);
  line-height: 1.2;
}

.hm-col-month {
  font-size: 9px;
  font-weight: 500;
  color: var(--muted);
  line-height: 1.2;
}

.hm-cell.hm-rowh {
  text-align: left;
  justify-content: flex-start;
  font-weight: 600;
  color: var(--text);
  background: var(--bg);
  position: sticky;
  left: 0;
  z-index: 4;
  border-right: 2px solid var(--stroke);
}

.hm-rowh-text {
  font-size: 12px;
  line-height: 1.3;
}

.hm-cell.hm-val {
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.hm-cell.hm-val:hover {
  transform: scale(1.02);
  z-index: 2;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.hm-val-text {
  font-size: 13px;
  font-weight: 700;
  line-height: 1.2;
}

.hm-cell.hm-ok {
  background: linear-gradient(135deg, rgba(187, 247, 208, 0.5) 0%, rgba(187, 247, 208, 0.3) 100%);
  color: #065f46;
  border-left: 3px solid #10b981;
}

.hm-cell.hm-warn {
  background: linear-gradient(135deg, rgba(254, 215, 170, 0.5) 0%, rgba(254, 215, 170, 0.3) 100%);
  color: #92400e;
  border-left: 3px solid #f59e0b;
}

.hm-cell.hm-bad {
  background: linear-gradient(135deg, rgba(254, 202, 202, 0.5) 0%, rgba(254, 202, 202, 0.3) 100%);
  color: #991b1b;
  border-left: 3px solid #ef4444;
}

.hm-cell.hm-down {
  background: linear-gradient(135deg, rgba(191, 219, 254, 0.5) 0%, rgba(191, 219, 254, 0.3) 100%);
  color: #1e40af;
  border-left: 3px solid #3b82f6;
}

.hm-cell.hm-alert {
  background: linear-gradient(135deg, rgba(254, 202, 202, 0.5) 0%, rgba(254, 202, 202, 0.3) 100%);
  color: #991b1b;
  border-left: 3px solid #ef4444;
}

.hm-cell.hm-empty {
  background: var(--bg);
  color: var(--muted);
  font-weight: 400;
}

.seg-mini.segmented {
  padding: 3px;
  border-radius: 8px;
  background: var(--bg);
  display: inline-flex;
  gap: 2px;
  border: 1px solid var(--stroke);
}

.seg-mini .seg-btn {
  padding: 8px 14px;
  font-size: 12px;
  font-weight: 600;
  border: none;
  background: transparent;
  color: var(--muted);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.seg-mini .seg-btn:hover {
  background: rgba(0, 0, 0, 0.03);
  color: var(--text);
}

.seg-mini .seg-btn.is-active {
  background: var(--panel);
  color: var(--text);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  font-weight: 700;
}

@media (max-width: 768px) {
  .exec-h {
    flex-direction: column;
    align-items: stretch;
  }

  .exec-controls {
    width: 100%;
    justify-content: center;
  }

  .hm-cell {
    padding: 8px 6px;
    font-size: 11px;
  }

  .hm-val-text {
    font-size: 11px;
  }
}
</style>
