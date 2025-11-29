<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useFilteredProdutos } from '../composables/useFilteredProdutos'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { useBusinessDays } from '../composables/useBusinessDays'
import { formatPoints, formatPeso, formatByMetric, formatMetricFull, formatBRL } from '../utils/formatUtils'
import type { ProdutoCard } from '../composables/useProdutos'

// Obtém filtros e período globais
const { filterState, period } = useGlobalFilters()

// Usa produtos filtrados
const { produtosPorFamilia, loading, error } = useFilteredProdutos(filterState, period)

const { getCurrentMonthBusinessSnapshot, loadCalendario } = useBusinessDays()

// Estado para controlar qual tooltip está aberto
const openTooltipId = ref<string | null>(null)

// Carrega calendário ao montar
onMounted(async () => {
  await loadCalendario()
})

// Fecha tooltips ao clicar fora ou pressionar ESC
const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.prod-card')) {
    openTooltipId.value = null
  }
}

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    openTooltipId.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)
})

// Função para formatar valores no tooltip
const fmtTooltip = (m: string, v: number): string => {
  if (!Number.isFinite(v)) v = 0
  const metricLower = m?.toLowerCase() || 'valor'
  if (metricLower === 'perc') return `${v.toFixed(1)}%`
  if (metricLower === 'qtd') return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(Math.round(v))
  return formatBRL(Math.round(v))
}

// Computed para obter dados do tooltip de um item
const getTooltipData = (item: ProdutoCard) => {
  const snapshot = getCurrentMonthBusinessSnapshot.value
  const { total: diasTotais, elapsed: diasDecorridos, remaining: diasRestantes } = snapshot

  let meta = item.meta || 0
  let realizado = item.realizado || 0
  if (item.metrica?.toLowerCase() === 'perc') meta = 100

  const faltaTotal = Math.max(0, meta - realizado)
  const necessarioPorDia = diasRestantes > 0 ? (faltaTotal / diasRestantes) : 0
  const mediaDiariaAtual = diasDecorridos > 0 ? (realizado / diasDecorridos) : 0
  const projecaoRitmoAtual = mediaDiariaAtual * (diasTotais || 0)
  const referenciaHoje = diasTotais > 0 ? (meta / diasTotais) * diasDecorridos : 0

  return {
    diasTotais,
    diasDecorridos,
    diasRestantes,
    faltaTotal: fmtTooltip(item.metrica || 'valor', faltaTotal),
    necessarioPorDia: diasRestantes > 0 ? fmtTooltip(item.metrica || 'valor', necessarioPorDia) : '—',
    referenciaHoje: diasDecorridos > 0 ? fmtTooltip(item.metrica || 'valor', referenciaHoje) : '—',
    projecaoRitmoAtual: fmtTooltip(item.metrica || 'valor', projecaoRitmoAtual)
  }
}

// Função para posicionar o tooltip
const positionTip = (badge: HTMLElement, tip: HTMLElement): void => {
  const card = badge.closest('.prod-card') as HTMLElement
  if (!card) return

  const b = badge.getBoundingClientRect()
  const c = card.getBoundingClientRect()
  const tw = tip.offsetWidth || 320
  const th = tip.offsetHeight || 200
  const vw = window.innerWidth
  const vh = window.innerHeight

  let top = (b.bottom - c.top) + 8
  if (b.bottom + th + 12 > vh) {
    top = (b.top - c.top) - th - 8
  }

  let left = c.width - tw - 12
  const absLeft = c.left + left
  if (absLeft < 12) left = 12
  if (absLeft + tw > vw - 12) {
    left = Math.max(12, vw - 12 - c.left - tw)
  }

  tip.style.top = `${top}px`
  tip.style.left = `${left}px`
}

// Função para abrir tooltip
const openTooltip = (itemId: string, event: Event): void => {
  event.stopPropagation()
  openTooltipId.value = itemId

  // Posiciona o tooltip após ele ser renderizado
  requestAnimationFrame(() => {
    const card = document.querySelector(`[data-prod-id="${itemId}"]`) as HTMLElement
    if (!card) return

    const badge = card.querySelector('.badge') as HTMLElement
    const tip = card.querySelector('.kpi-tip') as HTMLElement

    if (badge && tip) {
      positionTip(badge, tip)
    }
  })
}

// Função para fechar tooltip
const closeTooltip = (): void => {
  openTooltipId.value = null
}

// Função para toggle (usado em click/touch)
const toggleTooltip = (itemId: string, event: Event): void => {
  event.stopPropagation()

  if (openTooltipId.value === itemId) {
    closeTooltip()
  } else {
    openTooltip(itemId, event)
  }
}

const getBadgeClass = (pct: number): string => {
  if (pct < 50) return 'badge--low'
  if (pct < 100) return 'badge--warn'
  return 'badge--ok'
}

const getTrackClass = (pct: number): string => {
  if (pct < 50) return 'var--low'
  if (pct < 100) return 'var--warn'
  return 'var--ok'
}

const calculateAtingimento = (item: ProdutoCard): number => {
  const meta = item.meta || 0
  const realizado = item.realizado || 0
  if (meta <= 0) {
    return 0
  }
  return (realizado / meta) * 100
}

const calculatePontosRatio = (item: ProdutoCard): number => {
  const pontosMeta = item.pontosMeta || 0
  const pontos = item.pontos || 0
  return pontosMeta > 0 ? (pontos / pontosMeta) * 100 : 0
}

const getMetricLabel = (metric: string): string => {
  if (!metric) return 'Valor'
  const metricLower = metric.toLowerCase()
  if (metricLower === 'valor') return 'Valor'
  if (metricLower === 'qtd' || metricLower === 'quantidade') return 'Quantidade'
  return 'Percentual'
}
</script>

<template>
  <div id="grid-familias">
    <div v-if="loading" class="loading-state">
      <p>Carregando produtos...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <template v-else-if="produtosPorFamilia.length > 0">
      <div
        v-for="section in produtosPorFamilia"
        :key="section.id"
        class="fam-section"
      >
        <header class="fam-section__header">
          <div class="fam-section__title">
            <span>{{ section.label }}</span>
            <small class="fam-section__meta" v-if="section.totals">
              <span class="fam-section__meta-item" :title="`Pontos: ${formatPoints(section.totals.pontosHit, { withUnit: true })} / ${formatPoints(section.totals.pontosTotal, { withUnit: true })}`">
                Pontos: {{ formatPoints(section.totals.pontosHit) }} / {{ formatPoints(section.totals.pontosTotal) }}
              </span>
            </small>
          </div>
        </header>
        <div class="fam-section__grid">
          <article
            v-for="item in section.items"
            :key="item.id"
            class="prod-card"
            :class="{ 'is-tip-open': openTooltipId === item.id }"
            :data-prod-id="item.id"
            :data-familia-id="item.familiaId"
            :data-familia-label="item.familiaNome"
            @mouseleave="closeTooltip"
          >
            <div class="prod-card__title">
              <i :class="item.icon || 'ti ti-chart-line'"></i>
              <span class="prod-card__name has-ellipsis" :title="item.nome">{{ item.nome }}</span>
              <span
                class="badge"
                :class="getBadgeClass(calculateAtingimento(item))"
                :aria-label="`Atingimento: ${calculateAtingimento(item).toFixed(1)}%`"
                :title="`${calculateAtingimento(item).toFixed(1)}%`"
                @mouseenter="(e) => { e.stopPropagation(); openTooltip(item.id, e) }"
                @click="(e) => { e.stopPropagation(); toggleTooltip(item.id, e) }"
                @touchstart="(e) => { e.stopPropagation(); toggleTooltip(item.id, e) }"
              >
                {{ calculateAtingimento(item) >= 100
                  ? `${Math.round(calculateAtingimento(item))}%`
                  : `${calculateAtingimento(item).toFixed(1)}%` }}
              </span>
            </div>

            <div class="prod-card__meta">
              <span class="pill">
                Pontos: {{ formatPoints(item.pontos || 0) }} / {{ formatPoints(item.pontosMeta || 0) }}
              </span>
              <span class="pill">Peso: {{ formatPeso(item.peso || 0) }}</span>
              <span class="pill">{{ getMetricLabel(item.metrica) }}</span>
            </div>

            <div class="prod-card__kpis">
              <div class="kv">
                <small>Meta</small>
                <strong class="has-ellipsis" :title="formatMetricFull(item.metrica, item.meta)">
                  {{ formatByMetric(item.metrica, item.meta) }}
                </strong>
              </div>
              <div class="kv">
                <small>Realizado</small>
                <strong class="has-ellipsis" :title="formatMetricFull(item.metrica, item.realizado)">
                  {{ formatByMetric(item.metrica, item.realizado) }}
                </strong>
              </div>
            </div>

            <div class="prod-card__var">
              <div class="prod-card__var-head">
                <small>Atingimento de pontos</small>
              </div>
              <div class="prod-card__var-body">
                <span class="prod-card__var-goal" :title="formatPoints(item.pontosMeta || 0, { withUnit: true })">
                  {{ formatPoints(item.pontosMeta || 0, { withUnit: true }) }}
                </span>
                <div
                  class="prod-card__var-track"
                  :class="getTrackClass(calculatePontosRatio(item))"
                  :data-ratio="calculatePontosRatio(item).toFixed(2)"
                  role="progressbar"
                  aria-valuemin="0"
                  aria-valuemax="100"
                  :aria-valuenow="Math.round(calculatePontosRatio(item))"
                  :aria-valuetext="`${calculatePontosRatio(item).toFixed(1)}% (${formatPoints(item.pontos || 0)} de ${formatPoints(item.pontosMeta || 0)})`"
                >
                  <span
                    class="prod-card__var-fill"
                    :style="{ '--target': `${calculatePontosRatio(item)}%` }"
                  ></span>
                  <span
                    class="prod-card__var-label"
                    :style="{ '--target': `${calculatePontosRatio(item)}%` }"
                    :title="`Atingido: ${formatPoints(item.pontos || 0, { withUnit: true })} · ${calculatePontosRatio(item).toFixed(1)}%`"
                  >
                    <span class="prod-card__var-value">{{ calculatePontosRatio(item).toFixed(1) }}%</span>
                  </span>
                </div>
              </div>
            </div>

            <div class="prod-card__foot">
              Atualizado em {{ item.ultimaAtualizacao || 'N/A' }}
            </div>

            <!-- Tooltip com informações de projeção e metas -->
            <div
              v-if="openTooltipId === item.id"
              class="kpi-tip is-open"
              role="dialog"
              aria-label="Detalhes do indicador"
            >
              <template v-if="getTooltipData(item)">
                <h5>Projeção e metas</h5>
                <div class="row">
                  <span>Quantidade de dias úteis no mês</span>
                  <span>{{ new Intl.NumberFormat('pt-BR').format(getTooltipData(item).diasTotais) }}</span>
                </div>
                <div class="row">
                  <span>Dias úteis trabalhados</span>
                  <span>{{ new Intl.NumberFormat('pt-BR').format(getTooltipData(item).diasDecorridos) }}</span>
                </div>
                <div class="row">
                  <span>Dias úteis que faltam</span>
                  <span>{{ new Intl.NumberFormat('pt-BR').format(getTooltipData(item).diasRestantes) }}</span>
                </div>
                <div class="row">
                  <span>Falta para a meta</span>
                  <span>{{ getTooltipData(item).faltaTotal }}</span>
                </div>
                <div class="row">
                  <span>Referência para hoje</span>
                  <span>{{ getTooltipData(item).referenciaHoje }}</span>
                </div>
                <div class="row">
                  <span>Meta diária necessária</span>
                  <span>{{ getTooltipData(item).necessarioPorDia }}</span>
                </div>
                <div class="row">
                  <span>Projeção (ritmo atual)</span>
                  <span>{{ getTooltipData(item).projecaoRitmoAtual }}</span>
                </div>
              </template>
            </div>
          </article>
        </div>
      </div>
    </template>

    <div v-else class="empty-state">
      <p>Nenhum produto encontrado</p>
    </div>
  </div>
</template>

<style scoped>
/* Usa os estilos globais do CSS, apenas adiciona estilos específicos se necessário */
.loading-state,
.error-state,
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.error-state {
  color: #dc2626;
}
</style>

<style>
/* Garantir que os estilos dos cards sejam aplicados mesmo se as variáveis CSS não estiverem carregadas */
.prod-card {
  position: relative;
  overflow: visible;
  background: #fff;
  border: 1px solid #e7eaf2;
  border-radius: 18px;
  padding: 16px 16px 20px;
  box-shadow: 0 12px 28px rgba(17, 23, 41, 0.08);
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  cursor: pointer;
}

.prod-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
  border-color: #d7def1;
}

.prod-card__title {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.prod-card__title i {
  font-size: 22px;
  color: #b30000;
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  background: #fff4f5;
  border: 1px solid #ffd7db;
  border-radius: 12px;
  flex-shrink: 0;
}

.prod-card__name {
  flex: 1 1 auto;
  min-width: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.badge {
  --size: 54px;
  flex: 0 0 var(--size);
  width: var(--size);
  height: var(--size);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 11px;
  line-height: 1;
  text-align: center;
  border: 1px solid transparent;
  padding: 0 2px;
  white-space: nowrap;
  user-select: none;
  flex-shrink: 0;
}

.badge--low {
  background: #fff1f2;
  border-color: #fecaca;
  color: #991b1b;
}

.badge--warn {
  background: #fff7ed;
  border-color: #fed7aa;
  color: #92400e;
}

.badge--ok {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #166534;
}

.prod-card__meta {
  margin: 4px 0 2px;
  color: #6b7280;
  font-size: 12px;
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.pill {
  background: #f3f4f6;
  border-radius: 999px;
  padding: 2px 8px;
  font-weight: 700;
  color: #4b5563;
  font-size: 12px;
}

.prod-card__kpis {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.kv {
  background: #f8faff;
  border: 1px dashed #dde5ff;
  border-radius: 12px;
  padding: 10px 12px;
  min-width: 0;
}

.kv small {
  display: block;
  color: #6b7280;
  font-size: 12px;
  margin-bottom: 4px;
}

.kv strong {
  display: block;
  font-size: 16px;
  line-height: 1.15;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.prod-card__var {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.prod-card__var-head {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  font-size: 12px;
  color: #4b5563;
}

.prod-card__var-head small {
  display: block;
}

.prod-card__var-body {
  display: flex;
  align-items: center;
  gap: 12px;
}

.prod-card__var-goal {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  white-space: nowrap;
  min-width: max-content;
}

.prod-card__var-track {
  position: relative;
  flex: 1 1 auto;
  min-width: 0;
  height: 18px;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
  background: #f8fafc;
  overflow: hidden;
}

.prod-card__var-fill {
  position: absolute;
  left: 2px;
  top: 3px;
  bottom: 3px;
  border-radius: 999px;
  width: var(--target, 0%);
  transform-origin: left center;
  transform: scaleX(1);
  will-change: transform;
}

.prod-card__var-track.var--low .prod-card__var-fill {
  background: #fecaca;
}

.prod-card__var-track.var--warn .prod-card__var-fill {
  background: #fed7aa;
}

.prod-card__var-track.var--ok .prod-card__var-fill {
  background: #bbf7d0;
}

.prod-card__var-label {
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

.prod-card__var-value {
  display: inline-block;
}

.prod-card__foot {
  margin-top: auto;
  padding-top: 6px;
  font-size: 11px;
  color: #6b7280;
  text-align: right;
  align-self: flex-end;
}

#grid-familias {
  display: block !important;
  gap: 0 !important;
}

.fam-section {
  margin: 22px 0 6px;
}

.fam-section__header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0 2px 12px;
}

.fam-section__title {
  font-weight: 800;
  color: #1f2937;
  font-size: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.fam-section__meta {
  color: #6b7280;
  font-weight: 700;
  font-size: 12.5px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.fam-section__meta-item {
  white-space: nowrap;
}

.fam-section__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 16px;
}

.has-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.kpi-tip {
  position: absolute;
  right: 0;
  top: 60px;
  z-index: 900;
  width: 320px;
  background: #0f172a;
  color: #e2e8f0;
  border-radius: 12px;
  border: 1px solid #1e293b;
  box-shadow: 0 12px 28px rgba(2, 6, 23, 0.35);
  padding: 10px 12px;
  display: none;
  pointer-events: auto;
}

.kpi-tip.is-open {
  display: block;
}

.kpi-tip h5 {
  margin: 0 0 6px;
  font-size: 12px;
  color: #93c5fd;
}

.kpi-tip .row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 0;
  border-top: 1px dashed #334155;
}

.kpi-tip .row:first-of-type {
  border-top: none;
}

.kpi-tip .row span:first-child {
  color: #94a3b8;
  font-size: 12px;
}

.kpi-tip .row span:last-child {
  font-weight: 800;
  color: #e5e7eb;
  font-size: 12.5px;
}

.prod-card.is-tip-open {
  z-index: 20;
}
</style>

