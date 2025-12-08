<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import Icon from '../components/Icon.vue'
import type { DetalhesItem } from '../services/detalhesService'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { usePeriodManager } from '../composables/usePeriodManager'
import { useDetalhesData } from '../composables/useDetalhesData'
import { formatINT, formatCurrency, formatDate } from '../utils/formatUtils'
import TreeTableRow, { type TreeNode } from '../components/TreeTableRow.vue'
import TableViewChips from '../components/TableViewChips.vue'
import DetailViewBar, { type DetailView } from '../components/DetailViewBar.vue'
import AppliedFiltersBar from '../components/AppliedFiltersBar.vue'
import DetailColumnDesigner from '../components/DetailColumnDesigner.vue'
import ErrorState from '../components/ErrorState.vue'
import EmptyState from '../components/EmptyState.vue'

const router = useRouter()
const { filterState } = useGlobalFilters()
const { period } = usePeriodManager()

const { detalhes: detalhesData, loading, error } = useDetalhesData(filterState, period)
const expandedRows = ref<Set<string>>(new Set())
const searchTerm = ref('')
const tableView = ref('diretoria')
const activeDetailViewId = ref('default')
const showColumnDesigner = ref(false)

const sortState = ref<{ id: string | null; direction: 'asc' | 'desc' | null }>({
  id: null,
  direction: null
})
const AVAILABLE_COLUMNS: string[] = [
  'realizado',
  'meta',
  'atingimento_p',
  'meta_diaria',
  'referencia_hoje',
  'pontos',
  'meta_diaria_necessaria',
  'peso',
  'projecao',
  'data'
]

const DEFAULT_COLUMNS = [...AVAILABLE_COLUMNS]

function sanitizeColumns(columns: string[]): string[] {
  const seen = new Set<string>()
  const filtered = columns.filter(column => {
    if (!AVAILABLE_COLUMNS.includes(column) || seen.has(column)) {
      return false
    }
    seen.add(column)
    return true
  })
  return filtered.length ? filtered : [...DEFAULT_COLUMNS]
}

const activeColumns = ref<string[]>(sanitizeColumns(DEFAULT_COLUMNS))

const TABLE_VIEWS = [
  { id: 'diretoria', label: 'Diretoria', key: 'diretoria' },
  { id: 'gerencia', label: 'Regional', key: 'gerenciaRegional' },
  { id: 'agencia', label: 'Agência', key: 'agencia' },
  { id: 'gGestao', label: 'Gerente de gestão', key: 'gerenteGestao' },
  { id: 'gerente', label: 'Gerente', key: 'gerente' },
  { id: 'secao', label: 'Família', key: 'secao' },
  { id: 'familia', label: 'Indicador', key: 'familia' },
  { id: 'prodsub', label: 'Subindicador', key: 'prodOrSub' },
  { id: 'contrato', label: 'Contratos', key: 'contrato' }
]

const detailViews = ref<DetailView[]>([
  {
    id: 'default',
    name: 'Visão padrão',
    columns: sanitizeColumns(DEFAULT_COLUMNS)
  }
])

const LEVEL_HIERARCHY: Record<string, string[]> = {
  diretoria: ['diretoria', 'regional', 'agencia', 'gGestao', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gerencia: ['regional', 'agencia', 'gGestao', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  agencia: ['agencia', 'gGestao', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gGestao: ['gGestao', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gerente: ['gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  secao: ['familia', 'indicador', 'subindicador', 'contrato'],
  familia: ['indicador', 'subindicador', 'contrato'],
  prodsub: ['subindicador', 'contrato'],
  contrato: ['contrato']
}

function getSortValue(node: TreeNode, columnId: string): number | string {
  if (columnId === '__label__') {
    return node.label || ''
  }

  const summary = node.summary
  switch (columnId) {
    case 'realizado':
      return summary.valor_realizado || 0
    case 'meta':
      return summary.valor_meta || 0
    case 'atingimento_v':
      return summary.atingimento_v || 0
    case 'atingimento_p':
      return summary.atingimento_p || 0
    case 'meta_diaria':
      return summary.meta_diaria || 0
    case 'referencia_hoje':
      return summary.referencia_hoje || 0
    case 'pontos':
      return summary.pontos || 0
    case 'meta_diaria_necessaria':
      return summary.meta_diaria_necessaria || 0
    case 'peso':
      return summary.peso || 0
    case 'projecao':
      return summary.projecao || 0
    case 'data':
      return summary.data || ''
    default:
      return 0
  }
}

function sortNodes(nodes: TreeNode[]): TreeNode[] {
  if (!sortState.value.id || !sortState.value.direction) {
    return nodes
  }

  const sorted = [...nodes].sort((a, b) => {
    const aVal = getSortValue(a, sortState.value.id!)
    const bVal = getSortValue(b, sortState.value.id!)

    let comparison = 0
    if (typeof aVal === 'number' && typeof bVal === 'number') {
      comparison = aVal - bVal
    } else {
      comparison = String(aVal).localeCompare(String(bVal), 'pt-BR')
    }

    return sortState.value.direction === 'asc' ? comparison : -comparison
  })

  return sorted.map(node => ({
    ...node,
    children: sortNodes(node.children)
  }))
}

const contratosData = computed(() => {
  if (!searchTerm.value.trim() || !detalhesData.value.length) return []

  const term = searchTerm.value.toLowerCase().trim()
  const filtered = detalhesData.value.filter(item =>
    item.id_contrato?.toLowerCase().includes(term) ||
    item.registro_id?.toLowerCase().includes(term) ||
    item.gerente_nome?.toLowerCase().includes(term) ||
    item.familia_nome?.toLowerCase().includes(term) ||
    item.ds_indicador?.toLowerCase().includes(term)
  )

  const contratos = new Map<string, DetalhesItem[]>()
  filtered.forEach(item => {
    const key = item.id_contrato || item.registro_id || 'sem-contrato'
    if (!contratos.has(key)) {
      contratos.set(key, [])
    }
    contratos.get(key)!.push(item)
  })

  const result: Array<{
    id: string
    contratoId: string
    items: DetalhesItem[]
    summary: ReturnType<typeof calculateSummary>
    detail: {
      canal_venda?: string
      tipo_venda?: string
      gerente?: string
      gerente_gestao?: string
      modalidade_pagamento?: string
      dt_vencimento?: string
      dt_cancelamento?: string
      motivo_cancelamento?: string
    }
  }> = []

  contratos.forEach((items, contratoId) => {
    const firstItem = items[0]
    if (!firstItem) return
    result.push({
      id: `contrato-${contratoId}`,
      contratoId,
      items,
      summary: calculateSummary(items),
      detail: {
        canal_venda: firstItem.canal_venda,
        tipo_venda: firstItem.tipo_venda,
        gerente: firstItem.gerente_nome,
        gerente_gestao: firstItem.gerente_gestao_nome,
        modalidade_pagamento: firstItem.modalidade_pagamento,
        dt_vencimento: firstItem.dt_vencimento,
        dt_cancelamento: firstItem.dt_cancelamento,
        motivo_cancelamento: firstItem.motivo_cancelamento
      }
    })
  })

  if (sortState.value.id && sortState.value.direction) {
    result.sort((a, b) => {
      let aVal: number | string = 0
      let bVal: number | string = 0

      if (sortState.value.id === '__label__') {
        aVal = a.contratoId
        bVal = b.contratoId
      } else {
        const summaryKey = sortState.value.id as keyof typeof a.summary
        aVal = a.summary[summaryKey] ?? 0
        bVal = b.summary[summaryKey] ?? 0
      }

      let comparison = 0
      if (typeof aVal === 'number' && typeof bVal === 'number') {
        comparison = aVal - bVal
      } else {
        comparison = String(aVal).localeCompare(String(bVal), 'pt-BR')
      }

      return sortState.value.direction === 'asc' ? comparison : -comparison
    })
  }

  return result
})

const treeData = computed(() => {
  if (!detalhesData.value.length || searchTerm.value.trim()) return []

  const hierarchy: string[] = (LEVEL_HIERARCHY[tableView.value] as string[]) || LEVEL_HIERARCHY.diretoria

  let result: TreeNode[] = []

  if (tableView.value === 'contrato') {
    const contratos = new Map<string, DetalhesItem[]>()
    detalhesData.value.forEach(item => {
      const key = item.id_contrato || item.registro_id || 'sem-contrato'
      if (!contratos.has(key)) {
        contratos.set(key, [])
      }
      contratos.get(key)!.push(item)
    })

    contratos.forEach((items, contratoId) => {
      const firstItem = items[0]
      if (!firstItem) return
      result.push({
        id: `contrato-${contratoId}`,
        label: contratoId,
        level: 'contrato' as const,
        children: [],
        data: items,
        summary: calculateSummary(items),
        detail: {
          canal_venda: firstItem.canal_venda,
          tipo_venda: firstItem.tipo_venda,
          gerente: firstItem.gerente_nome,
          gerente_gestao: firstItem.gerente_gestao_nome,
          modalidade_pagamento: firstItem.modalidade_pagamento,
          dt_vencimento: firstItem.dt_vencimento,
          dt_cancelamento: firstItem.dt_cancelamento,
          motivo_cancelamento: firstItem.motivo_cancelamento
        }
      })
    })
  } else {
    const hierarchyArray: string[] = hierarchy || LEVEL_HIERARCHY.diretoria
    result = buildTreeHierarchy(detalhesData.value, hierarchyArray, 0)
  }

  return sortNodes(result)
})

const showCards = computed(() => searchTerm.value.trim().length > 0)

function recalculateSummaryFromChildren(node: TreeNode): void {
  
  node.children.forEach(child => recalculateSummaryFromChildren(child))

  if (node.children.length > 0) {
    const pontosFromChildren = node.children.reduce((sum, child) => sum + (child.summary.pontos || 0), 0)
    const pesoFromChildren = node.children.reduce((sum, child) => sum + (child.summary.peso || 0), 0)

    node.summary.pontos = pontosFromChildren
    node.summary.peso = pesoFromChildren
  }
}

function buildTreeHierarchy(items: DetalhesItem[], hierarchy: string[], level: number): TreeNode[] {
  if (level >= hierarchy.length || items.length === 0) return []

  const currentLevel = hierarchy[level]
  const nextLevel = hierarchy[level + 1]

  const groups = new Map<string, DetalhesItem[]>()

  items.forEach(item => {
    let key: string
    let label: string

    switch (currentLevel) {
      case 'diretoria':
        key = item.diretoria_id || 'sem-diretoria'
        label = item.diretoria_nome || 'Sem diretoria'
        break
      case 'regional':
        key = item.gerencia_id || 'sem-regional'
        label = item.gerencia_nome || 'Sem regional'
        break
      case 'agencia':
        key = item.agencia_id || 'sem-agencia'
        label = item.agencia_nome || 'Sem agência'
        break
      case 'gGestao':
        key = item.gerente_gestao_id || 'sem-gerente-gestao'
        label = item.gerente_gestao_nome || 'Sem gerente de gestão'
        break
      case 'gerente':
        key = item.gerente_id || 'sem-gerente'
        label = item.gerente_nome || 'Sem gerente'
        break
      case 'familia':
        key = item.familia_id || 'sem-familia'
        label = item.familia_nome || 'Sem família'
        break
      case 'indicador':
        key = item.id_indicador || 'sem-indicador'
        label = item.ds_indicador || 'Sem indicador'
        break
      case 'subindicador':
        key = item.id_subindicador || 'sem-subindicador'
         
        label = item.subindicador || 'Sem subindicador'
        break
      case 'contrato':
        key = item.id_contrato || item.registro_id || 'sem-contrato'
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        label = key
        break
      default:
        key = 'unknown'
    }

    if (!groups.has(key)) {
      groups.set(key, [])
    }
    groups.get(key)!.push(item)
  })

  const nodes: TreeNode[] = []

  groups.forEach((groupItems, key) => {
    const firstItem = groupItems[0]
    if (!firstItem) return

    let label = firstItem.diretoria_nome || 'Sem label'

    switch (currentLevel) {
      case 'diretoria':
        label = firstItem.diretoria_nome || 'Sem diretoria'
        break
      case 'regional':
        label = firstItem.gerencia_nome || 'Sem regional'
        break
      case 'agencia':
        label = firstItem.agencia_nome || 'Sem agência'
        break
      case 'gGestao':
        label = firstItem.gerente_gestao_nome || 'Sem gerente de gestão'
        break
      case 'gerente':
        label = firstItem.gerente_nome || 'Sem gerente'
        break
      case 'familia':
        label = firstItem.familia_nome || 'Sem família'
        break
      case 'indicador':
        label = firstItem.ds_indicador || 'Sem indicador'
        break
      case 'subindicador':
        label = firstItem.subindicador || 'Sem subindicador'
        break
      case 'contrato':
        label = firstItem.id_contrato || firstItem.registro_id || 'Sem contrato'
        break
    }

    const node: TreeNode = {
      id: `${currentLevel}-${key}`,
      label,
      level: currentLevel as any,
      children: nextLevel ? buildTreeHierarchy(groupItems, hierarchy, level + 1) : [],
      data: groupItems,
      summary: calculateSummary(groupItems)
    }

    if (currentLevel === 'contrato') {
      node.detail = {
        canal_venda: firstItem.canal_venda || undefined,
        tipo_venda: firstItem.tipo_venda || undefined,
        gerente: firstItem.gerente_nome || undefined,
        modalidade_pagamento: firstItem.modalidade_pagamento || undefined,
        dt_vencimento: firstItem.dt_vencimento || undefined,
        dt_cancelamento: firstItem.dt_cancelamento || undefined,
        motivo_cancelamento: firstItem.motivo_cancelamento || undefined
      }
    }

    nodes.push(node)
  })

  nodes.forEach(node => recalculateSummaryFromChildren(node))

  return nodes
}

function calculateSummary(items: DetalhesItem[]) {
  const valor_realizado = items.reduce((sum, item) => sum + (item.valor_realizado || 0), 0)
  const valor_meta = items.reduce((sum, item) => sum + (item.valor_meta || item.meta_mensal || 0), 0)
  const pontos = items.reduce((sum, item) => sum + (item.peso || 0), 0)
  const peso = items.reduce((sum, item) => sum + (item.peso || 0), 0)

  const diasTotais = 30
  const diasDecorridos = new Date().getDate()
  const diasRestantes = Math.max(1, diasTotais - diasDecorridos)

  const meta_diaria = diasTotais > 0 ? (valor_meta / diasTotais) : 0
  const referencia_hoje = diasDecorridos > 0 ? Math.min(valor_meta, meta_diaria * diasDecorridos) : 0
  const meta_diaria_necessaria = diasRestantes > 0 ? Math.max(0, (valor_meta - valor_realizado) / diasRestantes) : 0
  const projecao = diasDecorridos > 0 ? (valor_realizado / Math.max(diasDecorridos, 1)) * diasTotais : valor_realizado

  const atingimento_v = valor_realizado - valor_meta
  const atingimento_p = valor_meta > 0 ? (valor_realizado / valor_meta) * 100 : 0

  const firstItem = items.length > 0 ? items[0] : null
  const data = firstItem ? (firstItem.data || firstItem.competencia || '') : ''

  return {
    valor_realizado,
    valor_meta,
    atingimento_v,
    atingimento_p,
    meta_diaria,
    referencia_hoje,
    pontos,
    meta_diaria_necessaria,
    peso,
    projecao,
    data
  }
}

const detailOpenRows = ref<Set<string>>(new Set())

function handleAction(payload: { type: 'ticket' | 'opportunities', node: TreeNode }) {
  if (payload.type === 'ticket') {
    
    const observation = buildTicketObservation(payload.node)
    const params = new URLSearchParams({
      openDrawer: 'true',
      intent: 'new-ticket',
      preferredQueue: 'POBJ',
      queue: 'POBJ',
      observation
    })
    
    const omegaRoute = router.resolve({ name: 'Omega', query: Object.fromEntries(params) })
    window.open(omegaRoute.href, '_blank')
  } else if (payload.type === 'opportunities') {
    
    console.log('Abrir oportunidades para:', payload.node)
  }
}

function buildTicketObservation(node: TreeNode): string {
  const parts: string[] = []
  
  if (node.label) {
    parts.push(`Item: ${node.label}`)
  }
  
  if (node.level) {
    parts.push(`Nível: ${node.level}`)
  }
  
  if (node.summary) {
    const summary = node.summary
    if (summary.valor_realizado) {
      parts.push(`Realizado: R$ ${summary.valor_realizado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`)
    }
    if (summary.valor_meta) {
      parts.push(`Meta: R$ ${summary.valor_meta.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`)
    }
    if (summary.atingimento_p !== undefined) {
      parts.push(`Atingimento: ${summary.atingimento_p.toFixed(1)}%`)
    }
  }
  
  if (node.detail) {
    const detailInfo = node.detail
    if (detailInfo.gerente) {
      parts.push(`Gerente: ${detailInfo.gerente}`)
    }
    if (detailInfo.canal_venda) {
      parts.push(`Canal: ${detailInfo.canal_venda}`)
    }
    if (detailInfo.tipo_venda) {
      parts.push(`Tipo de venda: ${detailInfo.tipo_venda}`)
    }
  }
  
  return parts.join('\n')
}

function toggleRow(nodeId: string) {
  const node = findNodeById(treeData.value, nodeId)
  const isContract = node?.level === 'contrato'

  if (expandedRows.value.has(nodeId)) {
    expandedRows.value.delete(nodeId)
    if (isContract) {
      detailOpenRows.value.delete(nodeId)
    }
  } else {
    expandedRows.value.add(nodeId)
    if (isContract) {
      detailOpenRows.value.add(nodeId)
    }
  }
}

function findNodeById(nodes: TreeNode[], id: string): TreeNode | null {
  for (const node of nodes) {
    if (node.id === id) return node
    const found = findNodeById(node.children, id)
    if (found) return found
  }
  return null
}

const firstColumnLabel = computed(() => {
  const view = TABLE_VIEWS.find(v => v.id === tableView.value)
  if (view) {
    return view.label
  }
  return 'Item'
})

function getColumnLabel(columnId: string): string {
  const columnMap: Record<string, string> = {
    realizado: 'Realizado no período (R$)',
    meta: 'Meta no período (R$)',
    atingimento_p: 'Atingimento (%)',
    meta_diaria: 'Meta diária total (R$)',
    referencia_hoje: 'Referência para hoje (R$)',
    pontos: 'Pontos no período (pts)',
    meta_diaria_necessaria: 'Meta diária necessária (R$)',
    peso: 'Peso (pts)',
    projecao: 'Projeção (R$)',
    data: 'Data'
  }
  return columnMap[columnId] || columnId
}

function getSortIcon(columnId: string): string {
  if (sortState.value.id !== columnId || !sortState.value.direction) {
    return 'arrows-up-down'
  }
  return sortState.value.direction === 'asc' ? 'arrow-up' : 'arrow-down'
}

function handleSort(columnId: string) {
  const prev = sortState.value
  const isString = columnId === '__label__'
  const defaultDir: 'asc' | 'desc' = isString ? 'asc' : 'desc'
  const oppositeDir: 'asc' | 'desc' = defaultDir === 'asc' ? 'desc' : 'asc'

  let nextDirection: 'asc' | 'desc' | null

  if (prev.id !== columnId || !prev.direction) {
    nextDirection = defaultDir
  } else if (prev.direction === defaultDir) {
    nextDirection = oppositeDir
  } else {
    nextDirection = null
  }

  if (nextDirection) {
    sortState.value = { id: columnId, direction: nextDirection }
  } else {
    sortState.value = { id: null, direction: null }
  }
}

function isExpanded(nodeId: string): boolean {
  return expandedRows.value.has(nodeId)
}

function expandAll() {
  const allIds: string[] = []
  function collectIds(nodes: TreeNode[]) {
    nodes.forEach(node => {
      if (node.children.length > 0) {
        allIds.push(node.id)
        collectIds(node.children)
      }
    })
  }
  collectIds(treeData.value)
  expandedRows.value = new Set(allIds)
}

function collapseAll() {
  expandedRows.value.clear()
}

function handleTableViewChange(viewId: string) {
  tableView.value = viewId
}

function handleDetailViewChange(viewId: string) {
  activeDetailViewId.value = viewId
  const view = detailViews.value.find(v => v.id === viewId)
  if (view) {
    const sanitized = sanitizeColumns(view.columns)
    view.columns = sanitized
    activeColumns.value = [...sanitized]
  }
}

function handleOpenColumnDesigner() {
  showColumnDesigner.value = true
}

function handleSaveView(name: string, columns: string[]) {
  const sanitizedColumns = sanitizeColumns(columns)
  const existingView = detailViews.value.find(v => v.name.toLowerCase() === name.toLowerCase() && v.id !== 'default')
  if (existingView) {
    existingView.columns = [...sanitizedColumns]
    activeDetailViewId.value = existingView.id
  } else {
    const customViews = detailViews.value.filter(v => v.id !== 'default')
    if (customViews.length >= 5) {
      alert('Você já possui 5 visões personalizadas. Exclua uma antes de criar outra.')
      return
    }
    const newView: DetailView = {
      id: `custom-${Date.now()}`,
      name,
      columns: [...sanitizedColumns]
    }
    detailViews.value.push(newView)
    activeDetailViewId.value = newView.id
  }
  activeColumns.value = [...sanitizedColumns]
  localStorage.setItem('pobj3:detailViews', JSON.stringify(detailViews.value.filter(v => v.id !== 'default')))
  localStorage.setItem('pobj3:detailActiveView', activeDetailViewId.value)
}

function handleDeleteView(viewId: string) {
  if (viewId === 'default') return
  const index = detailViews.value.findIndex(v => v.id === viewId)
  if (index > -1) {
    detailViews.value.splice(index, 1)
    if (activeDetailViewId.value === viewId) {
      activeDetailViewId.value = 'default'
      const defaultView = detailViews.value.find(v => v.id === 'default')
      if (defaultView) {
        const sanitized = sanitizeColumns(defaultView.columns)
        defaultView.columns = sanitized
        activeColumns.value = [...sanitized]
      }
    }
    localStorage.setItem('pobj3:detailViews', JSON.stringify(detailViews.value.filter(v => v.id !== 'default')))
    localStorage.setItem('pobj3:detailActiveView', activeDetailViewId.value)
  }
}

function handleApplyColumns(columns: string[]) {
  const sanitizedColumns = sanitizeColumns(columns)
  activeColumns.value = [...sanitizedColumns]
  if (activeDetailViewId.value === '__custom__' || activeDetailViewId.value.startsWith('custom-')) {
    const customView = detailViews.value.find(v => v.id === activeDetailViewId.value)
    if (customView) {
      customView.columns = [...sanitizedColumns]
      localStorage.setItem('pobj3:detailViews', JSON.stringify(detailViews.value.filter(v => v.id !== 'default')))
    }
  }
  localStorage.setItem('pobj3:detailActiveView', activeDetailViewId.value)
}

onMounted(() => {
  try {
    const saved = localStorage.getItem('pobj3:detailViews')
    if (saved) {
      const parsed = JSON.parse(saved)
      if (Array.isArray(parsed)) {
        detailViews.value = [
          ...detailViews.value,
          ...parsed.map((v: any) => ({
            id: v.id || `custom-${Date.now()}`,
            name: v.name || 'Visão personalizada',
            columns: sanitizeColumns(Array.isArray(v.columns) ? v.columns : [])
          }))
        ]
      }
    }

    const activeId = localStorage.getItem('pobj3:detailActiveView')
    if (activeId) {
      const view = detailViews.value.find(v => v.id === activeId)
      if (view) {
        activeDetailViewId.value = activeId
        const sanitized = sanitizeColumns(view.columns)
        view.columns = sanitized
        activeColumns.value = [...sanitized]
      }
    }
  } catch (e) {
    console.error('Erro ao carregar visões salvas:', e)
  }
})
</script>

<template>
  <div class="detalhes-wrapper">
    <div class="detalhes-view">
        <div class="card card--detalhes">
          <header class="card__header">
            <div class="title-subtitle">
              <h3>Detalhamento</h3>
              <p class="muted">Visualize os contratos em uma estrutura hierárquica</p>
            </div>
            <div class="card__actions">
              <div class="search-box">
                <input
                  v-model="searchTerm"
                  type="text"
                  placeholder="Contrato (Ex.: 999999)"
                  class="input input--search"
                />
              </div>
            </div>
          </header>

          <AppliedFiltersBar />

          <div class="table-controls">
            <div class="table-controls__main">
              <div class="table-controls__chips">
                <TableViewChips
                  :views="TABLE_VIEWS"
                  :active-view="tableView"
                  @view-change="handleTableViewChange"
                />
              </div>
              <div class="table-controls__search">
              </div>
            </div>
          </div>

          <DetailViewBar
            :views="detailViews"
            :active-view-id="activeDetailViewId"
            @view-change="handleDetailViewChange"
          />

          
          <div class="table-toolbar-wrapper">
            <div class="table-toolbar">
              <button
                type="button"
                class="table-toolbar__btn"
                @click="expandAll"
              >
                <span class="table-toolbar__icon"><Icon name="chevrons-down" :size="16" /></span>
                <span class="table-toolbar__text">Expandir tudo</span>
              </button>
              <button
                type="button"
                class="table-toolbar__btn"
                @click="collapseAll"
              >
                <span class="table-toolbar__icon"><Icon name="chevrons-up" :size="16" /></span>
                <span class="table-toolbar__text">Recolher tudo</span>
              </button>
              <button
                type="button"
                class="table-toolbar__btn detail-view-manage"
                title="Personalizar colunas da tabela"
                @click="handleOpenColumnDesigner"
              >
                <span class="table-toolbar__icon"><Icon name="columns" :size="16" /></span>
                <span class="table-toolbar__text">Personalizar colunas</span>
              </button>
            </div>
          </div>

          <template v-if="loading">
            <div class="detalhes-skeleton">
              <div class="skeleton skeleton--table-header" style="height: 40px; width: 100%; margin-bottom: 12px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
              <div class="skeleton skeleton--table-row" style="height: 48px; width: 100%; margin-bottom: 8px; border-radius: 8px;"></div>
          </div>
          </template>

          <template v-else>
            <ErrorState v-if="error" :message="error" />

            <EmptyState
              v-else-if="showCards && contratosData.length === 0"
              title="Nenhum contrato encontrado"
              message="Nenhum contrato encontrado para a busca."
            />

            <EmptyState
              v-else-if="!showCards && treeData.length === 0"
              title="Nenhum dado encontrado"
              message="Nenhum dado encontrado para os filtros selecionados."
            />

          <div v-else-if="showCards" class="contratos-grid">
            <div
              v-for="contrato in contratosData"
              :key="contrato.id"
              class="contrato-card"
            >
              <div class="contrato-card__header">
                <h4 class="contrato-card__title">{{ contrato.contratoId }}</h4>
                <div class="contrato-card__badge" :class="{
                  'is-success': contrato.summary.atingimento_p >= 100,
                  'is-warning': contrato.summary.atingimento_p >= 50 && contrato.summary.atingimento_p < 100,
                  'is-danger': contrato.summary.atingimento_p < 50
                }">
                  {{ contrato.summary.atingimento_p.toFixed(1) }}%
                </div>
              </div>

              <div class="contrato-card__body">
                <div class="contrato-card__info">
                  <div class="contrato-card__info-item">
                    <span class="contrato-card__label">Gerente:</span>
                    <span class="contrato-card__value">{{ contrato.detail.gerente || '—' }}</span>
                  </div>
                  <div v-if="contrato.detail.gerente_gestao" class="contrato-card__info-item">
                    <span class="contrato-card__label">Gerente de gestão:</span>
                    <span class="contrato-card__value">{{ contrato.detail.gerente_gestao }}</span>
                  </div>
                  <div class="contrato-card__info-item">
                    <span class="contrato-card__label">Canal:</span>
                    <span class="contrato-card__value">{{ contrato.detail.canal_venda || '—' }}</span>
                  </div>
                  <div class="contrato-card__info-item">
                    <span class="contrato-card__label">Tipo:</span>
                    <span class="contrato-card__value">{{ contrato.detail.tipo_venda || '—' }}</span>
                  </div>
                  <div class="contrato-card__info-item">
                    <span class="contrato-card__label">Modalidade:</span>
                    <span class="contrato-card__value">{{ contrato.detail.modalidade_pagamento || '—' }}</span>
                  </div>
                </div>

                <div class="contrato-card__metrics">
                  <div class="contrato-card__metric">
                    <span class="contrato-card__metric-label">Realizado</span>
                    <span class="contrato-card__metric-value">{{ formatCurrency(contrato.summary.valor_realizado) }}</span>
                  </div>
                  <div class="contrato-card__metric">
                    <span class="contrato-card__metric-label">Meta</span>
                    <span class="contrato-card__metric-value">{{ formatCurrency(contrato.summary.valor_meta) }}</span>
                  </div>
                  <div class="contrato-card__metric">
                    <span class="contrato-card__metric-label">Pontos</span>
                    <span class="contrato-card__metric-value">{{ formatINT(contrato.summary.pontos) }}</span>
                  </div>
                  <div class="contrato-card__metric">
                    <span class="contrato-card__metric-label">Peso</span>
                    <span class="contrato-card__metric-value">{{ formatINT(contrato.summary.peso) }}</span>
                  </div>
                </div>

                <div v-if="contrato.detail.dt_vencimento" class="contrato-card__dates">
                  <div class="contrato-card__date-item">
                    <span class="contrato-card__date-label">Vencimento:</span>
                    <span class="contrato-card__date-value">{{ formatDate(contrato.detail.dt_vencimento) }}</span>
                  </div>
                  <div v-if="contrato.detail.dt_cancelamento" class="contrato-card__date-item">
                    <span class="contrato-card__date-label">Cancelamento:</span>
                    <span class="contrato-card__date-value">{{ formatDate(contrato.detail.dt_cancelamento) }}</span>
                  </div>
                </div>

                <div v-if="contrato.detail.motivo_cancelamento" class="contrato-card__cancelamento">
                  <span class="contrato-card__cancelamento-label">Motivo:</span>
                  <span class="contrato-card__cancelamento-value">{{ contrato.detail.motivo_cancelamento }}</span>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="table-wrapper">
            <table class="tree-table">
                  <thead>
                    <tr>
                      <th>
                        <button
                          type="button"
                          class="tree-sort"
                          :aria-pressed="sortState.id === '__label__' && sortState.direction ? 'true' : 'false'"
                          @click="handleSort('__label__')"
                        >
                          {{ firstColumnLabel }}
                          <span class="tree-sort__icon"><Icon :name="getSortIcon('__label__')" :size="16" /></span>
                        </button>
                      </th>
                  <th
                    v-for="columnId in activeColumns"
                    :key="columnId"
                    class="col-number"
                  >
                    <button
                      type="button"
                      class="tree-sort"
                      :aria-pressed="sortState.id === columnId && sortState.direction ? 'true' : 'false'"
                      @click="handleSort(columnId)"
                    >
                      {{ getColumnLabel(columnId) }}
                      <span class="tree-sort__icon"><Icon :name="getSortIcon(columnId)" :size="16" /></span>
                    </button>
                  </th>
                </tr>
              </thead>
              <tbody>
                <template v-for="node in treeData" :key="node.id">
                  <TreeTableRow
                    :node="node"
                    :level="0"
                    :expanded="isExpanded(node.id)"
                    :expanded-rows="expandedRows"
                    :detail-open="detailOpenRows.has(node.id)"
                    :active-columns="activeColumns"
                    @toggle="(id) => toggleRow(id)"
                    @action="handleAction"
                  />
                </template>
              </tbody>
            </table>
          </div>
          </template>
        </div>
      </div>

      
      <DetailColumnDesigner
        v-model="showColumnDesigner"
        :selected-columns="activeColumns"
        :views="detailViews"
        :active-view-id="activeDetailViewId"
        @apply="handleApplyColumns"
        @save="handleSaveView"
        @load-view="handleDetailViewChange"
        @delete-view="handleDeleteView"
      />
    </div>
</template>

<style scoped>
.detalhes-wrapper {
  --brand: var(--brad-color-primary, #cc092f);
  --brand-dark: var(--brad-color-primary-dark, #9d0b21);
  --info: var(--brad-color-accent, #517bc5);
  --bg: var(--brad-color-neutral-0, #fff);
  --panel: var(--brad-color-neutral-0, #fff);
  --stroke: var(--brad-color-gray-light, #ebebeb);
  --text: var(--brad-color-neutral-100, #000);
  --muted: var(--brad-color-gray-dark, #999);
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);
  --ring: 0 0 0 3px rgba(204, 9, 47, 0.12);
  --omega-gradient: linear-gradient(135deg, rgba(36, 107, 253, 0.12), rgba(255, 255, 255, 0));
  --omega-sidebar-bg: linear-gradient(180deg, rgba(246, 247, 252, 0.88) 0%, rgba(255, 255, 255, 0.95) 100%);
  --omega-badge-bg: rgba(36, 107, 253, 0.12);
  --omega-success: #16a34a;
  --omega-warning: #f97316;
  --omega-danger: #dc2626;
  --omega-progress: #2563eb;
  --text-muted: var(--brad-color-gray-dark, #999);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  color: var(--text);
  font-family: var(--brad-font-family);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  box-sizing: border-box;
}

.detalhes-view {
  width: 100%;
  margin-top: 24px;
}


.card {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 16px;
  margin-bottom: 12px;
  box-sizing: border-box;
}

.card--detalhes {
  padding: 0;
  overflow: hidden;
}

.card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 16px;
  border-bottom: 1px solid var(--stroke);
}

.card__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
}

.title-subtitle h3 {
  margin: 0 0 4px 0;
  font-size: 20px;
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text);
  line-height: 1.2;
}

.title-subtitle .muted {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
}

.search-box {
  flex: 0 0 320px;
  max-width: 360px;
  width: 100%;
}

.input--search {
  width: 100%;
  background: var(--panel);
  color: var(--text);
  border: 1px solid var(--stroke);
  border-radius: 12px;
  padding: 10px 12px;
  outline: none;
  font-size: 14px;
  box-sizing: border-box;
}

.input--search:focus {
  box-shadow: var(--ring);
  border-color: var(--info);
}

.table-controls {
  padding: 12px 16px;
  border-bottom: 1px solid var(--stroke);
  background: var(--bg);
}

.table-controls__main {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 16px;
  align-items: flex-end;
  justify-content: space-between;
  margin: 8px 0 12px;
}

.table-controls__chips {
  flex: 1 1 360px;
  min-width: 240px;
  display: flex;
  align-items: flex-end;
}

.table-controls__search {
  flex: 0 1 320px;
  min-width: 220px;
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
}

.table-toolbar-wrapper {
  padding: 12px 16px;
  border-bottom: 1px solid var(--stroke);
  background: var(--bg);
}

.table-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
  align-items: stretch;
  margin: 6px 0 12px;
}

.table-toolbar__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-width: 118px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid var(--stroke);
  background: var(--panel);
  color: var(--brand);
  font-weight: 700;
  font-size: 13px;
  box-shadow: var(--shadow);
  transition: all 0.2s ease;
  cursor: pointer;
  box-sizing: border-box;
  font-family: inherit;
  outline: none;
}

.table-toolbar__btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(179, 0, 0, 0.15);
  border-color: var(--brand);
  background: rgba(179, 0, 0, 0.08);
}

.table-toolbar__btn .table-toolbar__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: rgba(179, 0, 0, 0.12);
  color: var(--brand);
}

.table-toolbar__btn .table-toolbar__icon i {
  font-size: 15px;
}

.table-toolbar__btn .table-toolbar__text {
  font-size: 13px;
  color: inherit;
}

.table-toolbar__btn.is-active {
  background: var(--brand);
  color: #fff;
  box-shadow: 0 4px 12px rgba(179, 0, 0, 0.25);
  border-color: var(--brand);
}

.table-toolbar__btn.is-active .table-toolbar__icon {
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
}

.table-wrapper {
  overflow-x: auto;
  width: 100%;
  max-width: 100%;
  padding: 16px;
}

.tree-table {
  width: 100%;
  min-width: 720px;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border: 1px solid var(--stroke);
  border-radius: 14px;
  overflow: hidden;
  box-sizing: border-box;
}

:deep(.tree-table thead th) {
  background: #fbfcff;
  color: #475569;
  font-weight: 800;
  font-size: 12px;
  padding: 10px 8px;
  border-bottom: 1px solid #e5e7eb;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  vertical-align: middle;
}

:deep(.tree-table thead th:first-child) {
  text-align: left;
}

:deep(.tree-table thead th.col-number) {
  text-align: center;
}

:deep(.tree-table thead th:not(:first-child):not(.col-number)) {
  text-align: center;
}

:deep(.tree-table tbody td:first-child) {
  text-align: left;
  padding-left: 0;
}

.tree-sort {
  width: 100%;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  background: none;
  font: inherit;
  font-weight: var(--brad-font-weight-bold, 700);
  color: inherit;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 999px;
  transition: color 0.15s ease, background-color 0.15s ease;
  font-family: var(--brad-font-family, inherit);
}

:deep(.tree-table thead th:first-child .tree-sort) {
  justify-content: flex-start;
}

:deep(.tree-table thead th.col-number .tree-sort) {
  justify-content: center;
}

:deep(.tree-table thead th:not(:first-child):not(.col-number) .tree-sort) {
  justify-content: center;
}

.tree-sort:hover:not([disabled]) {
  color: var(--brand, #cc092f);
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
}

.tree-sort[aria-pressed="true"] {
  color: var(--brand, #cc092f);
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
}

.tree-sort[disabled] {
  cursor: default;
  opacity: 0.65;
}

.tree-sort__icon {
  display: inline-flex;
  align-items: center;
  font-size: 16px;
  line-height: 1;
}

:deep(.tree-table tbody td) {
  padding: 10px 8px;
  font-size: 13px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

:deep(.tree-table tbody td.col-number) {
  text-align: center;
}

:deep(.tree-table tbody td:not(.col-number):not(:first-child)) {
  text-align: center;
}

:deep(.tree-table tbody tr:hover) {
  background: #fcfdff;
}

:deep(.tree-table tbody tr:last-child td) {
  border-bottom: none;
}

:deep(.tree-table .tree-cell) {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: flex-start;
  min-height: 28px;
  white-space: nowrap;
}

:deep(.tree-table .label-strong) {
  font-weight: 800;
  color: #111827;
  line-height: 1.25;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

:deep(.tree-table .toggle--placeholder) {
  visibility: hidden;
  pointer-events: none;
}

.col-expand {
  width: 50px;
  text-align: left;
}

.col-label {
  min-width: 200px;
  text-align: left;
}

:deep(.tree-table .col-number) {
  text-align: center;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.detalhes-skeleton {
  padding: 16px;
}


:deep(.tree-row.lvl-0 .tree-cell) {
  padding-left: 8px;
}

:deep(.tree-row.lvl-1 .tree-cell) {
  padding-left: 28px;
}

:deep(.tree-row.lvl-2 .tree-cell) {
  padding-left: 48px;
}

:deep(.tree-row.lvl-3 .tree-cell) {
  padding-left: 68px;
}

:deep(.tree-row.lvl-4 .tree-cell) {
  padding-left: 88px;
}

:deep(.tree-row.lvl-5 .tree-cell) {
  padding-left: 108px;
}

:deep(.tree-row.lvl-6 .tree-cell) {
  padding-left: 128px;
}

:deep(.tree-row.lvl-7 .tree-cell) {
  padding-left: 148px;
}

:deep(.tree-row.lvl-8 .tree-cell) {
  padding-left: 168px;
}

@media (max-width: 900px) {
  .table-toolbar {
    justify-content: stretch;
  }

  .table-toolbar__btn {
    flex: 1 1 calc(50% - 12px);
    min-width: 0;
  }

  .search-box {
    flex: 1 1 auto;
    min-width: 0;
  }
}


.detail-view-manage i {
  margin-right: 6px;
}

@media (max-width: 900px) {
  .table-controls__main {
    flex-direction: column;
    align-items: stretch;
  }

  .table-controls__chips {
    width: 100%;
  }

  .table-controls__search {
    flex: 1 1 auto;
    min-width: 0;
    justify-content: flex-start;
  }

  .table-toolbar {
    justify-content: stretch;
  }

  .table-toolbar__btn {
    flex: 1 1 calc(50% - 12px);
    min-width: 0;
  }
}

@media (max-width: 768px) {
  .table-wrapper {
    overflow-x: auto;
  }

  .table-wrapper .tree-table {
    min-width: 720px;
    font-size: 12px;
  }

  .tree-table tbody td {
    padding: 10px 8px;
  }

  .tree-table .tree-cell {
    gap: 8px;
  }

  .tree-toggle {
    width: 26px;
    height: 26px;
  }
}

@media (max-width: 640px) {
  .table-toolbar__btn {
    flex: 1 1 100%;
  }
}

.contratos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
  padding: 16px;
}

.contrato-card {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.contrato-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 32px rgba(17, 23, 41, 0.12);
}

.contrato-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: linear-gradient(135deg, rgba(36, 107, 253, 0.08), rgba(255, 255, 255, 0));
  border-bottom: 1px solid var(--stroke);
}

.contrato-card__title {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: var(--text);
}

.contrato-card__badge {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  background: var(--omega-badge-bg);
  color: var(--info);
}

.contrato-card__badge.is-success {
  background: rgba(22, 163, 74, 0.12);
  color: var(--omega-success);
}

.contrato-card__badge.is-warning {
  background: rgba(249, 115, 22, 0.12);
  color: var(--omega-warning);
}

.contrato-card__badge.is-danger {
  background: rgba(220, 38, 38, 0.12);
  color: var(--omega-danger);
}

.contrato-card__body {
  padding: 16px;
}

.contrato-card__info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--stroke);
}

.contrato-card__info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.contrato-card__label {
  color: var(--muted);
  font-weight: 600;
}

.contrato-card__value {
  color: var(--text);
  font-weight: 500;
}

.contrato-card__metrics {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--stroke);
}

.contrato-card__metric {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.contrato-card__metric-label {
  font-size: 12px;
  color: var(--muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.contrato-card__metric-value {
  font-size: 16px;
  font-weight: 800;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

.contrato-card__dates {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
  font-size: 13px;
}

.contrato-card__date-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.contrato-card__date-label {
  color: var(--muted);
  font-weight: 600;
}

.contrato-card__date-value {
  color: var(--text);
  font-weight: 500;
}

.contrato-card__cancelamento {
  padding: 12px;
  background: rgba(220, 38, 38, 0.08);
  border-radius: 8px;
  font-size: 13px;
}

.contrato-card__cancelamento-label {
  display: block;
  color: var(--omega-danger);
  font-weight: 700;
  margin-bottom: 4px;
}

.contrato-card__cancelamento-value {
  color: var(--text);
  font-weight: 500;
}

@media (max-width: 768px) {
  .contratos-grid {
    grid-template-columns: 1fr;
  }
}
</style>

