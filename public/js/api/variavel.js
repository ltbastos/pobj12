// BEGIN api/variavel.js
/* =========================================================
   POBJ • API Variável  —  Carregamento e processamento de dados de variável
   Endpoint: /api/variavel
   ========================================================= */

/* ===== Variável de variável ===== */
var FACT_VARIAVEL = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_VARIAVEL = FACT_VARIAVEL;
}

/* ===== Função para normalizar linhas de variável ===== */
// Aqui eu trato o fato variável (pontos) porque ele vem com os nomes de colunas diferentes das outras bases.
function normalizarLinhasFatoVariavel(rows){
  return rows.map(raw => {
    const registroId = raw.registro_id;
    if (!registroId) return null;
    let produtoId = raw.id_indicador;
    let produtoNome = raw.ds_indicador || produtoId;
    let familiaId = raw.familia_id || "";
    let familiaNome = raw.familia_nome || familiaId;
    const familiaCodigoExtra = raw.familia_codigo;
    const indicadorCodigoExtra = raw.indicador_codigo;
    const subCodigoExtra = raw.subindicador_codigo;
    const familiaCodigo = limparTexto(familiaCodigoExtra);
    if (familiaCodigo) {
      const familiaSlug = FAMILIA_CODE_TO_SLUG.get(familiaCodigo);
      if (familiaSlug && (!familiaId || familiaId === familiaCodigo)) {
        familiaId = familiaSlug;
        if (!familiaNome || familiaNome === familiaCodigo) {
          const famMeta = FAMILIA_BY_ID.get(familiaSlug);
          if (famMeta?.nome) familiaNome = famMeta.nome;
        }
      }
    }
    const indicadorCodigo = limparTexto(indicadorCodigoExtra);
    if (indicadorCodigo) {
      const indicadorSlug = INDICADOR_CODE_TO_SLUG.get(indicadorCodigo);
      if (indicadorSlug) {
        produtoId = indicadorSlug;
      }
    }
    const subCodigo = limparTexto(subCodigoExtra);
    const subSlug = subCodigo ? SUB_CODE_TO_SLUG.get(subCodigo) : "";
    const variavelMeta = toNumber(raw.variavel_meta);
    const variavelReal = toNumber(raw.variavel_real);
    let data = converterDataISO(raw.data);
    let competencia = converterDataISO(raw.competencia);
    if (!data && competencia) {
      data = competencia;
    }
    if (!competencia && data) {
      competencia = `${data.slice(0, 7)}-01`;
    }
    const indicadorRes = resolveIndicatorFromDimension([produtoId, produtoNome, indicadorCodigoExtra], "");
    if (indicadorRes) {
      if (indicadorRes.indicadorId) produtoId = indicadorRes.indicadorId;
      if (indicadorRes.indicadorNome) produtoNome = indicadorRes.indicadorNome;
      if (indicadorRes.familiaId) familiaId = indicadorRes.familiaId;
      if (indicadorRes.familiaNome) familiaNome = indicadorRes.familiaNome;
    }

    let resolvedSubId = subSlug;
    let resolvedSubNome = "";
    const subRes = resolveSubIndicatorFromDimension([subSlug, subCodigo], indicadorRes, "");
    if (subRes) {
      if (subRes.subId) resolvedSubId = subRes.subId;
      if (subRes.subNome) resolvedSubNome = subRes.subNome;
      if (subRes.familiaId && !familiaId) familiaId = subRes.familiaId;
      if (subRes.familiaNome && !familiaNome) familiaNome = subRes.familiaNome;
    }

    const base = {
      registroId,
      familiaId,
      familiaNome,
      data,
      competencia,
      variavelMeta,
      variavelReal,
    };
    if (familiaCodigo) base.familiaCodigo = familiaCodigo;
    if (indicadorCodigo) base.indicadorCodigo = indicadorCodigo;
    if (subCodigo) base.subindicadorCodigo = subCodigo;
    if (resolvedSubId) {
      base.subprodutoId = resolvedSubId;
      base.subId = resolvedSubId;
      base.subindicadorId = resolvedSubId;
    }
    if (resolvedSubNome) {
      base.subproduto = resolvedSubNome;
      base.subProduto = resolvedSubNome;
      base.subindicadorNome = resolvedSubNome;
    }
    aplicarIndicadorAliases(base, produtoId, produtoNome);
    base.familiaId = familiaId;
    base.familiaNome = familiaNome;
    base.prodOrSub = resolvedSubNome || base.produtoNome || base.produtoId;
    return base;
  }).filter(Boolean);
}

/* ===== Função para carregar dados de variável da API ===== */
async function loadVariavelData(){
  try {
    const variavel = await apiGet('/variavel').catch(() => []);
    return Array.isArray(variavel) ? variavel : [];
  } catch (error) {
    console.error('Erro ao carregar dados de variável:', error);
    return [];
  }
}

/* ===== Função para processar dados de variável ===== */
function processVariavelData(variavelRaw = []) {
  FACT_VARIAVEL = normalizarLinhasFatoVariavel(Array.isArray(variavelRaw) ? variavelRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.FACT_VARIAVEL = FACT_VARIAVEL;
  }
  
  return FACT_VARIAVEL;
}

// END variavel.js

