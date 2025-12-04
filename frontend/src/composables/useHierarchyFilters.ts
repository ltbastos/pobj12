import { ref, computed, type Ref } from 'vue'
import type { InitData } from '../services/initService'
import type { FilterOption, HierarchySelection } from '../types'

type InitDataReadonly = {
  readonly segmentos: readonly any[]
  readonly diretorias: readonly any[]
  readonly regionais: readonly any[]
  readonly agencias: readonly any[]
  readonly gerentes_gestao: readonly any[]
  readonly gerentes: readonly any[]
  readonly familias: readonly any[]
  readonly indicadores: readonly any[]
  readonly subindicadores: readonly any[]
  readonly status_indicadores: readonly any[]
}

type InitDataCompatible = InitData | InitDataReadonly

const normalizeId = (v: any): string =>
  v == null || v === '' ? '' : String(v).trim()

const formatIdNome = (id: string, nome: string) =>
  !id && !nome ? '' : !id ? nome : !nome || nome === id ? id : `${id} - ${nome}`

const formatFuncionalNome = (funcional: string, nome: string) =>
  !funcional && !nome
    ? ''
    : !funcional
    ? nome
    : !nome || nome === funcional
    ? funcional
    : `${funcional} - ${nome}`

const normalizeOption = (item: any, type: string): FilterOption => {
  const nome = String(item.nome ?? item.label ?? '').trim()
  let id = ''
  let funcional: string | undefined

  if (type === 'ggestao' || type === 'gerente') {
    funcional = normalizeId(item.funcional ?? '')
    id = normalizeId(item.id ?? '')
  } else {
    id = normalizeId(
      item.id ??
      item.codigo ??
      item.id_diretoria ??
      item.id_regional ??
      item.id_agencia ??
      ''
    )
    funcional = item.funcional ? normalizeId(item.funcional) : undefined
  }

  return {
    id,
    nome: (type === 'ggestao' || type === 'gerente') ? formatFuncionalNome(funcional || id, nome) : formatIdNome(id, nome),
    id_segmento: normalizeId(item.segmento_id ?? item.id_segmento ?? item.idSegmento),
    id_diretoria: normalizeId(item.diretoria_id ?? item.id_diretoria ?? item.idDiretoria),
    id_regional: normalizeId(item.regional_id ?? item.id_regional ?? item.idRegional ?? item.gerencia_id ?? item.gerenciaId),
    id_agencia: normalizeId(item.agencia_id ?? item.id_agencia ?? item.idAgencia),
    id_gestor: normalizeId(item.id_gestor ?? item.idGestor ?? item.gerente_gestao_id ?? item.gerenteGestaoId),
    funcional
  }
}

export function useHierarchyFilters(estruturaData: Ref<InitDataCompatible | null> | { readonly value: InitDataCompatible | null }) {
  const state = {
    segmento: ref(''),
    diretoria: ref(''),
    gerencia: ref(''), 
    agencia: ref(''),
    ggestao: ref(''),
    gerente: ref('')
  }

  const normalized = computed(() => {
    if (!estruturaData.value) return null
    return {
      segmentos: (estruturaData.value.segmentos ?? []).map(i => normalizeOption(i, 'segmento')),
      diretorias: (estruturaData.value.diretorias ?? []).map(i => normalizeOption(i, 'diretoria')),
      regionais: (estruturaData.value.regionais ?? []).map(i => normalizeOption(i, 'regional')),
      agencias: (estruturaData.value.agencias ?? []).map(i => normalizeOption(i, 'agencia')),
      ggestoes: (estruturaData.value.gerentes_gestao ?? []).map(i => normalizeOption(i, 'ggestao')),
      gerentes: (estruturaData.value.gerentes ?? []).map(i => normalizeOption(i, 'gerente'))
    }
  })

  const maps = computed(() => {
    if (!normalized.value) return null
    const idx = (arr: any[]) => Object.fromEntries((arr ?? []).map(i => [normalizeId(i.id), i]))
    return {
      segmento: idx(normalized.value.segmentos),
      diretoria: idx(normalized.value.diretorias),
      gerencia: idx(normalized.value.regionais),
      agencia: idx(normalized.value.agencias),
      ggestao: idx(normalized.value.ggestoes),
      gerente: idx(normalized.value.gerentes)
    }
  })

  const parentKeyByChild: Record<keyof HierarchySelection, string | null> = {
    segmento: null,
    diretoria: 'id_segmento', 
    gerencia: 'id_diretoria', 
    agencia: 'id_regional',    
    ggestao: 'id_agencia',     
    gerente: 'id_gestor'       
  }

  const parentFieldByChild: Record<keyof HierarchySelection, keyof HierarchySelection | null> = {
    segmento: null,
    diretoria: 'segmento',
    gerencia: 'diretoria', 
    agencia: 'gerencia',   
    ggestao: 'agencia',
    gerente: 'ggestao'
  }

  function autoFillParent(childField: keyof HierarchySelection, childValue: string) {
    if (!childValue) return
    const parentField = parentFieldByChild[childField]
    const parentKey = parentKeyByChild[childField]
    if (!parentField || !parentKey) return
    const childMeta = maps.value?.[childField]?.[normalizeId(childValue)]
    if (!childMeta) return

    const parentId = normalizeId((childMeta as any)[parentKey] ?? '')
    if (!parentId) return

    const parentMeta = maps.value?.[parentField]?.[parentId]
    if (!parentMeta) return

    state[parentField].value = parentMeta.id

    console.debug('[hierarchy] autoFillParent:', { childField, childValue, parentField, parentId })

    autoFillParent(parentField, parentMeta.id)
  }

  const stateOrder: (keyof HierarchySelection)[] = ['segmento', 'diretoria', 'gerencia', 'agencia', 'ggestao', 'gerente']

  function clearChildrenFrom(field: keyof HierarchySelection) {
    let clearing = false
    for (const k of stateOrder) {
      if (clearing) state[k].value = ''
      if (k === field) clearing = true
    }
  }

  function onChange(field: keyof HierarchySelection, value: string) {
    state[field].value = normalizeId(value)
    clearChildrenFrom(field)
    
    autoFillParent(field, state[field].value)
  }

  const segmentos = computed(() => normalized.value?.segmentos ?? [])
  const diretorias = computed(() => normalized.value?.diretorias ?? [])
  const regionais = computed(() => normalized.value?.regionais ?? [])
  const agencias = computed(() => normalized.value?.agencias ?? [])
  const gerentesGestao = computed(() => normalized.value?.ggestoes ?? [])
  const gerentes = computed(() => normalized.value?.gerentes ?? [])

  const clearAll = () => {
    for (const k of stateOrder) state[k].value = ''
  }

  return {
    ...state,
    segmentos,
    diretorias,
    regionais,
    agencias,
    gerentesGestao,
    gerentes,
    handleSegmentoChange: (v: string) => onChange('segmento', v),
    handleDiretoriaChange: (v: string) => onChange('diretoria', v),
    handleGerenciaChange: (v: string) => onChange('gerencia', v),
    handleAgenciaChange: (v: string) => onChange('agencia', v),
    handleGerenteGestaoChange: (v: string) => onChange('ggestao', v),
    handleGerenteChange: (v: string) => onChange('gerente', v),
    clearAll
  }
}
