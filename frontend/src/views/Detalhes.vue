<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { getDetalhes, type DetalhesItem, type DetalhesFilters } from '../services/detalhesService'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { usePeriodManager } from '../composables/usePeriodManager'
import { formatINT, formatCurrency, formatDate } from '../utils/formatUtils'
import Filters from '../components/Filters.vue'
import TabsNavigation from '../components/TabsNavigation.vue'
import TreeTableRow from '../components/TreeTableRow.vue'
import TableViewChips from '../components/TableViewChips.vue'
import DetailViewBar, { type DetailView } from '../components/DetailViewBar.vue'
import AppliedFiltersBar from '../components/AppliedFiltersBar.vue'
import DetailColumnDesigner from '../components/DetailColumnDesigner.vue'

const { filterState } = useGlobalFilters()
const { period } = usePeriodManager()

const detalhesData = ref<DetalhesItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const expandedRows = ref<Set<string>>(new Set())
const searchTerm = ref('')
const compactMode = ref(false)
const tableView = ref('diretoria')
const activeDetailViewId = ref('default')
const showColumnDesigner = ref(false)

// Estado de ordenação
const sortState = ref<{ id: string | null; direction: 'asc' | 'desc' | null }>({
  id: null,
  direction: null
})
// Todas as colunas habilitadas por padrão (na ordem correta)
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

// Visões da tabela (chips principais)
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

// Visões personalizadas da tabela (colunas)
const detailViews = ref<DetailView[]>([
  {
    id: 'default',
    name: 'Visão padrão',
    columns: sanitizeColumns(DEFAULT_COLUMNS)
  }
])

// Agrupa dados por hierarquia
interface TreeNode {
  id: string
  label: string
  level: 'segmento' | 'diretoria' | 'regional' | 'agencia' | 'gerente' | 'familia' | 'indicador' | 'subindicador' | 'contrato'
  children: TreeNode[]
  data: DetalhesItem[]
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
  expanded?: boolean
  detail?: {
    canal_venda?: string
    tipo_venda?: string
    gerente?: string
    modalidade_pagamento?: string
    dt_vencimento?: string
    dt_cancelamento?: string
    motivo_cancelamento?: string
  }
}

const loadDetalhes = async () => {
  loading.value = true
  error.value = null

  try {
    const filters: DetalhesFilters = {
      segmento: filterState.value.segmento || undefined,
      diretoria: filterState.value.diretoria || undefined,
      regional: filterState.value.gerencia || undefined,
      agencia: filterState.value.agencia || undefined,
      gerente: filterState.value.gerente || undefined,
      familia: filterState.value.familia || undefined,
      indicador: filterState.value.indicador || undefined,
      subindicador: filterState.value.subindicador || undefined,
      dataInicio: period.value.start,
      dataFim: period.value.end
    }

    const data = await getDetalhes(filters)
    if (data) {
      detalhesData.value = data
    } else {
      error.value = 'Não foi possível carregar os dados de detalhes'
      detalhesData.value = []
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Erro ao carregar detalhes'
    detalhesData.value = []
  } finally {
    loading.value = false
  }
}

// Mapeamento de níveis hierárquicos
const LEVEL_HIERARCHY: Record<string, string[]> = {
  diretoria: ['diretoria', 'regional', 'agencia', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gerencia: ['regional', 'agencia', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  agencia: ['agencia', 'gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gGestao: ['gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  gerente: ['gerente', 'familia', 'indicador', 'subindicador', 'contrato'],
  secao: ['familia', 'indicador', 'subindicador', 'contrato'],
  familia: ['indicador', 'subindicador', 'contrato'],
  prodsub: ['subindicador', 'contrato'],
  contrato: ['contrato']
}

// Função para obter o valor de ordenação de um node
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

// Função para ordenar nodes recursivamente
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

  // Ordena os filhos recursivamente
  return sorted.map(node => ({
    ...node,
    children: sortNodes(node.children)
  }))
}

// Agrupa dados em árvore hierárquica baseado na visão selecionada
const treeData = computed(() => {
  if (!detalhesData.value.length) return []

  // Filtra por termo de busca se houver
  let filtered = detalhesData.value
  if (searchTerm.value.trim()) {
    const term = searchTerm.value.toLowerCase().trim()
    filtered = detalhesData.value.filter(item =>
      item.id_contrato?.toLowerCase().includes(term) ||
      item.gerente_nome?.toLowerCase().includes(term) ||
      item.familia_nome?.toLowerCase().includes(term) ||
      item.ds_indicador?.toLowerCase().includes(term)
    )
  }

  // Obtém a hierarquia baseada na visão selecionada
  const hierarchy: string[] = (LEVEL_HIERARCHY[tableView.value] as string[]) || LEVEL_HIERARCHY.diretoria

  let result: TreeNode[] = []

  // Se a visão for 'contrato', mostra todos os contratos diretamente
  if (tableView.value === 'contrato') {
    const contratos = new Map<string, DetalhesItem[]>()
    filtered.forEach(item => {
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
          modalidade_pagamento: firstItem.modalidade_pagamento,
          dt_vencimento: firstItem.dt_vencimento,
          dt_cancelamento: firstItem.dt_cancelamento,
          motivo_cancelamento: firstItem.motivo_cancelamento
        }
      })
    })
  } else {
    // Agrupa começando pelo primeiro nível da hierarquia
    const hierarchyArray: string[] = hierarchy || LEVEL_HIERARCHY.diretoria
    result = buildTreeHierarchy(filtered, hierarchyArray, 0)
  }

  // Aplica ordenação se houver
  return sortNodes(result)
})

function buildTreeHierarchy(items: DetalhesItem[], hierarchy: string[], level: number): TreeNode[] {
  if (level >= hierarchy.length || items.length === 0) return []

  const currentLevel = hierarchy[level]
  const nextLevel = hierarchy[level + 1]

  // Agrupa pelo nível atual
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
        label = key
        break
      default:
        key = 'unknown'
        label = 'Desconhecido'
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

    // Se for contrato, adiciona detalhes (sempre, mesmo que vazio)
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

  return nodes
}

function calculateSummary(items: DetalhesItem[]) {
  const valor_realizado = items.reduce((sum, item) => sum + (item.valor_realizado || 0), 0)
  const valor_meta = items.reduce((sum, item) => sum + (item.valor_meta || item.meta_mensal || 0), 0)
  const pontos = items.reduce((sum, item) => sum + (item.peso || 0), 0) // Assumindo que pontos = peso
  const peso = items.reduce((sum, item) => sum + (item.peso || 0), 0)

  // Calcular dias para meta diária (assumindo 30 dias por padrão)
  const diasTotais = 30
  const diasDecorridos = new Date().getDate()
  const diasRestantes = Math.max(1, diasTotais - diasDecorridos)

  const meta_diaria = diasTotais > 0 ? (valor_meta / diasTotais) : 0
  const referencia_hoje = diasDecorridos > 0 ? Math.min(valor_meta, meta_diaria * diasDecorridos) : 0
  const meta_diaria_necessaria = diasRestantes > 0 ? Math.max(0, (valor_meta - valor_realizado) / diasRestantes) : 0
  const projecao = diasDecorridos > 0 ? (valor_realizado / Math.max(diasDecorridos, 1)) * diasTotais : valor_realizado

  const atingimento_v = valor_realizado - valor_meta
  const atingimento_p = valor_meta > 0 ? (valor_realizado / valor_meta) * 100 : 0

  // Data mais recente
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
    // Se for um contrato, também abre os detalhes
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

// Label dinâmico da primeira coluna baseado na visão selecionada
const firstColumnLabel = computed(() => {
  const view = TABLE_VIEWS.find(v => v.id === tableView.value)
  if (view) {
    return view.label
  }
  return 'Item'
})

// Função para obter o label de uma coluna
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

// Função para obter o ícone de ordenação
function getSortIcon(columnId: string): string {
  if (sortState.value.id !== columnId || !sortState.value.direction) {
    return 'ti ti-arrows-up-down'
  }
  return sortState.value.direction === 'asc' ? 'ti ti-arrow-up' : 'ti ti-arrow-down'
}

// Função para lidar com o clique no botão de ordenação
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

function toggleCompactMode() {
  compactMode.value = !compactMode.value
}

function handleTableViewChange(viewId: string) {
  tableView.value = viewId
  // Aqui você pode implementar a lógica para mudar a agregação dos dados
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
  // Verificar se já existe uma visão com o mesmo nome
  const existingView = detailViews.value.find(v => v.name.toLowerCase() === name.toLowerCase() && v.id !== 'default')
  if (existingView) {
    // Atualizar visão existente
    existingView.columns = [...sanitizedColumns]
    activeDetailViewId.value = existingView.id
  } else {
    // Verificar limite de 5 visões personalizadas (sem contar a padrão)
    const customViews = detailViews.value.filter(v => v.id !== 'default')
    if (customViews.length >= 5) {
      alert('Você já possui 5 visões personalizadas. Exclua uma antes de criar outra.')
      return
    }
    // Criar nova visão
    const newView: DetailView = {
      id: `custom-${Date.now()}`,
      name,
      columns: [...sanitizedColumns]
    }
    detailViews.value.push(newView)
    activeDetailViewId.value = newView.id
  }
  activeColumns.value = [...sanitizedColumns]
  // Salvar no localStorage
  localStorage.setItem('pobj3:detailViews', JSON.stringify(detailViews.value.filter(v => v.id !== 'default')))
  localStorage.setItem('pobj3:detailActiveView', activeDetailViewId.value)
}

function handleDeleteView(viewId: string) {
  if (viewId === 'default') return
  const index = detailViews.value.findIndex(v => v.id === viewId)
  if (index > -1) {
    detailViews.value.splice(index, 1)
    // Se a visão deletada era a ativa, voltar para a padrão
    if (activeDetailViewId.value === viewId) {
      activeDetailViewId.value = 'default'
      const defaultView = detailViews.value.find(v => v.id === 'default')
      if (defaultView) {
        const sanitized = sanitizeColumns(defaultView.columns)
        defaultView.columns = sanitized
        activeColumns.value = [...sanitized]
      }
    }
    // Salvar no localStorage
    localStorage.setItem('pobj3:detailViews', JSON.stringify(detailViews.value.filter(v => v.id !== 'default')))
    localStorage.setItem('pobj3:detailActiveView', activeDetailViewId.value)
  }
}

function handleApplyColumns(columns: string[]) {
  const sanitizedColumns = sanitizeColumns(columns)
  activeColumns.value = [...sanitizedColumns]
  // Atualiza a visão customizada se estiver ativa
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
  // Carrega visões salvas do localStorage
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

  loadDetalhes()
})

watch([filterState, period], () => {
  loadDetalhes()
}, { deep: true })
</script>

<template>
  <div class="detalhes-wrapper">
    <div class="container">
      <Filters />
      <TabsNavigation />

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
                  placeholder="Contrato (Ex.: CT-AAAA-999999)"
                  class="input input--search"
                />
              </div>
            </div>
          </header>

          <!-- Barra de filtros aplicados -->
          <AppliedFiltersBar />

          <!-- Controles da tabela -->
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
                <!-- Search já está no header -->
              </div>
            </div>
          </div>

          <!-- Barra de visões da tabela -->
          <DetailViewBar
            :views="detailViews"
            :active-view-id="activeDetailViewId"
            @view-change="handleDetailViewChange"
          />

          <!-- Toolbar -->
          <div class="table-toolbar-wrapper">
            <div class="table-toolbar">
              <button
                type="button"
                class="table-toolbar__btn"
                @click="expandAll"
              >
                <span class="table-toolbar__icon"><i class="ti ti-chevrons-down"></i></span>
                <span class="table-toolbar__text">Expandir tudo</span>
              </button>
              <button
                type="button"
                class="table-toolbar__btn"
                @click="collapseAll"
              >
                <span class="table-toolbar__icon"><i class="ti ti-chevrons-up"></i></span>
                <span class="table-toolbar__text">Recolher tudo</span>
              </button>
              <button
                type="button"
                class="table-toolbar__btn"
                :class="{ 'is-active': compactMode }"
                :aria-pressed="compactMode"
                @click="toggleCompactMode"
              >
                <span class="table-toolbar__icon">
                  <i :class="compactMode ? 'ti ti-arrows-minimize' : 'ti ti-layout-collage'"></i>
                </span>
                <span class="table-toolbar__text">{{ compactMode ? 'Modo confortável' : 'Modo compacto' }}</span>
              </button>
              <button
                type="button"
                class="table-toolbar__btn detail-view-manage"
                title="Personalizar colunas da tabela"
                @click="handleOpenColumnDesigner"
              >
                <span class="table-toolbar__icon"><i class="ti ti-columns"></i></span>
                <span class="table-toolbar__text">Personalizar colunas</span>
              </button>
            </div>
          </div>

          <div v-if="loading" class="loading-state">
            <p>Carregando detalhes...</p>
          </div>

          <div v-else-if="error" class="error-state">
            <p>{{ error }}</p>
          </div>

          <div v-else-if="treeData.length === 0" class="empty-state">
            <p>Nenhum dado encontrado para os filtros selecionados.</p>
          </div>

          <div v-else class="table-wrapper" :class="{ 'is-compact': compactMode }">
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
                          <span class="tree-sort__icon"><i :class="getSortIcon('__label__')"></i></span>
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
                      <span class="tree-sort__icon"><i :class="getSortIcon(columnId)"></i></span>
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
                  />
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de personalização de colunas -->
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
  --omega-gradient: linear-gradient(135deg, rgba(36, 107, 253, 0.12), rgba(255, 255, 255, 0));
  --omega-sidebar-bg: linear-gradient(180deg, rgba(246, 247, 252, 0.88) 0%, rgba(255, 255, 255, 0.95) 100%);
  --omega-badge-bg: rgba(36, 107, 253, 0.12);
  --omega-success: #16a34a;
  --omega-warning: #f97316;
  --omega-danger: #dc2626;
  --omega-progress: #2563eb;
  --text-muted: #64748b;

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  background-color: var(--bg);
  color: var(--text);
  font-family: "Plus Jakarta Sans", Inter, system-ui, "Segoe UI", Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  box-sizing: border-box;
}

.container {
  max-width: min(1600px, 96vw);
  margin: 18px auto;
  padding: 0 16px;
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
  font-weight: 800;
  color: var(--text);
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
  padding: 6px 12px;
  border-radius: 10px;
  border: 1px solid var(--stroke);
  background: var(--panel);
  color: var(--info);
  font-weight: 700;
  font-size: 12px;
  box-shadow: var(--shadow);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
  cursor: pointer;
  box-sizing: border-box;
}

.table-toolbar__btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 32px rgba(17, 23, 41, 0.12);
}

.table-toolbar__btn .table-toolbar__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: var(--omega-badge-bg);
  color: var(--info);
}

.table-toolbar__btn .table-toolbar__icon i {
  font-size: 15px;
}

.table-toolbar__btn .table-toolbar__text {
  font-size: 12px;
  color: inherit;
}

.table-toolbar__btn.is-active {
  background: var(--info);
  color: #fff;
  box-shadow: 0 18px 34px rgba(36, 107, 253, 0.24);
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
  font-weight: 800;
  color: inherit;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 999px;
  transition: color 0.15s ease, background-color 0.15s ease;
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
  color: #1d4ed8;
  background: rgba(199, 210, 254, 0.35);
}

.tree-sort[aria-pressed="true"] {
  color: #1d4ed8;
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
  /* O padding-left será aplicado via classes lvl-* para indentação */
}

.col-label {
  min-width: 200px;
  text-align: left;
  /* O padding-left será aplicado via classes lvl-* para indentação */
}

:deep(.tree-table .col-number) {
  text-align: center;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.loading-state,
.error-state,
.empty-state {
  padding: 48px 24px;
  text-align: center;
  color: var(--muted);
  font-size: 14px;
}

.error-state {
  color: var(--omega-danger);
}

/* Recuos por nível - indentação para a direita conforme o nível aumenta */
/* Recuos por nível - aplicado na tree-cell (como no app.js) */
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

/* Modo compacto da tabela */
.table-wrapper.is-compact .tree-table tbody td {
  padding: 6px 6px;
}

:deep(.table-wrapper.is-compact .toggle) {
  width: 24px;
  height: 22px;
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
</style>

