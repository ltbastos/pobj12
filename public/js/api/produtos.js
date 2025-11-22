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

/* ===== Função para normalizar linhas de produtos ===== */
function normalizarDimProdutos(rows){
  const list = Array.isArray(rows) ? rows : [];
  return list.map(raw => {
    const familiaCodigoRaw = raw.id_familia || raw.familia_codigo || "";
    const familiaNomeRaw = raw.familia || raw.familia_nome || "";
    const indicadorCodigoRaw = raw.id_indicador || raw.indicador_codigo || "";
    const indicadorNomeRaw = raw.indicador || raw.ds_indicador || "";
    const subCodigoRaw = raw.id_subindicador || raw.subindicador_codigo || "";
    const subNomeRaw = raw.subindicador || "";
    const pesoRaw = raw.peso || 0;

    const familiaNome = limparTexto(familiaNomeRaw) || "Indicador";
    const familiaCodigo = limparTexto(String(familiaCodigoRaw));
    let familiaId = "";
    if (familiaCodigo && /[^0-9]/.test(familiaCodigo)) {
      familiaId = familiaCodigo;
    } else {
      const slug = simplificarTexto(familiaNome);
      familiaId = slug ? slug.replace(/\s+/g, "_") : String(familiaCodigo || "");
    }

    const indicadorNome = limparTexto(indicadorNomeRaw) || "Indicador";
    const indicadorCodigo = limparTexto(String(indicadorCodigoRaw));
    let indicadorId = "";
    if (indicadorCodigo && /[^0-9]/.test(indicadorCodigo)) {
      indicadorId = indicadorCodigo;
    } else {
      const slug = simplificarTexto(indicadorNome);
      indicadorId = slug ? slug.replace(/\s+/g, "_") : String(indicadorCodigo || "");
    }

    const subNomeLimpo = limparTexto(subNomeRaw);
    const subCodigo = limparTexto(String(subCodigoRaw));
    const subCodigoValido = subCodigo && subCodigo !== "-" && subCodigo !== "0" ? subCodigo : "";
    const hasSub = Boolean(subCodigoValido) || Boolean(subNomeLimpo);
    
    if (!hasSub) {
      return {
        familiaCodigo,
        familiaId,
        familiaNome,
        indicadorCodigo,
        indicadorId,
        indicadorNome,
        peso: toNumber(pesoRaw) || 0,
        subCodigo: "",
        subId: "",
        subNome: ""
      };
    }

    let subId = "";
    if (subCodigoValido && /[^0-9]/.test(subCodigoValido) && !subNomeLimpo) {
      subId = subCodigoValido;
    } else {
      const slug = subNomeLimpo ? simplificarTexto(subNomeLimpo) : simplificarTexto(subCodigoValido);
      subId = slug ? slug.replace(/\s+/g, "_") : subCodigoValido;
    }

    const subNome = subNomeLimpo || subId || subCodigoValido || "";

    return {
      familiaCodigo,
      familiaId,
      familiaNome,
      indicadorCodigo,
      indicadorId,
      indicadorNome,
      peso: toNumber(pesoRaw) || 0,
      subCodigo: subCodigoValido,
      subId,
      subNome
    };
  }).filter(row => row.indicadorId);
}

/* ===== Função para carregar dados de produtos da API ===== */
async function loadProdutosData(){
  try {
    const produtos = await apiGet('/produtos').catch(() => []);
    return Array.isArray(produtos) ? produtos : [];
  } catch (error) {
    console.error('Erro ao carregar dados de produtos:', error);
    return [];
  }
}

/* ===== Função para processar dados de produtos ===== */
function processProdutosData(produtosDimRaw = []) {
  const produtosDimRows = Array.isArray(produtosDimRaw) ? produtosDimRaw : [];
  const baseDimProdutos = normalizarDimProdutos(produtosDimRows);
  const dimProdutosVarejo = baseDimProdutos.map(row => ({ ...row, scenario: SEGMENT_SCENARIO_DEFAULT }));

  const dimProdutosEmpresas = dimProdutosVarejo.map(row => ({ ...row, scenario: "empresas" }));

  SEGMENT_DIMENSION_MAP.clear();
  SEGMENT_DIMENSION_MAP.set(SEGMENT_SCENARIO_DEFAULT, dimProdutosVarejo);
  SEGMENT_DIMENSION_MAP.set("empresas", dimProdutosEmpresas);
  const defaultDim = dimProdutosVarejo.slice();
  const empresasDim = dimProdutosEmpresas.slice();

  const dimensionAliases = [
    { key: SEGMENT_SCENARIO_DEFAULT, value: defaultDim },
    { key: "default", value: defaultDim },
    { key: "todos", value: defaultDim },
    { key: "empresas", value: empresasDim }
  ];

  const globalMaps = [];
  if (typeof window !== "undefined") {
    globalMaps.push(window.segMap, window.dirMap, window.regMap, window.ageMap, window.agMap);
  } else {
    globalMaps.push(segMap, dirMap, regMap, ageMap, agMap);
  }

  globalMaps.forEach(map => {
    if (!(map instanceof Map)) return;
    map.clear();
    dimensionAliases.forEach(({ key, value }) => {
      map.set(key, value);
    });
  });
  
  rebuildGlobalDimensionAliasIndex();
  applySegmentDimension(CURRENT_SEGMENT_SCENARIO);
  
  return {
    dimProdutos: DIM_PRODUTOS,
    dimProdutosPorSegmento: Object.fromEntries(Array.from(SEGMENT_DIMENSION_MAP.entries()).map(([key, rows]) => [key, rows])),
  };
}

// END produtos.js

