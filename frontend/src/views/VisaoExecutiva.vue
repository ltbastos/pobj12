<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { formatBRLReadable, formatBRL, formatDate } from '../utils/formatUtils'
import Filters from '../components/Filters.vue'
import TabsNavigation from '../components/TabsNavigation.vue'

const { filterState, period } = useGlobalFilters()

// Dados mock
const mockKPIs = ref({
  real_mens: 125000000,
  meta_mens: 150000000,
  real_acum: 125000000,
  meta_acum: 150000000
})

const mockRanking = ref([
  { key: 'gr-001', label: 'Regional Sudeste', real_mens: 45000000, meta_mens: 40000000, p_mens: 112.5 },
  { key: 'gr-002', label: 'Regional Sul', real_mens: 38000000, meta_mens: 35000000, p_mens: 108.6 },
  { key: 'gr-003', label: 'Regional Norte', real_mens: 32000000, meta_mens: 30000000, p_mens: 106.7 },
  { key: 'gr-004', label: 'Regional Centro-Oeste', real_mens: 28000000, meta_mens: 28000000, p_mens: 100.0 },
  { key: 'gr-005', label: 'Regional Nordeste', real_mens: 25000000, meta_mens: 27000000, p_mens: 92.6 },
  { key: 'gr-006', label: 'Regional Leste', real_mens: 22000000, meta_mens: 25000000, p_mens: 88.0 },
  { key: 'gr-007', label: 'Regional Oeste', real_mens: 18000000, meta_mens: 23000000, p_mens: 78.3 },
  { key: 'gr-008', label: 'Regional Centro', real_mens: 15000000, meta_mens: 20000000, p_mens: 75.0 }
])

const mockStatus = ref({
  hit: [
    { key: 'gr-001', label: 'Regional Sudeste', p_mens: 112.5 },
    { key: 'gr-002', label: 'Regional Sul', p_mens: 108.6 },
    { key: 'gr-003', label: 'Regional Norte', p_mens: 106.7 },
    { key: 'gr-004', label: 'Regional Centro-Oeste', p_mens: 100.0 }
  ],
  quase: [
    { key: 'gr-005', label: 'Regional Nordeste', p_mens: 92.6 }
  ],
  longe: [
    { key: 'gr-008', label: 'Regional Centro', gap: -5000000 },
    { key: 'gr-007', label: 'Regional Oeste', gap: -5000000 },
    { key: 'gr-006', label: 'Regional Leste', gap: -3000000 }
  ]
})

const mockChartData = ref({
  keys: ['2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06'],
  labels: ['Jan 2024', 'Fev 2024', 'Mar 2024', 'Abr 2024', 'Mai 2024', 'Jun 2024'],
  series: [
    { id: 'credito', label: 'Crédito', values: [85, 92, 88, 95, 98, 102], color: '#2563eb' },
    { id: 'investimentos', label: 'Investimentos', values: [78, 82, 85, 90, 88, 95], color: '#10b981' },
    { id: 'seguros', label: 'Seguros', values: [65, 70, 75, 80, 85, 88], color: '#f59e0b' },
    { id: 'previdencia', label: 'Previdência', values: [72, 75, 78, 82, 85, 90], color: '#8b5cf6' }
  ]
})

const mockHeatmap = ref({
  units: [
    { value: 'gr-001', label: 'Regional Sudeste' },
    { value: 'gr-002', label: 'Regional Sul' },
    { value: 'gr-003', label: 'Regional Norte' }
  ],
  sections: [
    { id: 'credito', label: 'Crédito' },
    { id: 'investimentos', label: 'Investimentos' },
    { id: 'seguros', label: 'Seguros' },
    { id: 'previdencia', label: 'Previdência' }
  ],
  data: {
    'gr-001|credito': { real: 18000000, meta: 16000000 },
    'gr-001|investimentos': { real: 15000000, meta: 14000000 },
    'gr-001|seguros': { real: 8000000, meta: 7000000 },
    'gr-001|previdencia': { real: 4000000, meta: 3000000 },
    'gr-002|credito': { real: 16000000, meta: 15000000 },
    'gr-002|investimentos': { real: 12000000, meta: 11000000 },
    'gr-002|seguros': { real: 7000000, meta: 6500000 },
    'gr-002|previdencia': { real: 3000000, meta: 2500000 },
    'gr-003|credito': { real: 14000000, meta: 13000000 },
    'gr-003|investimentos': { real: 10000000, meta: 9500000 },
    'gr-003|seguros': { real: 6000000, meta: 5500000 },
    'gr-003|previdencia': { real: 2000000, meta: 2000000 }
  }
})

const heatmapMode = ref<'secoes' | 'meta'>('secoes')

// Computed
const atingimento = computed(() => {
  if (mockKPIs.value.meta_mens === 0) return 0
  return (mockKPIs.value.real_mens / mockKPIs.value.meta_mens) * 100
})

const defasagem = computed(() => {
  return mockKPIs.value.real_mens - mockKPIs.value.meta_mens
})

const forecast = computed(() => {
  // Simulação: média diária * dias totais
  const diasDecorridos = 15
  const diasTotais = 30
  const mediaDiaria = mockKPIs.value.real_mens / diasDecorridos
  return mediaDiaria * diasTotais
})

const forecastPct = computed(() => {
  if (mockKPIs.value.meta_mens === 0) return 0
  return (forecast.value / mockKPIs.value.meta_mens) * 100
})

const topRanking = computed(() => {
  return [...mockRanking.value]
    .sort((a, b) => b.p_mens - a.p_mens)
    .slice(0, 5)
})

const bottomRanking = computed(() => {
  return [...mockRanking.value]
    .sort((a, b) => a.p_mens - b.p_mens)
    .slice(0, 5)
    .reverse()
})

const contexto = computed(() => {
  const f = filterState.value
  const foco =
    f.gerente && f.gerente !== 'Todos' ? `Gerente: ${f.gerente}` :
    f.ggestao && f.ggestao !== 'Todos' ? `GG: ${f.ggestao}` :
    f.agencia && f.agencia !== 'Todas' ? `Agência: ${f.agencia}` :
    f.gerencia && f.gerencia !== 'Todas' ? `GR: ${f.gerencia}` :
    f.diretoria && f.diretoria !== 'Todas' ? `Diretoria: ${f.diretoria}` :
    'Todas as Diretorias'
  
  return `${foco} · Período: ${formatDate(period.value.start)} a ${formatDate(period.value.end)}`
})

// Funções auxiliares
const pctBadgeClass = (p: number): string => {
  if (p < 50) return 'att-low'
  if (p < 100) return 'att-warn'
  return 'att-ok'
}

const moneyBadgeClass = (v: number): string => {
  return v >= 0 ? 'def-pos' : 'def-neg'
}

const getHeatmapCellClass = (pct: number | null): string => {
  if (pct === null) return 'hm-empty'
  if (pct < 50) return 'hm-bad'
  if (pct < 100) return 'hm-warn'
  return 'hm-ok'
}

const getHeatmapValue = (unit: string, section: string): { pct: number | null; text: string } => {
  const key = `${unit}|${section}`
  const bucket = mockHeatmap.value.data[key as keyof typeof mockHeatmap.value.data]
  if (!bucket) return { pct: null, text: '—' }
  
  if (bucket.meta > 0) {
    const pct = (bucket.real / bucket.meta) * 100
    return { pct, text: `${Math.round(pct)}%` }
  }
  
  if (bucket.real > 0) {
    return { pct: null, text: '—' }
  }
  
  return { pct: null, text: '—' }
}

// Renderizar gráfico SVG
const renderChart = () => {
  const container = document.getElementById('exec-chart')
  if (!container || !mockChartData.value.series.length) return
  
  const W = 900
  const H = 260
  const m = { t: 28, r: 36, b: 48, l: 64 }
  const iw = W - m.l - m.r
  const ih = H - m.t - m.b
  const n = mockChartData.value.labels.length
  
  const x = (idx: number) => {
    if (n <= 1) return m.l + iw / 2
    const step = iw / (n - 1)
    return m.l + step * idx
  }
  
  const values = mockChartData.value.series.flatMap(s => s.values.filter(v => v !== null))
  const maxVal = values.length ? Math.max(...values) : 0
  const yMax = Math.max(120, Math.ceil((maxVal || 100) / 10) * 10)
  
  const y = (val: number) => {
    const clamped = Math.min(Math.max(val, 0), yMax)
    return m.t + ih - (clamped / yMax) * ih
  }
  
  const gridLines = []
  const steps = 5
  for (let k = 0; k <= steps; k++) {
    const val = (yMax / steps) * k
    gridLines.push({ y: y(val), label: `${Math.round(val)}%` })
  }
  
  const paths = mockChartData.value.series.map(series => {
    let d = ''
    let started = false
    series.values.forEach((value, idx) => {
      if (value === null || value === undefined) {
        started = false
        return
      }
      const cmd = started ? 'L' : 'M'
      d += `${cmd} ${x(idx)} ${y(value)} `
      started = true
    })
    return `<path class="exec-line" d="${d.trim()}" fill="none" stroke="${series.color}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><title>${series.label}</title></path>`
  }).join('')
  
  const points = mockChartData.value.series.map(series => 
    series.values.map((value, idx) => {
      if (value === null || value === undefined) return ''
      const monthLabel = mockChartData.value.labels[idx] || String(idx + 1)
      const valueLabel = `${value.toFixed(1)}%`
      return `<circle class="exec-line__point" cx="${x(idx)}" cy="${y(value)}" r="3.4" fill="${series.color}" stroke="#fff" stroke-width="1.2"><title>${series.label} • ${monthLabel}: ${valueLabel}</title></circle>`
    }).join('')
  ).join('')
  
  const gridY = gridLines.map(line =>
    `<line x1="${m.l}" y1="${line.y}" x2="${W - m.r}" y2="${line.y}" stroke="#eef2f7"/>
     <text x="${m.l - 6}" y="${line.y + 3}" font-size="10" text-anchor="end" fill="#6b7280">${line.label}</text>`
  ).join('')
  
  const xlabels = mockChartData.value.labels.map((lab, idx) =>
    `<text x="${x(idx)}" y="${H - 10}" font-size="10" text-anchor="middle" fill="#6b7280">${lab}</text>`
  ).join('')
  
  container.innerHTML = `
    <svg class="exec-chart-svg" viewBox="0 0 ${W} ${H}" preserveAspectRatio="none" role="img" aria-label="Linhas mensais de atingimento por seção">
      <rect x="0" y="0" width="${W}" height="${H}" fill="white"/>
      ${gridY}
      ${paths}
      ${points}
      <line x1="${m.l}" y1="${H - m.b}" x2="${W - m.r}" y2="${H - m.b}" stroke="#e5e7eb"/>
      ${xlabels}
    </svg>`
}

onMounted(() => {
  renderChart()
})
</script>

<template>
  <div class="exec-wrapper">
    <div class="container">
      <Filters />
      <TabsNavigation />

      <div id="view-exec" class="exec-view">
        <!-- Contexto -->
        <div id="exec-context" class="exec-context">
          <strong>{{ contexto }}</strong>
        </div>

        <!-- KPIs -->
        <div id="exec-kpis" class="exec-kpis">
          <div class="kpi-card">
            <div class="kpi-card__title">Atingimento mensal</div>
            <div class="kpi-card__value">
              <span :title="formatBRL(mockKPIs.real_mens)">{{ formatBRLReadable(mockKPIs.real_mens) }}</span>
              <small>/ <span :title="formatBRL(mockKPIs.meta_mens)">{{ formatBRLReadable(mockKPIs.meta_mens) }}</span></small>
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
            <div class="kpi-card__title">Defasagem do mês</div>
            <div class="kpi-card__value" :class="moneyBadgeClass(defasagem)" :title="formatBRL(defasagem)">
              {{ formatBRLReadable(defasagem) }}
            </div>
            <div class="kpi-sub muted">Real – Meta (mês)</div>
          </div>

          <div class="kpi-card">
            <div class="kpi-card__title">Forecast x Meta</div>
            <div class="kpi-card__value">
              <span :title="formatBRL(forecast)">{{ formatBRLReadable(forecast) }}</span>
              <small>/ <span :title="formatBRL(mockKPIs.meta_mens)">{{ formatBRLReadable(mockKPIs.meta_mens) }}</span></small>
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

        <!-- Gráfico -->
        <div class="exec-chart">
          <div class="exec-head">
            <h3 id="exec-chart-title">Evolução mensal por seção</h3>
            <div id="exec-chart-legend" class="chart-legend">
              <span 
                v-for="serie in mockChartData.series" 
                :key="serie.id"
                class="legend-item"
              >
                <span 
                  class="legend-swatch legend-swatch--line" 
                  :style="{ background: serie.color, borderColor: serie.color }"
                ></span>
                {{ serie.label }}
              </span>
            </div>
          </div>
          <div id="exec-chart" class="chart"></div>
        </div>

        <!-- Rankings e Status -->
        <div class="exec-panels">
          <div class="exec-panel">
            <div class="exec-h">
              <h3 id="exec-rank-title">Desempenho por Regional</h3>
            </div>
            <div class="exec-rankings">
              <div class="rank-section">
                <h4>Top 5</h4>
                <div id="exec-rank-top" class="rank-mini">
                  <div 
                    v-for="(item, idx) in topRanking" 
                    :key="item.key"
                    class="rank-mini__row"
                  >
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
                </div>
              </div>
              <div class="rank-section">
                <h4>Bottom 5</h4>
                <div id="exec-rank-bottom" class="rank-mini">
                  <div 
                    v-for="(item, idx) in bottomRanking" 
                    :key="item.key"
                    class="rank-mini__row"
                  >
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
                </div>
              </div>
            </div>
          </div>

          <div class="exec-panel">
            <div class="exec-h">
              <h3 id="exec-status-title">Status das Regionais</h3>
            </div>
            <div class="exec-status">
              <div class="status-section">
                <h4>Hit (≥100%)</h4>
                <div id="exec-status-hit" class="list-mini">
                  <div 
                    v-for="item in mockStatus.hit" 
                    :key="item.key"
                    class="list-mini__row"
                  >
                    <div class="list-mini__name">{{ item.label }}</div>
                    <div class="list-mini__val">
                      <span class="att-badge att-ok">{{ item.p_mens.toFixed(1) }}%</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="status-section">
                <h4>Quase (90-99%)</h4>
                <div id="exec-status-quase" class="list-mini">
                  <div 
                    v-for="item in mockStatus.quase" 
                    :key="item.key"
                    class="list-mini__row"
                  >
                    <div class="list-mini__name">{{ item.label }}</div>
                    <div class="list-mini__val">
                      <span class="att-badge att-warn">{{ item.p_mens.toFixed(1) }}%</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="status-section">
                <h4>Longe (maior defasagem)</h4>
                <div id="exec-status-longe" class="list-mini">
                  <div 
                    v-for="item in mockStatus.longe" 
                    :key="item.key"
                    class="list-mini__row"
                  >
                    <div class="list-mini__name">{{ item.label }}</div>
                    <div class="list-mini__val">
                      <span class="def-badge def-neg">{{ formatBRLReadable(item.gap) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Heatmap -->
        <div class="exec-panel">
          <div class="exec-h">
            <h3 id="exec-heatmap-title">Heatmap — Regionais × Seções</h3>
            <div id="exec-heatmap-toggle" class="seg-mini segmented">
              <button 
                class="seg-btn" 
                :class="{ 'is-active': heatmapMode === 'secoes' }"
                data-hm="secoes"
                @click="heatmapMode = 'secoes'"
              >
                Seções
              </button>
              <button 
                class="seg-btn" 
                :class="{ 'is-active': heatmapMode === 'meta' }"
                data-hm="meta"
                @click="heatmapMode = 'meta'"
              >
                Variação Meta
              </button>
            </div>
          </div>
          <div id="exec-heatmap" class="exec-heatmap">
            <div class="hm-row hm-head" style="--hm-cols: 4; --hm-first: 240px; --hm-cell: 136px">
              <div class="hm-cell hm-corner">Regional \ Família</div>
              <div 
                v-for="section in mockHeatmap.sections" 
                :key="section.id"
                class="hm-cell hm-col"
                :title="section.label"
              >
                {{ section.label }}
              </div>
            </div>
            <div 
              v-for="unit in mockHeatmap.units" 
              :key="unit.value"
              class="hm-row"
              style="--hm-cols: 4; --hm-first: 240px; --hm-cell: 136px"
            >
              <div class="hm-cell hm-rowh" :title="unit.label">{{ unit.label }}</div>
              <div 
                v-for="section in mockHeatmap.sections" 
                :key="section.id"
                class="hm-cell hm-val"
                :class="getHeatmapCellClass(getHeatmapValue(unit.value, section.id).pct)"
                :title="`${unit.label} × ${section.label}: ${getHeatmapValue(unit.value, section.id).text}`"
              >
                {{ getHeatmapValue(unit.value, section.id).text }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.exec-wrapper {
  --brand: #b30000;
  --brand-dark: #8f0000;
  --info: #246BFD;
  --bg: #f6f7fc;
  --panel: #ffffff;
  --stroke: #e7eaf2;
  --text: #0f1424;
  --muted: #6b7280;
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);
  --ring: 0 0 0 3px rgba(36, 107, 253, 0.12);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  background-color: var(--bg);
  background-image:
    url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%20viewBox%3D%270%200%20320%20320%27%3E%3Ctext%20x%3D%2750%25%27%20y%3D%2750%25%27%20fill%3D%27rgba%2815%2C20%2C36%2C0.08%29%27%20font-size%3D%2720%27%20font-family%3D%27Plus%20Jakarta%20Sans%2C%20sans-serif%27%20text-anchor%3D%27middle%27%20dominant-baseline%3D%27middle%27%20transform%3D%27rotate%28-30%20160%20160%29%27%3EX%20Burguer%20%E2%80%A2%20Funcional%201234567%3C/text%3E%3C/svg%3E"),
    radial-gradient(1200px 720px at 95% -30%, #dfe8ff 0%, transparent 60%),
    radial-gradient(1100px 720px at -25% -10%, #ffe6ea 0%, transparent 55%);
  background-repeat: repeat, no-repeat, no-repeat;
  background-size: 320px 320px, auto, auto;
  background-position: center center, 95% -30%, -25% -10%;
  color: var(--text);
  font-family: "Plus Jakarta Sans", Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.container {
  max-width: min(1600px, 96vw);
  margin: 18px auto;
  padding: 0 16px;
}

.exec-view {
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.exec-context {
  padding: 14px 18px;
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  font-size: 14px;
  color: var(--text);
  line-height: 1.5;
}

.exec-context strong {
  color: var(--text);
  font-weight: 600;
}

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

.def-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.def-badge.def-pos {
  background: rgba(187, 247, 208, 0.3);
  color: #065f46;
}

.def-badge.def-neg {
  background: rgba(254, 202, 202, 0.3);
  color: #991b1b;
}

.kpi-card__value.def-pos {
  color: #065f46;
}

.kpi-card__value.def-neg {
  color: #991b1b;
}

.exec-chart {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 20px 20px 24px;
}

.exec-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.exec-head h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
}

.chart {
  width: 100%;
  overflow: hidden;
  padding: 20px;
  border-radius: 12px;
  background: #fff;
}

.chart svg {
  display: block;
  width: 100%;
  height: auto;
}

.chart-legend {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.legend-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text);
  font-weight: 600;
  font-size: 13px;
}

.legend-swatch {
  display: inline-block;
  width: 14px;
  height: 6px;
  border-radius: 999px;
  background: #cbd5e1;
  border: 1px solid #94a3b8;
  position: relative;
}

.legend-swatch--line {
  background: transparent;
  border: none;
  height: 0;
  border-top: 2.5px solid;
  width: 18px;
  margin-top: 4px;
  border-radius: 0;
}

.exec-panels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
  gap: 18px;
}

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

.exec-rankings {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.rank-section h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
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

.exec-status {
  display: flex;
  flex-direction: column;
  gap: 20px;
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

.exec-heatmap {
  overflow-x: auto;
}

.hm-row {
  display: grid;
  grid-template-columns: var(--hm-first) repeat(var(--hm-cols), var(--hm-cell));
  gap: 1px;
  background: var(--stroke);
}

.hm-row.hm-head {
  background: var(--bg);
  border-bottom: 2px solid var(--stroke);
}

.hm-cell {
  padding: 10px 12px;
  background: var(--panel);
  font-size: 12px;
  font-weight: 600;
  color: var(--text);
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hm-cell.hm-corner {
  text-align: left;
  justify-content: flex-start;
  font-weight: 700;
  color: var(--text);
}

.hm-cell.hm-col {
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 11px;
}

.hm-cell.hm-rowh {
  text-align: left;
  justify-content: flex-start;
  font-weight: 500;
  color: var(--text);
}

.hm-cell.hm-val {
  cursor: pointer;
  transition: background 0.2s ease;
}

.hm-cell.hm-val:hover {
  background: var(--bg);
}

.hm-cell.hm-ok {
  background: rgba(187, 247, 208, 0.4);
  color: #065f46;
  font-weight: 700;
}

.hm-cell.hm-warn {
  background: rgba(254, 215, 170, 0.4);
  color: #92400e;
  font-weight: 700;
}

.hm-cell.hm-bad {
  background: rgba(254, 202, 202, 0.4);
  color: #991b1b;
  font-weight: 700;
}

.hm-cell.hm-empty {
  background: var(--bg);
  color: var(--muted);
}

.seg-mini.segmented {
  padding: 2px;
  border-radius: 8px;
  background: var(--bg);
  display: inline-flex;
  gap: 2px;
}

.seg-mini .seg-btn {
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 600;
  border: none;
  background: transparent;
  color: var(--muted);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.seg-mini .seg-btn.is-active {
  background: var(--panel);
  color: var(--text);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.muted {
  color: var(--muted);
}

@media (max-width: 768px) {
  .exec-kpis {
    grid-template-columns: 1fr;
  }

  .exec-panels {
    grid-template-columns: 1fr;
  }

  .exec-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

