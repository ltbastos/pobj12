export type FilterOption = {
  id: string
  nome: string
  id_segmento?: string
  id_diretoria?: string
  id_regional?: string
  id_agencia?: string
  id_gestor?: string
  funcional?: string
  id_familia?: string
  id_indicador?: string
}

export type HierarchySelection = {
  segmento: string
  diretoria: string
  gerencia: string
  agencia: string
  ggestao: string
  gerente: string
}

export type InitData = {
  segmentos: any[]
  diretorias: any[]
  regionais: any[]
  agencias: any[]
  gerentes_gestao: any[]
  gerentes: any[]
  familias: any[]
  indicadores: any[]
  subindicadores: any[]
  status_indicadores: any[]
}

export type SegmentoItem = {
  id: string | number
  nome: string
}

export type Period = {
  start: string
  end: string
}

export type BusinessSnapshot = {
  total: number
  elapsed: number
  remaining: number
  monthStart: string
  monthEnd: string
  today: string
}

export type CalendarioItem = {
  data: string
  competencia: string
  ano: string
  mes: string
  mesNome: string
  mesAnoCurto: string
  dia: string
  diaSemana: string
  semana: string
  trimestre: string
  semestre: string
  ehDiaUtil: number
}

export type ApiResponse<T = any> = {
  success: boolean
  data?: T
  error?: string
  message?: string
}

export type DetalhesItem = {
  registro_id?: string
  id_contrato?: string
  data?: string
  competencia?: string
  ano?: number
  mes?: number
  mes_nome?: string
  segmento_id?: string
  segmento?: string
  diretoria_id?: string
  diretoria_nome?: string
  gerencia_id?: string
  gerencia_nome?: string
  agencia_id?: string
  agencia_nome?: string
  gerente_id?: string
  gerente_nome?: string
  gerente_gestao_id?: string
  gerente_gestao_nome?: string
  familia_id?: string
  familia_nome?: string
  id_indicador?: string
  ds_indicador?: string
  id_subindicador?: string
  subindicador?: string
  peso?: number
  valor_realizado?: number
  valor_meta?: number
  meta_mensal?: number
  canal_venda?: string
  tipo_venda?: string
  modalidade_pagamento?: string
  dt_vencimento?: string
  dt_cancelamento?: string
  motivo_cancelamento?: string
  status_id?: number
}

export type DetalhesFilters = {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerente?: string
  gerenteGestao?: string
  familia?: string
  indicador?: string
  subindicador?: string
  dataInicio?: string
  dataFim?: string
}

export type Produto = {
  id: string
  id_familia: string
  familia: string
  id_indicador: string
  indicador: string
  id_subindicador?: string
  subindicador?: string
  metrica: string
  peso: number
  meta?: number
  realizado?: number
  pontos?: number
  pontos_meta?: number
  variavel_meta?: number
  variavel_realizado?: number
  ating?: number
  atingido?: boolean
  ultima_atualizacao?: string
}

export type ProdutoFilters = {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  familia?: string
  indicador?: string
  subindicador?: string
  dataInicio?: string
  dataFim?: string
  status?: string
}

export type ProdutoMensal = {
  id: string
  id_indicador: string
  indicador: string
  id_familia: string
  familia: string
  id_subindicador?: string
  subindicador?: string
  metrica: string
  peso: number
  meta?: number
  realizado?: number
  pontos?: number
  pontos_meta?: number
  ating?: number
  atingido?: boolean
  ultima_atualizacao?: string
  meses: Array<{
    mes: string
    meta: number
    realizado: number
    atingimento: number
  }>
}

export type RankingItem = {
  data?: string
  competencia?: string
  segmento?: string
  segmento_id?: string
  diretoria_id?: string
  diretoria_nome?: string
  gerencia_id?: string
  gerencia_nome?: string
  agencia_id?: string
  agencia_nome?: string
  gerente_gestao_id?: string
  gerente_gestao_nome?: string
  gerente_id?: string
  gerente_nome?: string
  participantes?: number
  rank?: number
  pontos?: number
  realizado_mensal?: number
  meta_mensal?: number
  unidade?: string
  label?: string
  displayLabel?: string
  count?: number
  position?: number
}

export type RankingFilters = {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  dataInicio?: string
  dataFim?: string
}

export type Variavel = {
  id?: string
  registro_id?: string
  funcional: string
  variavel_meta: number
  variavel_real: number
  dt_atualizacao?: string
  nome_funcional?: string
  segmento?: string
  segmento_id?: string
  diretoria_nome?: string
  diretoria_id?: string
  regional_nome?: string
  gerencia_id?: string
  agencia_nome?: string
  agencia_id?: string
  data?: string
  competencia?: string
}

export type ResumoPayload = {
  cards: Produto[]
  classifiedCards: ProdutoMensal[]
  variableCard: Variavel[]
  businessSnapshot: BusinessSnapshot
}

export type VariavelFilters = {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  dataInicio?: string
  dataFim?: string
}

export type ButtonProps = {
  variant?: 'primary' | 'secondary' | 'info' | 'link'
  icon?: string
  label?: string
  disabled?: boolean
}

export type ViewType = 'cards' | 'table' | 'ranking' | 'exec' | 'simuladores' | 'campanhas'

export type TabConfig = {
  id: ViewType
  label: string
  icon: string
  ariaLabel: string
  path?: string
}
