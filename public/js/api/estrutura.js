// BEGIN api/estrutura.js
/* =========================================================
   POBJ • API Estrutura  —  Carregamento e processamento de dados de estrutura organizacional
   Endpoint: /api/estrutura
   ========================================================= */

/* ===== Variáveis de lookup para dimensões de estrutura ===== */
// Variáveis globais para acesso em outros módulos
var DIM_SEGMENTOS_LOOKUP = new Map();
var DIM_DIRETORIAS_LOOKUP = new Map();
var DIM_REGIONAIS_LOOKUP = new Map();
var DIM_AGENCIAS_LOOKUP = new Map();
var DIM_GGESTAO_LOOKUP = new Map();
var DIM_GERENTES_LOOKUP = new Map();

// Opções de filtro de dimensões (usado em estrutura e outros módulos)
// Usa var para ser acessível globalmente em outros scripts
var DIMENSION_FILTER_OPTIONS = {
  segmento: [],
  diretoria: [],
  gerencia: [],
  agencia: [],
  gerenteGestao: [],
  gerente: [],
};

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.DIM_SEGMENTOS_LOOKUP = DIM_SEGMENTOS_LOOKUP;
  window.DIM_DIRETORIAS_LOOKUP = DIM_DIRETORIAS_LOOKUP;
  window.DIM_REGIONAIS_LOOKUP = DIM_REGIONAIS_LOOKUP;
  window.DIM_AGENCIAS_LOOKUP = DIM_AGENCIAS_LOOKUP;
  window.DIM_GGESTAO_LOOKUP = DIM_GGESTAO_LOOKUP;
  window.DIM_GERENTES_LOOKUP = DIM_GERENTES_LOOKUP;
  window.DIMENSION_FILTER_OPTIONS = DIMENSION_FILTER_OPTIONS;
}

/* ===== Função para construir lookup de dimensões ===== */
function buildDimensionLookup(rows){
  const map = new Map();
  const list = Array.isArray(rows) ? rows : [];
  list.forEach(raw => {
    if (!raw || typeof raw !== "object") return;
    const candidates = [
      raw.id,
      raw.codigo,
      raw.id_segmento,
      raw.segmento_id,
      raw.id_diretoria,
      raw.diretoria_id,
      raw.id_regional,
      raw.regional_id,
      raw.gerencia_regional_id,
      raw.id_agencia,
      raw.agencia_id,
    ];
    const key = limparTexto(candidates.find(value => limparTexto(value)));
    if (!key) return;
    // Padronizado: backend retorna 'label', com fallback apenas para compatibilidade temporária
    const label = limparTexto(raw.label || raw.nome) || key;
    map.set(key, {
      ...raw,
      id: key,
      nome: label, // Mantém compatibilidade com código que usa 'nome'
      label: label,
    });
  });
  return map;
}

/* ===== Função para registrar lookups de dimensões ===== */
function registerDimensionLookups({
  segmentos = [],
  diretorias = [],
  regionais = [],
  agencias = [],
  gerentesGestao = [],
  gerentes = [],
} = {}){
  DIM_SEGMENTOS_LOOKUP = buildDimensionLookup(segmentos);
  DIM_DIRETORIAS_LOOKUP = buildDimensionLookup(diretorias);
  DIM_REGIONAIS_LOOKUP = buildDimensionLookup(regionais);
  DIM_AGENCIAS_LOOKUP = buildDimensionLookup(agencias);
  DIM_GGESTAO_LOOKUP = buildDimensionLookup(gerentesGestao);
  DIM_GERENTES_LOOKUP = buildDimensionLookup(gerentes);
}

/* ===== Funções auxiliares para buscar entradas de dimensões ===== */
function getGerenteGestaoEntry(id){
  const key = limparTexto(id);
  if (!key) return null;
  if (DIM_GGESTAO_LOOKUP?.has?.(key)) return DIM_GGESTAO_LOOKUP.get(key);
  if (ggMap instanceof Map && ggMap.has(key)) return ggMap.get(key);
  return null;
}

function getGerenteEntry(id){
  const key = limparTexto(id);
  if (!key) return null;
  if (DIM_GERENTES_LOOKUP?.has?.(key)) return DIM_GERENTES_LOOKUP.get(key);
  if (gerMap instanceof Map && gerMap.has(key)) return gerMap.get(key);
  return null;
}

function labelFromEntry(entry, fallbackId = "", fallbackName = ""){
  if (!entry) {
    const cleanId = limparTexto(fallbackId);
    const cleanName = limparTexto(fallbackName);
    if (cleanName && cleanName.includes(" - ")) return cleanName;
    if (cleanId && cleanName && cleanId !== cleanName) return `${cleanId} - ${cleanName}`;
    return cleanName || cleanId || "";
  }
  const entryId = limparTexto(entry.id) || limparTexto(fallbackId);
  const explicitLabel = limparTexto(entry.label);
  if (explicitLabel) return explicitLabel;
  const entryName = limparTexto(entry.nome) || limparTexto(fallbackName);
  if (entryName && entryName.includes(" - ")) return entryName;
  if (entryId && entryName && entryId !== entryName) return `${entryId} - ${entryName}`;
  return entryName || entryId || limparTexto(fallbackName) || "";
}

function labelGerenteGestao(id, fallbackName = ""){
  return labelFromEntry(getGerenteGestaoEntry(id), id, fallbackName);
}

function labelGerente(id, fallbackName = ""){
  return labelFromEntry(getGerenteEntry(id), id, fallbackName);
}

function deriveGerenteGestaoIdFromAgency(agencia){
  const key = limparTexto(agencia);
  if (!key) return "";
  const candidates = [];

  const dim = DIM_AGENCIAS_LOOKUP?.get?.(key);
  if (dim) {
    candidates.push(dim.gerente_gestao_id, dim.gerenteGestaoId, dim.gerente_gestao, dim.gerenteGestao);
  }

  if (GGESTAO_BY_AGENCIA instanceof Map && GGESTAO_BY_AGENCIA.has(key)) {
    candidates.push(...Array.from(GGESTAO_BY_AGENCIA.get(key) || []));
  }

  if (MESU_BY_AGENCIA instanceof Map && MESU_BY_AGENCIA.has(key)) {
    const meta = MESU_BY_AGENCIA.get(key);
    if (meta) candidates.push(meta.gerenteGestaoId, meta.gerenteGestao);
  }

  const simple = simplificarTexto(key);
  if (simple && MESU_AGENCIA_LOOKUP instanceof Map && MESU_AGENCIA_LOOKUP.has(simple)) {
    const meta = MESU_AGENCIA_LOOKUP.get(simple);
    if (meta) candidates.push(meta.gerenteGestaoId, meta.gerenteGestao);
  }

  if (!candidates.length) {
    const meta = resolveAgencyHierarchyMeta({ agenciaId: key, agencia: key, agenciaCodigo: key });
    if (meta) candidates.push(meta.gerenteGestaoId, meta.gerenteGestao);
  }

  for (const candidate of candidates) {
    const clean = limparTexto(candidate);
    if (clean) return clean;
  }
  return "";
}

/* ===== Função para carregar dados de estrutura da API ===== */
async function loadEstruturaData(){
  try {
    const dimensions = await apiGet('/estrutura', { _t: Date.now() }).catch(() => ({}));
    return {
      segmentos: dimensions.segmentos || [],
      diretorias: dimensions.diretorias || [],
      regionais: dimensions.regionais || [],
      agencias: dimensions.agencias || [],
      gerentesGestao: dimensions.gerentes_gestao || [],
      gerentes: dimensions.gerentes || [],
    };
  } catch (error) {
    console.error('Erro ao carregar dados de estrutura:', error);
    return {
      segmentos: [],
      diretorias: [],
      regionais: [],
      agencias: [],
      gerentesGestao: [],
      gerentes: [],
    };
  }
}

/* ===== Função para processar dados de estrutura ===== */
function processEstruturaData({
  dimSegmentosRaw = [],
  dimDiretoriasRaw = [],
  dimRegionaisRaw = [],
  dimAgenciasRaw = [],
  dimGerentesGestaoRaw = [],
  dimGerentesRaw = [],
} = {}) {
  const segmentosDim = Array.isArray(dimSegmentosRaw) ? dimSegmentosRaw : [];
  const diretoriasDim = Array.isArray(dimDiretoriasRaw) ? dimDiretoriasRaw : [];
  const regionaisDim = Array.isArray(dimRegionaisRaw) ? dimRegionaisRaw : [];
  const agenciasDim = Array.isArray(dimAgenciasRaw) ? dimAgenciasRaw : [];
  const gerentesGestaoDim = Array.isArray(dimGerentesGestaoRaw) ? dimGerentesGestaoRaw : [];
  const gerentesDim = Array.isArray(dimGerentesRaw) ? dimGerentesRaw : [];

  registerDimensionLookups({
    segmentos: segmentosDim,
    diretorias: diretoriasDim,
    regionais: regionaisDim,
    agencias: agenciasDim,
    gerentesGestao: gerentesGestaoDim,
    gerentes: gerentesDim,
  });

  const segmentosOptions = uniqById(segmentosDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));
  const diretoriasOptions = uniqById(diretoriasDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));
  const regionaisOptions = uniqById(regionaisDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));
  const agenciasOptions = uniqById(agenciasDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));
  const gerentesGestaoOptions = uniqById(gerentesGestaoDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));
  const gerentesOptions = uniqById(gerentesDim.map(row => normOpt({
    id: row?.id ?? '',
    label: row?.label || row?.nome || '',
  })));

  DIMENSION_FILTER_OPTIONS.segmento = segmentosOptions;
  DIMENSION_FILTER_OPTIONS.diretoria = diretoriasOptions;
  DIMENSION_FILTER_OPTIONS.gerencia = regionaisOptions;
  DIMENSION_FILTER_OPTIONS.agencia = agenciasOptions;
  DIMENSION_FILTER_OPTIONS.gerenteGestao = gerentesGestaoOptions;
  DIMENSION_FILTER_OPTIONS.gerente = gerentesOptions;

  return {
    dimSegmentos: segmentosDim,
    dimDiretorias: diretoriasDim,
    dimRegionais: regionaisDim,
    dimAgencias: agenciasDim,
    dimGerentesGestao: gerentesGestaoDim,
    dimGerentes: gerentesDim,
  };
}

// END estrutura.js

