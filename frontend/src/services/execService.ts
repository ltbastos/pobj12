import { apiGet } from './api'
import { ApiRoutes } from '../constants/apiRoutes'

export interface ExecKPIs {
  real_mens: number
  meta_mens: number
  real_acum: number
  meta_acum: number
}

export interface ExecRankingItem {
  key: string
  label: string
  real_mens: number
  meta_mens: number
  p_mens: number
}

export interface ExecStatus {
  hit: Array<{ key: string; label: string; p_mens: number }>
  quase: Array<{ key: string; label: string; p_mens: number }>
  longe: Array<{ key: string; label: string; gap: number }>
}

export interface ExecChartSeries {
  id: string
  label: string
  values: (number | null)[]
  color: string
}

export interface ExecChartData {
  keys: string[]
  labels: string[]
  series: ExecChartSeries[]
}

export interface ExecHeatmapUnit {
  value: string
  label: string
}

export interface ExecHeatmapSection {
  id: string
  label: string
}

export interface ExecHeatmap {
  units: ExecHeatmapUnit[]
  sections: ExecHeatmapSection[]
  data: Record<string, { real: number; meta: number }>
}

export interface ExecData {
  kpis: ExecKPIs
  ranking: ExecRankingItem[]
  status: ExecStatus
  chart: ExecChartData
  heatmap: ExecHeatmap
}

export interface ExecFilters {
  segmento?: string
  diretoria?: string
  regional?: string
  agencia?: string
  gerenteGestao?: string
  gerente?: string
  dataInicio?: string
  dataFim?: string
}

export async function getExecData(filters?: ExecFilters): Promise<ExecData | null> {
  const params: Record<string, string> = {}
  
  if (filters) {
    if (filters.segmento) params.segmento = filters.segmento
    if (filters.diretoria) params.diretoria = filters.diretoria
    if (filters.regional) params.regional = filters.regional
    if (filters.agencia) params.agencia = filters.agencia
    if (filters.gerenteGestao) params.gerenteGestao = filters.gerenteGestao
    if (filters.gerente) params.gerente = filters.gerente
    if (filters.dataInicio) params.dataInicio = filters.dataInicio
    if (filters.dataFim) params.dataFim = filters.dataFim
  }
  
  const response = await apiGet<ExecData>(ApiRoutes.EXEC, params)

  if (response.success && response.data) {
    return response.data
  }

  console.error('Erro ao buscar dados executivos:', response.error)
  return null
}

