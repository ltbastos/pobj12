export enum ApiRoutes {
  INIT = '/api/pobj/init',
  RESUMO = '/api/pobj/resumo',
  PRODUTOS = '/api/pobj/produtos',
  CALENDARIO = '/api/pobj/calendario',
  REALIZADOS = '/api/pobj/realizados',
  METAS = '/api/pobj/metas',
  VARIAVEL = '/api/pobj/variavel',
  MESU = '/api/pobj/mesu',
  CAMPANHAS = '/api/pobj/campanhas',
  DETALHES = '/api/pobj/detalhes',
  HISTORICO = '/api/pobj/historico',
  LEADS = '/api/pobj/leads',
  PONTOS = '/api/pobj/pontos',
  RANKING = '/api/pobj/ranking',
  EXEC = '/api/pobj/exec',
  SIMULADOR = '/api/pobj/simulador',
  AGENT = '/api/agent',
  HEALTH = '/api/health'
}

export function getApiRoute(route: ApiRoutes): string {
  return route
}
