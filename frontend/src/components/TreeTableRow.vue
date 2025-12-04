<script setup lang="ts">
import { computed } from 'vue'
import Icon from './Icon.vue'
import { formatINT, formatDate, formatBRL, formatBRLReadable, formatIntReadable } from '../utils/formatUtils'

export type TreeNode = {
  id: string
  label: string
  level: 'segmento' | 'diretoria' | 'regional' | 'agencia' | 'gGestao' | 'gerente' | 'familia' | 'indicador' | 'subindicador' | 'contrato'
  children: TreeNode[]
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  data: any[]
  summary: {
    valor_realizado: number
    valor_meta: number
    atingimento_v: number
    atingimento_p: number
    meta_diaria: number
    referencia_hoje: number
    pontos: number
    meta_diaria_necessaria: number
    peso: number
    projecao: number
    data: string
  }
  detail?: {
    canal_venda?: string
    tipo_venda?: string
    gerente?: string
    gerente_gestao?: string
    modalidade_pagamento?: string
    dt_vencimento?: string
    dt_cancelamento?: string
    motivo_cancelamento?: string
  }
}

type Props = {
  node: TreeNode
  level: number
  expanded: boolean
  expandedRows?: Set<string>
  detailOpen?: boolean
  activeColumns?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  expandedRows: () => new Set<string>(),
  detailOpen: false,
  activeColumns: () => []
})
const emit = defineEmits<{
  toggle: [id: string]
  action: [{ type: 'ticket' | 'opportunities', node: TreeNode }]
}>()

const hasChildren = computed(() => props.node.children.length > 0)

const atingimento = computed(() => {
  return props.node.summary.atingimento_p || 0
})

const atingimentoClass = computed(() => {
  const at = atingimento.value
  if (at >= 100) return 'text-success'
  if (at >= 80) return 'text-warning'
  return 'text-danger'
})

function getColumnValue(columnId: string) {
  const summary = props.node.summary
  switch (columnId) {
    case 'realizado':
      return formatBRLReadable(summary.valor_realizado)
    case 'meta':
      return formatBRLReadable(summary.valor_meta)
    case 'atingimento_p':
      return `${formatIntReadable(summary.atingimento_p)}%`
    case 'meta_diaria':
      return formatBRLReadable(summary.meta_diaria)
    case 'referencia_hoje':
      return formatBRLReadable(summary.referencia_hoje)
    case 'pontos':
      return `${formatIntReadable(summary.pontos)} pts`
    case 'meta_diaria_necessaria':
      return formatBRLReadable(summary.meta_diaria_necessaria)
    case 'peso':
      return `${formatIntReadable(summary.peso)} pts`
    case 'projecao':
      return formatBRLReadable(summary.projecao)
    case 'data':
      return formatDate(summary.data)
    default:
      return '—'
  }
}

function getColumnTooltip(columnId: string) {
  const summary = props.node.summary
  switch (columnId) {
    case 'realizado':
      return formatBRL(summary.valor_realizado)
    case 'meta':
      return formatBRL(summary.valor_meta)
    case 'atingimento_p':
      return `${formatINT(summary.atingimento_p)}%`
    case 'meta_diaria':
      return formatBRL(summary.meta_diaria)
    case 'referencia_hoje':
      return formatBRL(summary.referencia_hoje)
    case 'pontos':
      return `${formatINT(summary.pontos)} pts`
    case 'meta_diaria_necessaria':
      return formatBRL(summary.meta_diaria_necessaria)
    case 'peso':
      return `${formatINT(summary.peso)} pts`
    case 'projecao':
      return formatBRL(summary.projecao)
    case 'data':
      return formatDate(summary.data)
    default:
      return ''
  }
}

const isCanceled = computed(() => {
  return !!props.node.detail?.dt_cancelamento
})

const contractId = computed(() => {
  const id = props.node.label || ''
  if (!id) return '—'
  if (id.startsWith('CT-')) return id
  const match = id.match(/(\d{4})-?(\d+)/)
  if (match) {
    return `${match[1]}-${match[2]}`
  }
  return id
})
</script>

<template>
  <tr :class="['tree-row', 'lvl-' + level, { 'tree-row--expanded': expanded }]">
    <td>
      <div class="tree-cell">
        <button
          v-if="hasChildren || node.level === 'contrato'"
          type="button"
          class="toggle"
          :class="{ 'is-expanded': expanded || (node.level === 'contrato' && expanded) }"
          @click="emit('toggle', node.id)"
        >
          <Icon :name="(expanded || (node.level === 'contrato' && expanded)) ? 'chevron-down' : 'chevron-right'" :size="16" />
        </button>
        <span v-else class="toggle toggle--placeholder" aria-hidden="true"></span>
        <span class="label-strong">{{ node.label || '—' }}</span>
      </div>
    </td>
    <template v-for="columnId in activeColumns" :key="columnId">
      <td v-if="columnId === 'atingimento_p'" class="col-number">
        <span :class="atingimentoClass" :title="getColumnTooltip(columnId)" class="cell-content">
          {{ getColumnValue(columnId) }}
        </span>
      </td>
      <td v-else :class="columnId === 'data' ? 'col-number col-date' : 'col-number'">
        <span v-if="columnId !== 'data'" :title="getColumnTooltip(columnId)" class="cell-content">{{ getColumnValue(columnId) }}</span>
        <span v-else class="cell-content">{{ getColumnValue(columnId) }}</span>
      </td>
    </template>
    <td class="actions-cell">
      <span class="actions-group">
        <button
          type="button"
          class="icon-btn"
          title="Abrir chamado"
          aria-label="Abrir chamado"
          @click.stop="emit('action', { type: 'ticket', node })"
        >
          <Icon name="ticket" :size="16" />
        </button>
        <button
          v-if="node.level !== 'contrato'"
          type="button"
          class="icon-btn"
          title="Ver oportunidades"
          aria-label="Ver oportunidades"
          @click.stop="emit('action', { type: 'opportunities', node })"
        >
          <Icon name="bulb" :size="16" />
        </button>
      </span>
    </td>
  </tr>
  
  <!-- Card de detalhes do contrato -->
  <tr v-if="node.level === 'contrato' && expanded" class="tree-row tree-detail-row">
    <td :colspan="activeColumns.length + 2" class="tree-detail-cell">
      <div class="contract-detail-card">
        <!-- Barra superior com resumo -->
        <div class="contract-detail-card__header">
          <div class="contract-detail-card__header-left">
            <div class="contract-detail-card__id">
              <span class="contract-detail-card__id-label">CT-{{ contractId }}</span>
              <span v-if="isCanceled" class="contract-detail-card__badge contract-detail-card__badge--canceled">
                <Icon name="alert-triangle" :size="16" />
                Cancelado
              </span>
            </div>
          </div>
          <div class="contract-detail-card__header-center">
            <div class="contract-detail-card__summary">
              <span class="contract-detail-card__summary-item">{{ formatBRLReadable(node.summary.valor_realizado) }}</span>
              <span class="contract-detail-card__summary-item">—</span>
              <span class="contract-detail-card__summary-item">—</span>
              <span class="contract-detail-card__summary-item">{{ formatBRLReadable(node.summary.meta_diaria) }}</span>
              <span class="contract-detail-card__summary-item">{{ formatBRLReadable(node.summary.referencia_hoje) }}</span>
              <span class="contract-detail-card__summary-item">{{ formatIntReadable(node.summary.pontos) }} pts</span>
              <span class="contract-detail-card__summary-item">{{ formatBRLReadable(node.summary.meta_diaria_necessaria) }}</span>
              <span class="contract-detail-card__summary-item">{{ formatIntReadable(node.summary.peso) }} pts</span>
              <span class="contract-detail-card__summary-item">{{ formatBRLReadable(node.summary.projecao) }}</span>
            </div>
          </div>
          <div class="contract-detail-card__header-right">
            <span class="contract-detail-card__date">{{ formatDate(node.summary.data) }}</span>
            <Icon name="ticket" class="contract-detail-card__icon" :size="20" />
          </div>
        </div>

        <!-- Aviso de cancelamento -->
        <div v-if="isCanceled" class="contract-detail-card__warning">
          <div class="contract-detail-card__warning-content">
            <Icon name="alert-triangle" class="contract-detail-card__warning-icon" :size="20" />
            <div class="contract-detail-card__warning-text">
              <strong class="contract-detail-card__warning-title">Venda cancelada</strong>
              <p class="contract-detail-card__warning-details">
                Cancelado em {{ formatDate(node.detail?.dt_cancelamento || '') }}. {{ node.detail?.motivo_cancelamento || 'Solicitação do cliente' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tabela de detalhes -->
        <div class="contract-detail-card__table-wrapper">
          <table class="contract-detail-card__table">
            <thead>
              <tr>
                <th>Canal da venda</th>
                <th>Tipo da venda</th>
                <th>Gerente</th>
                <th>Condição de pagamento</th>
                <th>Data de vencimento</th>
                <th>Data de cancelamento</th>
                <th>Motivo do cancelamento</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ node.detail?.canal_venda || '—' }}</td>
                <td>{{ node.detail?.tipo_venda || '—' }}</td>
                <td>{{ node.detail?.gerente || '—' }}</td>
                <td>{{ node.detail?.modalidade_pagamento || '—' }}</td>
                <td>{{ node.detail?.dt_vencimento ? formatDate(node.detail.dt_vencimento) : '—' }}</td>
                <td>{{ node.detail?.dt_cancelamento ? formatDate(node.detail.dt_cancelamento) : '—' }}</td>
                <td>{{ node.detail?.motivo_cancelamento || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </td>
  </tr>
  
  <template v-if="expanded && hasChildren">
      <TreeTableRow
        v-for="child in node.children"
        :key="child.id"
        :node="child"
        :level="level + 1"
        :expanded="expandedRows?.has(child.id) || false"
        :expanded-rows="expandedRows"
        :detail-open="false"
        :active-columns="activeColumns"
        @toggle="emit('toggle', $event)"
      />
  </template>
</template>

<style scoped>
.tree-row {
  transition: background 0.15s ease;
}

.tree-row:hover {
  background: var(--bg, #f6f7fc);
}

.tree-row--expanded {
  background: var(--bg, #f6f7fc);
}

.toggle {
  flex: 0 0 28px;
  width: 28px;
  height: 26px;
  min-width: 28px;
  display: grid;
  place-items: center;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 8px;
  background: var(--panel, #fff);
  cursor: pointer;
  margin-right: 4px;
  transition: all 0.15s ease;
  color: var(--muted, #6b7280);
  box-sizing: border-box;
  flex-shrink: 0;
  font-family: var(--brad-font-family, inherit);
}

.toggle--placeholder {
  visibility: hidden;
  pointer-events: none;
}

.toggle:hover {
  box-shadow: 0 4px 10px rgba(17, 23, 41, 0.08);
  transform: translateY(-1px);
  border-color: var(--brand, #cc092f);
}

.toggle.is-expanded {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
  border-color: var(--brand, #cc092f);
  color: var(--brand, #cc092f);
}

.toggle[disabled] {
  opacity: 0.45;
  cursor: default;
}

.toggle i {
  font-size: 16px;
  line-height: 1;
}

.label-strong {
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text, #0f1424);
  line-height: 1.25;
  font-size: 13px;
  font-family: var(--brad-font-family, inherit);
}

.text-success {
  color: var(--omega-success, #16a34a);
  font-weight: 800;
}

.text-warning {
  color: var(--omega-warning, #f97316);
  font-weight: 800;
}

.text-danger {
  color: var(--omega-danger, #dc2626);
  font-weight: 800;
}

.tree-detail-row td {
  padding: 0;
  border-bottom: 1px solid var(--stroke, #e7eaf2);
  background: transparent;
}

.tree-detail-row:last-of-type td {
  border-bottom: none;
}

.contract-detail-card {
  background: var(--panel, #fff);
  border-radius: 12px;
  border: 1px solid var(--stroke, #e7eaf2);
  margin: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: #fef2f2;
  border-bottom: 1px solid #fee2e2;
  gap: 16px;
}

.contract-detail-card__header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.contract-detail-card__id {
  display: flex;
  align-items: center;
  gap: 12px;
}

.contract-detail-card__id-label {
  font-size: 15px;
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--brand, #cc092f);
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.contract-detail-card__badge--canceled {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.contract-detail-card__header-center {
  flex: 1 1 auto;
  min-width: 0;
  overflow-x: auto;
}

.contract-detail-card__summary {
  display: flex;
  align-items: center;
  gap: 16px;
  white-space: nowrap;
}

.contract-detail-card__summary-item {
  font-size: 13px;
  font-weight: var(--brad-font-weight-semibold, 600);
  color: var(--text, #0f1424);
  white-space: nowrap;
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__header-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.contract-detail-card__date {
  font-size: 13px;
  font-weight: var(--brad-font-weight-semibold, 600);
  color: var(--text, #0f1424);
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__icon {
  font-size: 20px;
  color: var(--muted, #6b7280);
}

.contract-detail-card__warning {
  margin: 16px 20px;
  padding: 14px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
}

.contract-detail-card__warning-content {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.contract-detail-card__warning-icon {
  font-size: 18px;
  color: #dc2626;
  flex-shrink: 0;
  margin-top: 2px;
}

.contract-detail-card__warning-text {
  flex: 1;
}

.contract-detail-card__warning-title {
  display: block;
  font-size: 14px;
  font-weight: 800;
  color: #dc2626;
  margin-bottom: 4px;
}

.contract-detail-card__warning-details {
  font-size: 13px;
  color: #991b1b;
  margin: 0;
  line-height: 1.5;
}

.contract-detail-card__table-wrapper {
  padding: 0 20px 20px;
  overflow-x: auto;
}

.contract-detail-card__table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: var(--panel, #fff);
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__table thead th {
  background: var(--bg, #f6f7fc);
  color: var(--text, #0f1424);
  font-weight: var(--brad-font-weight-bold, 700);
  font-size: 12px;
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid var(--stroke, #e7eaf2);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__table tbody td {
  padding: 12px 16px;
  font-size: 13px;
  color: var(--text, #0f1424);
  border-bottom: 1px solid var(--stroke, #e7eaf2);
  vertical-align: middle;
  font-family: var(--brad-font-family, inherit);
}

.contract-detail-card__table tbody tr:last-child td {
  border-bottom: none;
}

/* Cores condicionais para Atingimento */
.cell-content {
  display: inline-block;
}

.actions-cell {
  padding: 8px 12px;
  text-align: right;
  white-space: nowrap;
}

.actions-group {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--stroke, #e7eaf2);
  background: var(--panel, #fff);
  color: var(--muted, #6b7280);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-sizing: border-box;
  font-family: var(--brad-font-family, inherit);
  outline: none;
  padding: 0;
}

.icon-btn:hover,
.icon-btn:focus-visible {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
  color: var(--brand, #cc092f);
  border-color: var(--brand-light, rgba(204, 9, 47, 0.35));
  outline: none;
}

.icon-btn i {
  font-size: 18px;
  line-height: 1;
}
</style>

