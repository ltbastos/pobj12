/**
 * Rotas da API
 * Centraliza todas as rotas da API para facilitar manutenção e evitar erros de digitação
 */
export enum ApiRoutes {
  // Inicialização
  INIT = '/api/pobj/init',

  // Resumo
  RESUMO = '/api/pobj/resumo',

  // Produtos
  PRODUTOS = '/api/pobj/produtos',

  // Calendário
  CALENDARIO = '/api/pobj/calendario',

  // Realizados
  REALIZADOS = '/api/pobj/realizados',

  // Metas
  METAS = '/api/pobj/metas',

  // Variável
  VARIAVEL = '/api/pobj/variavel',

  // Mesu
  MESU = '/api/pobj/mesu',

  // Campanhas
  CAMPANHAS = '/api/pobj/campanhas',

  // Detalhes
  DETALHES = '/api/pobj/detalhes',

  // Histórico
  HISTORICO = '/api/pobj/historico',

  // Leads
  LEADS = '/api/pobj/leads',

  // Pontos
  PONTOS = '/api/pobj/pontos',

  // Ranking
  RANKING = '/api/pobj/ranking',

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

