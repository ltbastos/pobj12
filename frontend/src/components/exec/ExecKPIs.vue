<script setup lang="ts">
import { computed } from 'vue'
import { formatBRLReadable, formatBRL } from '../../utils/formatUtils'
import { useBusinessDays } from '../../composables/useBusinessDays'

type KPIs = {
  real_mens: number
  meta_mens: number
  real_acum: number
  meta_acum: number
}

const props = defineProps<{
  kpis: KPIs
}>()

const { getCurrentMonthBusinessSnapshot } = useBusinessDays()

const atingimento = computed(() => {
  if (props.kpis.meta_mens === 0) return 0
  return (props.kpis.real_mens / props.kpis.meta_mens) * 100
})

const defasagem = computed(() => {
  return props.kpis.meta_mens - props.kpis.real_mens
})

const forecast = computed(() => {
  const snapshot = getCurrentMonthBusinessSnapshot.value
  const { total: diasTotais, elapsed: diasDecorridos } = snapshot
  
  if (diasDecorridos <= 0) {
    return props.kpis.real_mens
  }
  
  const mediaDiariaAtual = props.kpis.real_mens / diasDecorridos
  const projecaoRitmoAtual = mediaDiariaAtual * diasTotais
  
  return projecaoRitmoAtual
})

const forecastPct = computed(() => {
  if (props.kpis.meta_mens === 0) return 0
  return (forecast.value / props.kpis.meta_mens) * 100
})

const pctBadgeClass = (p: number): string => {
  if (p < 50) return 'att-low'
  if (p < 100) return 'att-warn'
  return 'att-ok'
}

const moneyBadgeClass = (v: number): string => {
  return v >= 0 ? 'def-pos' : 'def-neg'
}
</script>

<template>
  <div id="exec-kpis" class="exec-kpis">
    <div class="kpi-card">
      <div class="kpi-card__title">Atingimento mensal</div>
      <div class="kpi-card__value">
        <span :title="formatBRL(kpis.real_mens)">{{ formatBRLReadable(kpis.real_mens) }}</span>
        <small>/ <span :title="formatBRL(kpis.meta_mens)">{{ formatBRLReadable(kpis.meta_mens) }}</span></small>
      </div>
      <div class="kpi-card__bar">
        <div 
          class="kpi-card__fill" 
          :class="pctBadgeClass(atingimento)"
          :style="{ width: `${Math.min(100, Math.max(0, atingimento))}%` }"
        ></div>
      </div>
      <div class="kpi-card__pct">
        <span class="att-badge" :class="pctBadgeClass(atingimento)">{{ atingimento.toFixed(1) }}%</span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-card__title">Defasagem</div>
      <div class="kpi-card__value" :class="moneyBadgeClass(defasagem)" :title="formatBRL(defasagem)">
        {{ formatBRLReadable(defasagem) }}
      </div>
      <div class="kpi-sub muted">Meta – Realizado</div>
    </div>

    <div class="kpi-card">
      <div class="kpi-card__title">Forecast</div>
      <div class="kpi-card__value">
        <span :title="formatBRL(forecast)">{{ formatBRLReadable(forecast) }}</span>
        <small>/ <span :title="formatBRL(kpis.meta_mens)">{{ formatBRLReadable(kpis.meta_mens) }}</span></small>
      </div>
      <div class="kpi-card__bar">
        <div 
          class="kpi-card__fill" 
          :class="pctBadgeClass(forecastPct)"
          :style="{ width: `${Math.min(100, Math.max(0, forecastPct))}%` }"
        ></div>
      </div>
      <div class="kpi-card__pct">
        <span class="att-badge" :class="pctBadgeClass(forecastPct)">{{ forecastPct.toFixed(1) }}%</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.exec-kpis {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 18px;
}

.kpi-card {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
  border-color: #d7def1;
}

.kpi-card__title {
  font-size: 13px;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-card__value {
  font-size: 24px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
}

.kpi-card__value small {
  font-size: 16px;
  font-weight: 500;
  color: var(--muted);
}

.kpi-card__bar {
  width: 100%;
  height: 10px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  overflow: hidden;
}

.kpi-card__fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.3s ease;
}

.kpi-card__fill.att-low {
  background: #fecaca;
}

.kpi-card__fill.att-warn {
  background: #fed7aa;
}

.kpi-card__fill.att-ok {
  background: #bbf7d0;
}

.kpi-card__pct {
  display: flex;
  align-items: center;
}

.kpi-sub {
  font-size: 12px;
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

.kpi-card__value.def-pos {
  color: #065f46;
}

.kpi-card__value.def-neg {
  color: #991b1b;
}

.muted {
  color: var(--muted);
}
</style>

