/**
 * Tipos e interfaces compartilhadas do projeto
 * Centraliza todas as definições de tipos para facilitar manutenção
 */

// ============================================================================
// FILTROS
// ============================================================================

/**
 * Opção de filtro genérica
 */
export interface FilterOption {
  id: string
  label: string
  nome?: string
  // Campos de relacionamento hierárquico organizacional
  id_segmento?: string
  id_diretoria?: string
  id_regional?: string
  id_agencia?: string
  id_gestor?: string
  funcional?: string
  id_original?: string // ID numérico original (para comparações hierárquicas)
  // Campos de relacionamento hierárquico de produtos
  id_familia?: string
  id_indicador?: string
}

/**
 * Seleção hierárquica de filtros organizacionais
 */
export interface HierarchySelection {
  segmento: string
  diretoria: string
  gerencia: string
  agencia: string
  ggestao: string
  gerente: string
}

// ============================================================================
// ESTRUTURA ORGANIZACIONAL
// ============================================================================

/**
 * Dados completos da estrutura organizacional retornados pela API
 */
export interface EstruturaData {
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

/**
 * Item de segmento
 */
export interface SegmentoItem {
  id: string | number
  label: string
  nome?: string
}

// ============================================================================
// PERÍODO E CALENDÁRIO
// ============================================================================

/**
 * Período de datas (início e fim)
 */
export interface Period {
  start: string
  end: string
}

export interface BusinessSnapshot {
  total: number
  elapsed: number
  remaining: number
  monthStart: string
  monthEnd: string
  today: string
}

/**
 * Item de calendário retornado pela API
 */
export interface CalendarioItem {
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

// ============================================================================
// API
// ============================================================================

/**
 * Resposta padrão da API
 */
export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  error?: string
  message?: string
}

// ============================================================================
// DETALHES
// ============================================================================

export interface DetalhesItem {
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

export interface DetalhesFilters {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerente?: string
  familia?: string
  indicador?: string
  subindicador?: string
  dataInicio?: string
  dataFim?: string
}

// ============================================================================
// PRODUTOS
// ============================================================================

export interface Produto {
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

export interface ProdutoFilters {
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

export interface ProdutoMensal {
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

// ============================================================================
// RANKING
// ============================================================================

export interface RankingItem {
  data: string
  competencia: string
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
  rank: number
  pontos?: number
  realizado_mensal?: number
  meta_mensal?: number
}

export interface RankingFilters {
  gerenteGestao?: string
}

// ============================================================================
// VARIÁVEIS
// ============================================================================

export interface Variavel {
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

export interface ResumoPayload {
  produtos: Produto[]
  produtosMensais: ProdutoMensal[]
  variavel: Variavel[]
  businessSnapshot: BusinessSnapshot
}

export interface VariavelFilters {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  dataInicio?: string
  dataFim?: string
}

// ============================================================================
// COMPONENTES
// ============================================================================

/**
 * Props do componente Button
 */
export interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'info' | 'link'
  icon?: string
  label?: string
  disabled?: boolean
}

// ============================================================================
// VIEWS E NAVEGAÇÃO
// ============================================================================

/**
 * View disponível no sistema
 */
export type ViewType = 'cards' | 'table' | 'ranking' | 'exec' | 'simuladores' | 'campanhas'

/**
 * Configuração de uma aba de navegação
 */
export interface TabConfig {
  id: ViewType
  label: string
  icon: string
  ariaLabel: string
  path?: string
}

