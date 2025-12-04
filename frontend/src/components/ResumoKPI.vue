<template>
  <div id="kpi-summary" class="kpi-summary">
    <div 
      v-if="summary.indicadoresTotal > 0 || summary.pontosPossiveis > 0"
      class="kpi-pill"
    >
      <div class="kpi-strip__main">
        <span class="kpi-icon"><Icon name="list-check" :size="22" /></span>
        <div class="kpi-strip__text">
          <span class="kpi-strip__label">Indicadores</span>
          <div class="kpi-strip__stats">
            <span class="kpi-stat">
              Atg: <strong>{{ formatDisplay('int', summary.indicadoresAtingidos) }}</strong>
            </span>
            <span class="kpi-stat">
              Total: <strong>{{ formatDisplay('int', summary.indicadoresTotal) }}</strong>
            </span>
          </div>
        </div>
      </div>
      <div 
        class="hitbar"
        :class="hitbarClass(pctIndicadores)"
        role="progressbar"
        :aria-valuenow="pctIndicadores"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="`Atingimento de indicadores: ${pctIndicadores.toFixed(1)}%`"
        :aria-valuetext="`${pctIndicadores.toFixed(1)}% (${summary.indicadoresAtingidos} de ${summary.indicadoresTotal} indicadores)`"
      >
        <span 
          class="hitbar__track"
          :style="{ '--target': `${pctIndicadores.toFixed(2)}%`, '--thumb': `${pctIndicadores.toFixed(2)}%` }"
        >
          <span class="hitbar__fill"></span>
          <span class="hitbar__thumb">
            <span class="hitbar__pct">{{ pctIndicadores.toFixed(1) }}%</span>
          </span>
        </span>
      </div>
    </div>

    <div 
      v-if="summary.pontosPossiveis > 0"
      class="kpi-pill"
    >
      <div class="kpi-strip__main">
        <span class="kpi-icon"><Icon name="medal" :size="22" /></span>
        <div class="kpi-strip__text">
          <span class="kpi-strip__label">Pontos</span>
          <div class="kpi-strip__stats">
            <span class="kpi-stat">
              Atg: <strong>{{ formatDisplay('pts', summary.pontosAtingidos) }}</strong>
            </span>
            <span class="kpi-stat">
              Total: <strong>{{ formatDisplay('pts', summary.pontosPossiveis) }}</strong>
            </span>
          </div>
        </div>
      </div>
      <div 
        class="hitbar"
        :class="hitbarClass(pctPontos)"
        role="progressbar"
        :aria-valuenow="pctPontos"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <span 
          class="hitbar__track"
          :style="{ '--target': `${pctPontos.toFixed(2)}%`, '--thumb': `${pctPontos.toFixed(2)}%` }"
        >
          <span class="hitbar__fill"></span>
          <span class="hitbar__thumb">
            <span class="hitbar__pct">{{ pctPontos.toFixed(1) }}%</span>
          </span>
        </span>
      </div>
    </div>

    <div 
      class="kpi-pill"
    >
      <div class="kpi-strip__main">
        <span class="kpi-icon"><Icon name="cash" :size="22" /></span>
        <div class="kpi-strip__text">
          <span class="kpi-strip__label">
            Variável <span class="kpi-label-emphasis">Estimada</span>
          </span>
          <div class="kpi-strip__stats">
            <span class="kpi-stat">
              Atg: <strong>{{ formatDisplay('brl', summary.varAtingido || 0) }}</strong>
            </span>
            <span class="kpi-stat">
              Total: <strong>{{ formatDisplay('brl', summary.varPossivel || 0) }}</strong>
            </span>
          </div>
        </div>
      </div>
      <div 
        class="hitbar"
        :class="hitbarClass(pctVariavel)"
        role="progressbar"
        :aria-valuenow="pctVariavel"
        :aria-valuetext="`Variável Estimada: ${pctVariavel.toFixed(1)}%`"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        <span 
          class="hitbar__track"
          :style="{
            '--target': `${Math.max(0, Math.min(100, pctVariavel)).toFixed(2)}%`,
            '--thumb': `${Math.max(0, Math.min(100, pctVariavel)).toFixed(2)}%`
          }"
        >
          <span class="hitbar__fill"></span>
          <span class="hitbar__thumb">
            <span class="hitbar__pct">{{ pctVariavel.toFixed(1) }}%</span>
          </span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Icon from './Icon.vue'
import { useResumoSummary } from '../composables/useResumoSummary'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { formatBRLReadable, formatPoints, formatIntReadable } from '../utils/formatUtils'

const { filterState, period } = useGlobalFilters()
const { summary } = useResumoSummary(filterState, period)

const formatDisplay = (type: string, value: number): string => {
  if (type === 'brl') return formatBRLReadable(value)
  if (type === 'pts') return formatPoints(value)
  return formatIntReadable(value)
}

const hitbarClass = (p: number): string => {
  if (p < 50) return 'hitbar--low'
  if (p < 100) return 'hitbar--warn'
  return 'hitbar--ok'
}

const pctIndicadores = computed(() => {
  if (summary.value.indicadoresTotal === 0) return 0
  return (summary.value.indicadoresAtingidos / summary.value.indicadoresTotal) * 100
})

const pctPontos = computed(() => {
  if (summary.value.pontosPossiveis === 0) return 0
  return (summary.value.pontosAtingidos / summary.value.pontosPossiveis) * 100
})

const pctVariavel = computed(() => {
  const varPossivel = summary.value.varPossivel ?? 0
  const varAtingido = summary.value.varAtingido ?? 0
  if (varPossivel === 0 || !Number.isFinite(varPossivel) || !Number.isFinite(varAtingido)) return 0
  const pct = (varAtingido / varPossivel) * 100
  return Math.max(0, Math.min(100, pct))
})
</script>

<style scoped>
.kpi-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin: 8px 0 14px;
  align-items: flex-start;
}

.kpi-pill {
  position: relative;
  overflow: visible;
  background: #fff;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 18px;
  padding: 16px 16px 20px;
  box-shadow: var(--shadow, 0 12px 28px rgba(17, 23, 41, 0.08));
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1 1 360px;
  min-width: 280px;
  transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
  cursor: pointer;
  opacity: 1;
  transform: translateY(0);
}


@media (prefers-reduced-motion: no-preference) {
  .kpi-pill {
    animation: slideInUp 0.5s cubic-bezier(0.25, 0.1, 0.25, 1) forwards;
  }

  .kpi-pill:nth-child(1) { 
    animation-delay: 0.1s; 
  }
  .kpi-pill:nth-child(2) { 
    animation-delay: 0.2s; 
  }
  .kpi-pill:nth-child(3) { 
    animation-delay: 0.3s; 
  }
}

@keyframes slideInUp {
  from {
    opacity: 0.3;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.kpi-pill:hover {
  transform: translateY(-4px) scale(1.01);
  box-shadow: 0 20px 40px rgba(17, 23, 41, 0.15);
  border-color: #d7def1;
}

.kpi-pill:active {
  transform: translateY(-2px) scale(0.99);
}

.kpi-strip__main {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  width: 100%;
  min-width: 0;
}

.kpi-icon {
  font-size: 22px;
  color: var(--brand, #b30000);
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  background: #fff4f5;
  border: 1px solid #ffd7db;
  border-radius: 12px;
  flex-shrink: 0;
}

.kpi-strip__text {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
  flex: 1;
}

.kpi-strip__label {
  font-weight: 800;
  color: #111827;
  font-size: 14px;
  line-height: 1.1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 240px;
}

.kpi-label-emphasis {
  font-weight: 800;
  color: #b30000;
  font-style: italic;
}

.kpi-strip__stats {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 12px;
  align-items: center;
  min-width: 0;
}

.kpi-stat {
  color: #374151;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
}

.kpi-stat strong {
  font-weight: 800;
  color: #111827;
}

.hitbar {
  display: flex;
  align-items: center;
  width: 100%;
  min-width: 0;
  gap: 12px;
}

.hitbar__track {
  position: relative;
  flex: 1 1 auto;
  min-width: 0;
  height: 18px;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
  background: #f8fafc;
  overflow: hidden;
  --bar-anim-duration: 0.85s;
}

.hitbar__fill {
  position: absolute;
  left: 2px;
  top: 3px;
  bottom: 3px;
  width: var(--target, 0%);
  background: #bbf7d0;
  border-radius: 999px;
  transform-origin: left center;
  transition: width 0.8s cubic-bezier(0.25, 0.1, 0.25, 1);
  animation: fillProgress 1s cubic-bezier(0.25, 0.1, 0.25, 1) forwards;
  will-change: transform;
  z-index: 0;
}

@keyframes fillProgress {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}

.hitbar__thumb {
  position: absolute;
  top: 50%;
  left: var(--target, 0%);
  transform: translate(-50%, -50%);
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.1;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
  background: none;
  padding: 0;
  box-shadow: none;
  pointer-events: none;
  z-index: 1;
}

.hitbar__pct {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
}

.hitbar--low .hitbar__fill {
  background: #fecaca;
}

.hitbar--warn .hitbar__fill {
  background: #fed7aa;
}

.hitbar--ok .hitbar__fill {
  background: #bbf7d0;
}

@media (max-width: 640px) {
  .kpi-summary {
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 14px;
    padding-bottom: 6px;
    margin: 0 -16px;
    padding-inline: 16px;
    scroll-snap-type: x mandatory;
  }
  
  .kpi-summary::-webkit-scrollbar {
    display: none;
  }
  
  .kpi-pill {
    min-width: calc(100vw - 72px);
    flex: 0 0 auto;
    scroll-snap-align: start;
  }
}
</style>

