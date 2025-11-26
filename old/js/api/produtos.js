// BEGIN api/produtos.js
/* =========================================================
   POBJ • API Produtos  —  Carregamento e processamento de dados de produtos/indicadores
   Endpoint: /api/produtos
   ========================================================= */

/* ===== Constantes e variáveis de produtos ===== */
const SEGMENT_SCENARIO_DEFAULT = "varejo";
var SEGMENT_DIMENSION_MAP = new Map();
var CURRENT_SEGMENT_SCENARIO = SEGMENT_SCENARIO_DEFAULT;
var DIM_PRODUTOS = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.SEGMENT_DIMENSION_MAP = SEGMENT_DIMENSION_MAP;
  window.CURRENT_SEGMENT_SCENARIO = CURRENT_SEGMENT_SCENARIO;
  window.DIM_PRODUTOS = DIM_PRODUTOS;
  window.SEGMENT_SCENARIO_DEFAULT = SEGMENT_SCENARIO_DEFAULT;
}

/* ===== Função auxiliar para gerar ID ===== */
function gerarId(codigo, nome, nomePadrao = "Indicador") {
  const codigoLimpo = limparTexto(String(codigo || ""));
  const nomeLimpo = limparTexto(nome || "") || nomePadrao;
  
  if (codigoLimpo && /[^0-9]/.test(codigoLimpo)) {
    return codigoLimpo;
  }
  const slug = simplificarTexto(nomeLimpo);
  return slug ? slug.replace(/\s+/g, "_") : codigoLimpo || "";
}

/* ===== Função para normalizar linhas de produtos ===== */
function normalizarDimProdutos(rows){
  const list = Array.isArray(rows) ? rows : [];
  return list.map(raw => {
    const familiaCodigo = limparTexto(String(raw.id_familia || ""));
    const familiaNome = limparTexto(raw.familia || "") || "Indicador";
    const indicadorCodigo = limparTexto(String(raw.id_indicador || ""));
    const indicadorNome = limparTexto(raw.indicador || "") || "Indicador";
    const subCodigoRaw = raw.id_subindicador || "";
    const subNomeRaw = raw.subindicador || "";
    
    const subCodigo = limparTexto(String(subCodigoRaw));
    const subCodigoValido = subCodigo && subCodigo !== "-" && subCodigo !== "0" ? subCodigo : "";
    const subNomeLimpo = limparTexto(subNomeRaw);
    const hasSub = Boolean(subCodigoValido) || Boolean(subNomeLimpo);
    
    const base = {
      familiaCodigo,
      familiaId: gerarId(familiaCodigo, familiaNome),
      familiaNome,
      indicadorCodigo,
      indicadorId: gerarId(indicadorCodigo, indicadorNome),
      indicadorNome,
      metrica: raw.metrica || "valor",
      peso: toNumber(raw.peso) || 0
    };
    
    if (!hasSub) {
      return { ...base, subCodigo: "", subId: "", subNome: "" };
    }
    
    const subId = gerarId(subCodigoValido, subNomeLimpo);
    return {
      ...base,
      subCodigo: subCodigoValido,
      subId,
      subNome: subNomeLimpo || subId || subCodigoValido || ""
    };
  }).filter(row => row.indicadorId);
}

/* ===== Função para carregar dados de produtos da API ===== */
async function loadProdutosData(){
  try {
    const response = await apiGet('/produtos').catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de produtos:', error);
    return [];
  }
}

/* ===== Função para processar dados de produtos ===== */
function processProdutosData(produtosDimRaw = []) {
  const baseDimProdutos = normalizarDimProdutos(produtosDimRaw);
  const dimProdutosVarejo = baseDimProdutos.map(row => ({ ...row, scenario: SEGMENT_SCENARIO_DEFAULT }));
  const dimProdutosEmpresas = dimProdutosVarejo.map(row => ({ ...row, scenario: "empresas" }));

  SEGMENT_DIMENSION_MAP.clear();
  SEGMENT_DIMENSION_MAP.set(SEGMENT_SCENARIO_DEFAULT, dimProdutosVarejo);
  SEGMENT_DIMENSION_MAP.set("empresas", dimProdutosEmpresas);

  const dimensionAliases = [
    { key: SEGMENT_SCENARIO_DEFAULT, value: dimProdutosVarejo },
    { key: "default", value: dimProdutosVarejo },
    { key: "todos", value: dimProdutosVarejo },
    { key: "empresas", value: dimProdutosEmpresas }
  ];

  const globalMaps = typeof window !== "undefined" 
    ? [window.segMap, window.dirMap, window.regMap, window.ageMap, window.agMap]
    : [segMap, dirMap, regMap, ageMap, agMap];

  globalMaps.forEach(map => {
    if (map instanceof Map) {
    map.clear();
      dimensionAliases.forEach(({ key, value }) => map.set(key, value));
    }
  });
  
  rebuildGlobalDimensionAliasIndex();
  applySegmentDimension(CURRENT_SEGMENT_SCENARIO);
  
  return {
    dimProdutos: DIM_PRODUTOS,
    dimProdutosPorSegmento: Object.fromEntries(SEGMENT_DIMENSION_MAP)
  };
}

// END produtos.js

