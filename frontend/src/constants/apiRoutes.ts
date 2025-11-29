/**
 * Rotas da API
 * Centraliza todas as rotas da API para facilitar manutenção e evitar erros de digitação
 */
export enum ApiRoutes {
  // Estrutura organizacional
  ESTRUTURA = '/api/estrutura',

  // Resumo
  RESUMO = '/api/resumo',

  // Filtros
  FILTROS = '/api/filtros',

  // Status
  STATUS_INDICADORES = '/api/status_indicadores',

  // Produtos
  PRODUTOS = '/api/produtos',

  // Calendário
  CALENDARIO = '/api/calendario',

  // Realizados
  REALIZADOS = '/api/realizados',

  // Metas
  METAS = '/api/metas',

  // Variável
  VARIAVEL = '/api/variavel',

  // Mesu
  MESU = '/api/mesu',

  // Campanhas
  CAMPANHAS = '/api/campanhas',

  // Detalhes
  DETALHES = '/api/detalhes',

  // Histórico
  HISTORICO = '/api/historico',

  // Leads
  LEADS = '/api/leads',

  // Pontos
  PONTOS = '/api/pontos',

  // Ranking
  RANKING = '/api/ranking',

  // Agent
  AGENT = '/api/agent',

  // Health Check
  HEALTH = '/api/health'
}

/**
 * Helper para obter a rota completa da API
 */
export function getApiRoute(route: ApiRoutes): string {
  return route
}

