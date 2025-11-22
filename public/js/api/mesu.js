// BEGIN api/mesu.js
/* =========================================================
   POBJ • API MESU  —  Carregamento e processamento de dados MESU (hierarquia organizacional)
   Endpoint: /api/mesu
   ========================================================= */

/* ===== Variáveis globais relacionadas a MESU ===== */
var MESU_DATA = [];
var MESU_BY_AGENCIA = new Map();
var MESU_AGENCIA_LOOKUP = new Map();
var MESU_FALLBACK_ROWS = [];
var GERENCIAS_BY_DIRETORIA = new Map();
var AGENCIAS_BY_GERENCIA = new Map();
var GGESTAO_BY_AGENCIA = new Map();
var GERENTES_BY_AGENCIA = new Map();

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.MESU_DATA = MESU_DATA;
  window.MESU_BY_AGENCIA = MESU_BY_AGENCIA;
  window.MESU_AGENCIA_LOOKUP = MESU_AGENCIA_LOOKUP;
  window.MESU_FALLBACK_ROWS = MESU_FALLBACK_ROWS;
  window.GERENCIAS_BY_DIRETORIA = GERENCIAS_BY_DIRETORIA;
  window.AGENCIAS_BY_GERENCIA = AGENCIAS_BY_GERENCIA;
  window.GGESTAO_BY_AGENCIA = GGESTAO_BY_AGENCIA;
  window.GERENTES_BY_AGENCIA = GERENTES_BY_AGENCIA;
}

/* ===== Função para normalizar linhas MESU ===== */
function normalizarLinhasMesu(rows){
  return rows.map(raw => {
    const segmentoNome = lerCelula(raw, ["Segmento", "segmento"]);
    const segmentoId = lerCelula(raw, ["Id Segmento", "ID Segmento", "id segmento", "Id segmento", "segmento_id", "segmentoId", "segmentoID"]) || segmentoNome;
    const segmentoLabelRaw = lerCelula(raw, ["Segmento Label", "segmento_label", "segmentoLabel"]);
    const diretoriaNome = lerCelula(raw, ["Diretoria", "Diretoria Regional", "diretoria", "Diretoria regional", "diretoria_regional", "Diretoria_regional"]);
    const diretoriaId = lerCelula(raw, ["Id Diretoria", "ID Diretoria", "Diretoria ID", "Id Diretoria Regional", "id diretoria", "diretoria_id", "diretoriaId"]) || diretoriaNome;
    const diretoriaLabelRaw = lerCelula(raw, ["Diretoria Label", "diretoria_label", "diretoriaLabel", "Diretoria Rotulo", "diretoria_rotulo"]);
    const regionalNomeOriginal = lerCelula(raw, ["Regional", "Gerencia Regional", "Gerência Regional", "Gerencia regional", "Regional Nome", "gerencia_regional", "Gerencia_regional"]);
    const regionalId = lerCelula(raw, ["Id Regional", "ID Regional", "Id Gerencia Regional", "Id Gerência Regional", "Gerencia ID", "gerencia_regional_id", "regional_id", "regionalId"]) || regionalNomeOriginal;
    const regionalLabelRaw = lerCelula(raw, ["Gerencia Regional Label", "Gerência Regional Label", "regional_label", "gerencia_regional_label", "gerenciaRegionalLabel"]);
    const regionalNome = (regionalLabelRaw || buildHierarchyLabel(regionalId, regionalNomeOriginal));
    const regionalNomeAliases = [];
    if (regionalNomeOriginal && regionalNomeOriginal !== regionalNome) regionalNomeAliases.push(regionalNomeOriginal);
    const agenciaNomeOriginal = lerCelula(raw, ["Agencia", "Agência", "Agencia Nome", "Agência Nome", "agencia", "agencia_nome"]);
    const agenciaId = lerCelula(raw, ["Id Agencia", "ID Agencia", "Id Agência", "Agencia ID", "Agência ID", "agencia_id", "agenciaId"]) || agenciaNomeOriginal;
    const agenciaCodigo = lerCelula(raw, ["Agencia Codigo", "Agência Codigo", "Codigo Agencia", "Código Agência", "agencia_codigo", "codigo_agencia"]) || agenciaId || agenciaNomeOriginal;
    const agenciaLabelRaw = lerCelula(raw, ["Agencia Label", "Agência Label", "agencia_label", "agenciaLabel"]);
    const agenciaNome = (agenciaLabelRaw || buildHierarchyLabel(agenciaId, agenciaNomeOriginal));
    const agenciaNomeAliases = [];
    if (agenciaNomeOriginal && agenciaNomeOriginal !== agenciaNome) agenciaNomeAliases.push(agenciaNomeOriginal);
    const gerenteGestaoNome = lerCelula(raw, [
      "Gerente Gestao Nome",
      "Gerente de Gestao Nome",
      "Gerente de Gestão Nome",
      "Gerente de Gestao",
      "Gerente de Gestão",
      "Gerente Gestao",
      "gerente_gestao_nome",
      "gerente_gestao"
    ]);
    const gerenteGestaoId = lerCelula(raw, ["Id Gerente de Gestao", "ID Gerente de Gestao", "Id Gerente de Gestão", "Gerente de Gestao Id", "gerenteGestaoId", "gerente_gestao_id"]) || gerenteGestaoNome;
    const gerenteGestaoLabelRaw = lerCelula(raw, ["Gerente Gestao Label", "gerente_gestao_label", "gerenteGestaoLabel"]);
    const gerenteNome = lerCelula(raw, ["Gerente", "Gerente Nome", "Nome Gerente", "Gerente Geral", "Gerente geral", "gerente"]);
    const gerenteId = lerCelula(raw, ["Id Gerente", "ID Gerente", "Gerente Id", "gerente_id", "gerenteId"]) || gerenteNome;
    const gerenteLabelRaw = lerCelula(raw, ["Gerente Label", "gerente_label", "gerenteLabel"]);

    return {
      segmentoNome,
      segmentoId,
      segmentoLabel: segmentoLabelRaw || buildHierarchyLabel(segmentoId, segmentoNome),
      segmentoNomeOriginal: segmentoNome,
      diretoriaNome,
      diretoriaId,
      diretoriaLabel: diretoriaLabelRaw || buildHierarchyLabel(diretoriaId, diretoriaNome),
      diretoriaNomeOriginal: diretoriaNome,
      regionalNome,
      regionalId,
      regionalLabel: regionalLabelRaw || regionalNome,
      regionalNomeOriginal,
      regionalNomeAliases,
      agenciaNome,
      agenciaId,
      agenciaCodigo,
      agenciaLabel: agenciaLabelRaw || agenciaNome,
      agenciaNomeOriginal,
      agenciaNomeAliases,
      gerenteGestaoNome,
      gerenteGestaoId,
      gerenteGestaoLabel: gerenteGestaoLabelRaw || buildHierarchyLabel(gerenteGestaoId, gerenteGestaoNome),
      gerenteNome,
      gerenteId,
      gerenteLabel: gerenteLabelRaw || buildHierarchyLabel(gerenteId, gerenteNome)
    };
  }).filter(row => row.diretoriaId || row.regionalId || row.agenciaId);
}

/* ===== Função para carregar dados MESU da API ===== */
async function loadMesuData(){
  try {
    const mesu = await apiGet('/mesu').catch(() => []);
    return Array.isArray(mesu) ? mesu : [];
  } catch (error) {
    console.error('Erro ao carregar dados MESU:', error);
    return [];
  }
}

/* ===== Função para processar dados MESU ===== */
function processMesuData(mesuRaw = []) {
  const mesuRows = normalizarLinhasMesu(Array.isArray(mesuRaw) ? mesuRaw : []);
  // montarHierarquiaMesu será chamada em app.js após processMesuData
  return mesuRows;
}

// A função montarHierarquiaMesu será movida para cá, mas como ela depende de muitas variáveis globais
// e funções auxiliares que estão em app.js, ela será mantida em app.js por enquanto
// ou movida para cá se todas as dependências estiverem disponíveis

// END mesu.js

