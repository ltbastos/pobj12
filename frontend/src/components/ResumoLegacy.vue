<script setup lang="ts">
import { computed, ref } from 'vue'
import { useProdutosLegacy } from '../composables/useProdutosLegacy'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { useFilteredProdutos } from '../composables/useFilteredProdutos'
import { formatByMetric, formatMetricFull, formatPoints, formatINT } from '../utils/formatUtils'
import { formatBRDate } from '../services/calendarioService'
import type { LegacySection, LegacyItem } from '../composables/useProdutosLegacy'

const { filterState, period } = useGlobalFilters()
useFilteredProdutos(filterState, period) // Garante sincronização dos filtros com o resumo
const { produtosPorFamilia, loading, error } = useProdutosLegacy()

// Estado para controlar quais linhas estão expandidas
const expandedRows = ref<Set<string>>(new Set())
const expandedSections = ref<Set<string>>(new Set())

const toggleRow = (rowId: string) => {
  if (expandedRows.value.has(rowId)) {
    expandedRows.value.delete(rowId)
  } else {
    expandedRows.value.add(rowId)
  }
}

const toggleSection = (sectionId: string) => {
  if (expandedSections.value.has(sectionId)) {
    expandedSections.value.delete(sectionId)
  } else {
    expandedSections.value.add(sectionId)
  }
}

// Funções auxiliares para formatação de meses
const monthKeyShortLabel = (key: string): string => {
  if (!key) return ''
  const [y, m] = key.split('-')
  const year = Number(y)
  const month = Number(m)
  if (!Number.isFinite(year) || !Number.isFinite(month)) return key
  const dt = new Date(Date.UTC(year, month - 1, 1))
  return dt.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', '').toUpperCase()
}

const monthKeyLabel = (key: string): string => {
  if (!key) return '—'
  const [y, m] = key.split('-')
  const year = Number(y)
  const month = Number(m)
  if (!Number.isFinite(year) || !Number.isFinite(month)) return key
  const dt = new Date(Date.UTC(year, month - 1, 1))
  return dt.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' }).replace('.', '')
}

// Gera array de meses baseado no período
const monthKeys = computed(() => {
  const start = period.value.start
  const end = period.value.end
  if (!start || !end) return []

  const startDate = new Date(start + 'T00:00:00Z')
  const endDate = new Date(end + 'T00:00:00Z')
  const months: string[] = []

  let current = new Date(startDate)
  while (current <= endDate) {
    const year = current.getUTCFullYear()
    const month = String(current.getUTCMonth() + 1).padStart(2, '0')
    months.push(`${year}-${month}`)

    current.setUTCMonth(current.getUTCMonth() + 1)
  }

  return months
})

const monthLabels = computed(() => monthKeys.value.map(monthKeyShortLabel))
const monthLongLabels = computed(() => monthKeys.value.map(monthKeyLabel))

// Encontra o índice do mês atual
const currentMonthIdx = computed(() => {
  const currentMonth = new Date().toISOString().slice(0, 7)
  return monthKeys.value.findIndex(m => m === currentMonth)
})

// Função para obter dados de um mês específico de um item
const getMonthData = (item: LegacyItem, monthKey: string) => {
  const monthData = item.months.find(m => m.mes === monthKey)
  return monthData || { meta: 0, realizado: 0, atingimento: 0 }
}

// Função para determinar classe do badge de percentual
const pctBadgeCls = (pct: number): string => {
  if (!Number.isFinite(pct)) return 'is-empty'
  if (pct < 50) return 'att-low'
  if (pct < 100) return 'att-warn'
  return 'att-ok'
}

// Função para determinar se uma seção tem linhas expansíveis
const sectionHasExpandableRows = (section: LegacySection): boolean => {
  return section.items.some(item => item.children && item.children.length > 0)
}
</script>

<template>
  <div class="resumo-legacy">
    <div v-if="loading" class="resumo-legacy__loading">
      Carregando...
    </div>

    <div v-else-if="error" class="resumo-legacy__error">
      Erro: {{ error }}
    </div>

    <div v-else-if="!produtosPorFamilia || produtosPorFamilia.length === 0" class="resumo-legacy__empty">
      Nenhum dado disponível
    </div>

    <template v-else>
      <section
        v-for="section in produtosPorFamilia"
        :key="section.id"
        class="resumo-legacy__section resumo-legacy__section--annual card card--legacy"
      >
        <header class="resumo-legacy__head">
          <div class="resumo-legacy__heading">
            <div class="resumo-legacy__title-row">
              <span class="resumo-legacy__name">{{ section.label }}</span>
              <div class="resumo-legacy__section-actions">
                <button
                  type="button"
                  :class="[
                    'resumo-legacy__section-toggle',
                    { 'is-disabled': !sectionHasExpandableRows(section) },
                    { 'is-expanded': expandedSections.has(section.id) }
                  ]"
                  :disabled="!sectionHasExpandableRows(section)"
                  @click="toggleSection(section.id)"
                >
                  <i class="ti ti-filter" aria-hidden="true"></i>
                  <span class="resumo-legacy__section-toggle-label">
                    {{ expandedSections.has(section.id) ? 'Recolher filtros' : 'Abrir todos os filtros' }}
                  </span>
                </button>
              </div>
            </div>
            <div class="resumo-legacy__chips">
              <span class="resumo-legacy__chip">
                <i class="ti ti-box-multiple" aria-hidden="true"></i>
                {{ formatINT(section.items.length || 0) }} indicadores
              </span>
              <span class="resumo-legacy__chip" :title="`Pontos: ${formatPoints(section.totals.pontosHit, { withUnit: true })} / ${formatPoints(section.totals.pontosTotal, { withUnit: true })}`">
                Pontos {{ formatINT(Math.round(section.totals.pontosHit)) }} / {{ formatINT(Math.round(section.totals.pontosTotal)) }}
              </span>
            </div>
          </div>
          <div class="resumo-legacy__stats">
            <div class="resumo-legacy__stat">
              <span class="resumo-legacy__stat-label">Peso total</span>
              <strong class="resumo-legacy__stat-value">{{ formatINT(Math.round(section.totals.pontosTotal)) }}</strong>
            </div>
            <div class="resumo-legacy__stat" v-if="section.totals.realizadoTotal > 0">
              <span class="resumo-legacy__stat-label">Realizado</span>
              <strong class="resumo-legacy__stat-value">{{ formatByMetric('valor', section.totals.realizadoTotal) }}</strong>
            </div>
            <div class="resumo-legacy__stat">
              <span class="resumo-legacy__stat-label">Atingimento</span>
              <strong class="resumo-legacy__stat-value">
                {{ section.totals.metaTotal > 0 ? `${Math.max(0, Math.min(200, section.totals.atingPct)).toFixed(1)}%` : '—' }}
              </strong>
            </div>
          </div>
        </header>

        <div class="resumo-legacy__table-wrapper">
          <table class="resumo-legacy__table">
            <thead>
              <tr>
                <th scope="col">Subindicador</th>
                <th scope="col" class="resumo-legacy__col--peso">Peso</th>
                <th scope="col">Métrica</th>
                <th scope="col" class="resumo-legacy__col--meta">Meta</th>
                <th scope="col" class="resumo-legacy__col--real">Realizado</th>
                <th scope="col" class="resumo-legacy__col--ref">Ref. do dia</th>
                <th scope="col" class="resumo-legacy__col--forecast">Forecast</th>
                <th scope="col" class="resumo-legacy__col--meta-dia">Meta diária nec.</th>
                <th scope="col" class="resumo-legacy__col--pontos">Pontos</th>
                <th scope="col" class="resumo-legacy__col--ating">Ating.</th>
                <th scope="col" class="resumo-legacy__col--update">Atualização</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="item in section.items" :key="item.id">
                <tr
                  :class="[
                    'resumo-legacy__row',
                    { 'is-expanded': expandedRows.has(`${section.id}-${item.id}`) || expandedSections.has(section.id) }
                  ]"
                  :data-row-id="`${section.id}-${item.id}`"
                >
                  <td class="resumo-legacy__col--prod">
                    <div class="resumo-legacy__prod">
                      <button
                        v-if="item.children && item.children.length > 0"
                        type="button"
                        class="resumo-legacy__prod-toggle"
                        :aria-expanded="expandedRows.has(`${section.id}-${item.id}`) || expandedSections.has(section.id)"
                        @click="toggleRow(`${section.id}-${item.id}`)"
                      >
                        <i
                          class="ti ti-chevron-right resumo-legacy__prod-toggle-icon"
                          :class="{ 'is-expanded': expandedRows.has(`${section.id}-${item.id}`) || expandedSections.has(section.id) }"
                          aria-hidden="true"
                        ></i>
                        <span class="resumo-legacy__prod-name" :title="item.nome">{{ item.nome }}</span>
                      </button>
                      <div v-else class="resumo-legacy__prod-name-wrapper">
                        <span class="resumo-legacy__prod-toggle-icon resumo-legacy__prod-toggle-icon--placeholder" aria-hidden="true"></span>
                        <span class="resumo-legacy__prod-name" :title="item.nome">{{ item.nome }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="resumo-legacy__col--peso">
                    {{ formatINT(Math.round(item.pontosMeta || item.peso || 0)) }}
                  </td>
                  <td>
                    <span
                      :class="[
                        'resumo-legacy__metric',
                        `resumo-legacy__metric--${item.metrica === 'valor' ? 'valor' : item.metrica === 'qtd' ? 'qtd' : item.metrica === 'perc' ? 'perc' : ''}`
                      ]"
                      :title="`Métrica: ${item.metrica}`"
                    >
                      {{ item.metrica === 'valor' ? 'Valor' : item.metrica === 'qtd' ? 'Quantidade' : item.metrica === 'perc' ? 'Percentual' : item.metrica }}
                    </span>
                  </td>
                  <td class="resumo-legacy__col--meta" :title="formatMetricFull(item.metrica, item.meta)">
                    {{ formatByMetric(item.metrica, item.meta) }}
                  </td>
                  <td class="resumo-legacy__col--real" :title="formatMetricFull(item.metrica, item.realizado)">
                    {{ formatByMetric(item.metrica, item.realizado) }}
                  </td>
                  <td class="resumo-legacy__col--ref" :title="formatMetricFull(item.metrica, item.referenciaHoje)">
                    {{ item.referenciaHoje != null ? formatByMetric(item.metrica, item.referenciaHoje) : '—' }}
                  </td>
                  <td class="resumo-legacy__col--forecast" :title="formatMetricFull(item.metrica, item.projecao)">
                    {{ item.projecao != null ? formatByMetric(item.metrica, item.projecao) : '—' }}
                  </td>
                  <td class="resumo-legacy__col--meta-dia" :title="formatMetricFull(item.metrica, item.metaDiariaNecessaria)">
                    {{ item.metaDiariaNecessaria != null ? formatByMetric(item.metrica, item.metaDiariaNecessaria) : '—' }}
                  </td>
                  <td class="resumo-legacy__col--pontos" :title="formatPoints(item.pontos, { withUnit: true })">
                    {{ formatPoints(item.pontos, { withUnit: true }) }}
                  </td>
                  <td class="resumo-legacy__col--ating">
                    <div class="resumo-legacy__ating" title="Atingimento">
                      <div
                        :class="[
                          'resumo-legacy__ating-meter',
                          (item.ating || 0) >= 1 ? 'is-ok' : (item.ating || 0) >= 0.5 ? 'is-warn' : 'is-low'
                        ]"
                        :style="{ '--fill': Math.max(0, Math.min(1, (item.ating || 0))) }"
                        role="progressbar"
                        :aria-valuemin="0"
                        :aria-valuemax="200"
                        :aria-valuenow="Math.max(0, Math.min(200, (item.ating || 0) * 100))"
                      >
                        <span class="resumo-legacy__ating-value">
                          {{ item.meta > 0 ? `${Math.max(0, Math.min(200, (item.ating || 0) * 100)).toFixed(1)}%` : '—' }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="resumo-legacy__col--update">
                    {{ item.ultimaAtualizacao ? (item.ultimaAtualizacao.match(/^\d{4}-\d{2}-\d{2}$/) ? formatBRDate(item.ultimaAtualizacao) : item.ultimaAtualizacao) : '—' }}
                  </td>
                </tr>

                <!-- Linhas filhas (subindicadores) -->
                <template v-if="item.children && item.children.length > 0">
                  <tr
                    v-for="child in item.children"
                    :key="child.id"
                    :class="[
                      'resumo-legacy__row resumo-legacy__row--child',
                      { 'is-visible': expandedRows.has(`${section.id}-${item.id}`) || expandedSections.has(section.id) }
                    ]"
                    :data-row-id="`${section.id}-${item.id}-${child.id}`"
                  >
                    <td class="resumo-legacy__col--prod">
                      <div class="resumo-legacy__prod">
                        <span class="resumo-legacy__prod-name resumo-legacy__prod-name--child" :title="child.nome">{{ child.nome }}</span>
                      </div>
                    </td>
                    <td class="resumo-legacy__col--peso">
                      {{ formatINT(Math.round(child.pontosMeta || child.peso || 0)) }}
                    </td>
                    <td>
                      <span
                        :class="[
                          'resumo-legacy__metric',
                          `resumo-legacy__metric--${child.metrica === 'valor' ? 'valor' : child.metrica === 'qtd' ? 'qtd' : child.metrica === 'perc' ? 'perc' : ''}`
                        ]"
                        :title="`Métrica: ${child.metrica}`"
                      >
                        {{ child.metrica === 'valor' ? 'Valor' : child.metrica === 'qtd' ? 'Quantidade' : child.metrica === 'perc' ? 'Percentual' : child.metrica }}
                      </span>
                    </td>
                    <td class="resumo-legacy__col--meta" :title="formatMetricFull(child.metrica, child.meta)">
                      {{ formatByMetric(child.metrica, child.meta) }}
                    </td>
                    <td class="resumo-legacy__col--real" :title="formatMetricFull(child.metrica, child.realizado)">
                      {{ formatByMetric(child.metrica, child.realizado) }}
                    </td>
                    <td class="resumo-legacy__col--ref" :title="formatMetricFull(child.metrica, child.referenciaHoje)">
                      {{ child.referenciaHoje != null ? formatByMetric(child.metrica, child.referenciaHoje) : '—' }}
                    </td>
                    <td class="resumo-legacy__col--forecast" :title="formatMetricFull(child.metrica, child.projecao)">
                      {{ child.projecao != null ? formatByMetric(child.metrica, child.projecao) : '—' }}
                    </td>
                    <td class="resumo-legacy__col--meta-dia" :title="formatMetricFull(child.metrica, child.metaDiariaNecessaria)">
                      {{ child.metaDiariaNecessaria != null ? formatByMetric(child.metrica, child.metaDiariaNecessaria) : '—' }}
                    </td>
                    <td class="resumo-legacy__col--pontos" :title="formatPoints(child.pontos, { withUnit: true })">
                      {{ '-'}}
                    </td>
                    <td class="resumo-legacy__col--ating">
                      <div class="resumo-legacy__ating" title="Atingimento">
                        <div
                          :class="[
                            'resumo-legacy__ating-meter',
                            (child.ating || 0) >= 1 ? 'is-ok' : (child.ating || 0) >= 0.5 ? 'is-warn' : 'is-low'
                          ]"
                          :style="{ '--fill': Math.max(0, Math.min(1, (child.ating || 0))) }"
                          role="progressbar"
                          :aria-valuemin="0"
                          :aria-valuemax="200"
                          :aria-valuenow="Math.max(0, Math.min(200, (child.ating || 0) * 100))"
                        >
                          <span class="resumo-legacy__ating-value">
                            {{ '—' }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="resumo-legacy__col--update">
                      {{ child.ultimaAtualizacao ? (child.ultimaAtualizacao.match(/^\d{4}-\d{2}-\d{2}$/) ? formatBRDate(child.ultimaAtualizacao) : child.ultimaAtualizacao) : '—' }}
                    </td>
                  </tr>
                </template>
              </template>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<style scoped>
.resumo-legacy {
  width: 100%;
}

.resumo-legacy__loading,
.resumo-legacy__error,
.resumo-legacy__empty {
  padding: 32px 24px;
  text-align: center;
  background: #fff;
  border: 1px dashed #d7def3;
  border-radius: var(--radius, 18px);
  color: #475569;
  font-size: 14px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.resumo-legacy__section {
  overflow: hidden;
  margin-bottom: 24px;
  background: #fff;
  border: 1px solid var(--stroke, #d7def3);
  border-radius: 18px;
  box-shadow: var(--shadow, 0 12px 28px rgba(17, 23, 41, 0.12));
  transition: 0.18s ease transform, 0.18s ease box-shadow, 0.18s ease border-color;
}

.resumo-legacy__section:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
  border-color: #d7def1;
}

.resumo-legacy__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 22px 24px 18px;
  border-bottom: 1px solid var(--stroke, #e5e7eb);
}

.resumo-legacy__heading {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.resumo-legacy__title-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.resumo-legacy__name {
  font-size: 16px;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #111827;
}

.resumo-legacy__section-actions {
  display: flex;
  align-items: center;
}

.resumo-legacy__section-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 12px;
  border: 1px solid #ffd7db;
  background: #fff4f5;
  color: var(--brand, #b30000);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  appearance: none;
  font-family: inherit;
}

.resumo-legacy__section-toggle i {
  font-size: 14px;
}

.resumo-legacy__section-toggle span {
  display: inline-flex;
  align-items: center;
}

.resumo-legacy__section-toggle[disabled],
.resumo-legacy__section-toggle.is-disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.resumo-legacy__section-toggle[disabled]:hover,
.resumo-legacy__section-toggle.is-disabled:hover {
  background: #fff4f5;
  color: var(--brand, #b30000);
  border-color: #ffd7db;
}

.resumo-legacy__section-toggle:hover:not(:disabled):not(.is-disabled) {
  background: #ffe4e6;
  border-color: #fecdd3;
}

.resumo-legacy__section-toggle.is-expanded {
  background: var(--brand, #b30000);
  color: #fff;
  border-color: var(--brand, #b30000);
}

.resumo-legacy__section-toggle:focus-visible {
  outline: 2px solid var(--primary, #2563eb);
  outline-offset: 2px;
}

.resumo-legacy__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.resumo-legacy__chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #f3f4f6;
  color: #4b5563;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.resumo-legacy__chip i {
  font-size: 14px;
  color: #94a3b8;
}

.resumo-legacy__stats {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 18px;
  justify-content: flex-end;
  align-items: center;
}

.resumo-legacy__stat {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  min-width: 110px;
  padding: 8px 12px;
  border-radius: 14px;
  background: #f8faff;
  border: 1px dashed #dde5ff;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
}

.resumo-legacy__stat-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #64748b;
  font-weight: 800;
}

.resumo-legacy__stat-value {
  font-size: 20px;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #0f172a;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
}

@media (max-width: 900px) {
  .resumo-legacy__stats {
    justify-content: flex-start;
  }
  .resumo-legacy__stat {
    align-items: center;
    min-width: 0;
  }
}

.resumo-legacy__table-wrapper {
  width: 100%;
  overflow-x: auto;
}

.resumo-legacy__table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 680px;
}

.resumo-legacy__table--annual {
  min-width: 960px;
}

.resumo-legacy__table--annual thead th {
  text-align: center;
}

.resumo-legacy__table thead th {
  position: sticky;
  top: 0;
  background: #f9fafb;
  padding: 12px 16px;
  text-align: center;
  font-size: 11px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  border-bottom: 1px solid var(--stroke, #e5e7eb);
  font-weight: 800;
  white-space: nowrap;
  z-index: 1;
}

.resumo-legacy__table thead th:first-child {
  text-align: left;
}

.resumo-legacy__table tbody th,
.resumo-legacy__table tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--stroke, #e5e7eb);
  font-size: 12px;
  color: #111827;
  vertical-align: middle;
  text-align: center;
}

.resumo-legacy__table tbody tr:nth-child(odd) {
  background: rgba(248, 250, 252, 0.65);
}

.resumo-legacy__table tbody tr:last-child th,
.resumo-legacy__table tbody tr:last-child td {
  border-bottom: none;
}

.resumo-legacy__table tbody th {
  font-weight: 700;
  text-align: left;
}

.resumo-legacy__row--child {
  display: none;
}

.resumo-legacy__row--child.is-visible {
  display: table-row;
}

.resumo-legacy__child-row {
  background: rgba(241, 245, 249, 0.8);
}

.resumo-legacy__child-row .resumo-legacy__prod-name {
  font-weight: 600;
}

.resumo-legacy__prod {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.resumo-legacy__prod-name-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.resumo-legacy__prod-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  border: none;
  background: none;
  padding: 0;
  font: inherit;
  color: inherit;
  text-align: left;
  cursor: pointer;
}

.resumo-legacy__prod-toggle:focus-visible {
  outline: 2px solid var(--primary, #2563eb);
  outline-offset: 2px;
}

.resumo-legacy__prod-toggle-icon {
  transition: transform 0.2s ease;
  font-size: 14px;
  flex-shrink: 0;
  width: 14px;
  display: inline-block;
}

.resumo-legacy__prod-toggle-icon--placeholder {
  visibility: hidden;
}

.resumo-legacy__row.is-expanded .resumo-legacy__prod-toggle-icon:not(.resumo-legacy__prod-toggle-icon--placeholder) {
  transform: rotate(90deg);
}

.resumo-legacy__row.has-children {
  cursor: pointer;
}

.resumo-legacy__prod-name {
  font-weight: 700;
  color: #0f172a;
  line-height: 1.3;
}

.resumo-legacy__prod-name--child {
  font-weight: 600;
  color: #6b7280;
}

.resumo-legacy__prod-meta {
  font-size: 12px;
  color: #64748b;
}

.resumo-legacy__col--peso,
.resumo-legacy__col--meta,
.resumo-legacy__col--real {
  text-align: center;
  font-variant-numeric: tabular-nums;
}

.resumo-legacy__col--month-head {
  text-align: center;
  font-size: 11px;
  letter-spacing: 0.08em;
}

.resumo-legacy__col--month-head.is-current {
  color: #1d4ed8;
}

.resumo-legacy__col--month {
  text-align: center;
  font-weight: 800;
  font-size: 13px;
  font-variant-numeric: tabular-nums;
  padding: 12px 14px;
  border-bottom: 1px solid var(--stroke, #e5e7eb);
  border: 1px solid transparent;
}

.resumo-legacy__col--month span {
  display: inline-block;
  min-width: 56px;
}

.resumo-legacy__col--month.att-low,
.resumo-legacy__col--month.pct-badge--low {
  background: #fff1f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.resumo-legacy__col--month.att-warn,
.resumo-legacy__col--month.pct-badge--warn {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  color: #92400e;
}

.resumo-legacy__col--month.att-ok,
.resumo-legacy__col--month.pct-badge--ok {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.resumo-legacy__col--month.is-empty {
  background: #f8fafc;
  color: #94a3b8;
  font-weight: 600;
}

.resumo-legacy__col--month.is-current {
  box-shadow: inset 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.resumo-legacy__col--month.is-current.att-low,
.resumo-legacy__col--month.is-current.pct-badge--low {
  box-shadow: inset 0 0 0 2px rgba(239, 68, 68, 0.25);
}

.resumo-legacy__col--month.is-current.att-warn,
.resumo-legacy__col--month.is-current.pct-badge--warn {
  box-shadow: inset 0 0 0 2px rgba(249, 115, 22, 0.25);
}

.resumo-legacy__col--month.is-current.att-ok {
  box-shadow: inset 0 0 0 2px rgba(34, 197, 94, 0.25);
}

.is-empty {
  color: #94a3b8;
}

.resumo-legacy__metric {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  min-width: 84px;
  background: #fff4f5;
  border: 1px solid #ffd7db;
  color: var(--brand, #b30000);
}

.resumo-legacy__table tbody td > .resumo-legacy__metric {
  margin-inline: auto;
}

.resumo-legacy__ating {
  display: flex;
  align-items: center;
  justify-content: center;
}

.resumo-legacy__ating-meter {
  position: relative;
  width: 136px;
  height: 18px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #f1f5f9;
  overflow: hidden;
}

.resumo-legacy__ating-meter::before {
  content: "";
  position: absolute;
  inset: 0;
  transform-origin: left center;
  transform: scaleX(var(--fill, 0));
  background: linear-gradient(90deg, #bbf7d0, #34d399);
}

.resumo-legacy__ating-meter.is-low::before {
  background: #fecaca;
}

.resumo-legacy__ating-meter.is-warn::before {
  background: #fed7aa;
}

.resumo-legacy__ating-meter.is-ok::before {
  background: #bbf7d0;
}

.resumo-legacy__ating-value {
  position: relative;
  display: block;
  width: 100%;
  text-align: center;
  font-size: 12px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: 0.04em;
}
</style>

