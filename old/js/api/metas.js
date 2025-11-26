// BEGIN api/metas.js
/* =========================================================
   POBJ • API Metas  —  Carregamento e processamento de dados de metas
   Endpoint: /api/metas
   ========================================================= */

/* ===== Variável de metas ===== */
var FACT_METAS = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_METAS = FACT_METAS;
}

/* ===== Função para normalizar linhas de metas ===== */
// Aqui eu deixo o fato de metas com os mesmos padrões de datas e chaves dos realizados para facilitar os cruzamentos.
function normalizarLinhasFatoMetas(rows){
  return rows.map(raw => {
    const registroId = raw.registro_id;
    if (!registroId) return null;

    const segmento = raw.segmento;
    const segmentoId = raw.segmento_id;
    const diretoriaId = raw.diretoria_id;
    const diretoriaNome = raw.diretoria_nome || diretoriaId;
    const gerenciaId = raw.gerencia_id;
    const gerenciaNome = raw.gerencia_nome || raw.regional_nome || gerenciaId;
    const regionalNome = raw.regional_nome || gerenciaNome;
    const agenciaIdRaw = raw.agencia_id;
    const agenciaCodigoRaw = raw.agencia_codigo;
    const agenciaNome = raw.agencia_nome || agenciaCodigoRaw || agenciaIdRaw;
    const agenciaCodigo = agenciaCodigoRaw || agenciaIdRaw || agenciaNome;
    const agenciaId = agenciaIdRaw || agenciaCodigoRaw || agenciaNome;
    const gerenteGestaoIdRaw = raw.gerente_gestao_id;
    const gerenteGestaoNomeRaw = raw.gerente_gestao_nome;
    const gerenteGestaoParsed = normalizeFuncionalPair(gerenteGestaoIdRaw, gerenteGestaoNomeRaw);
    const gerenteGestaoId = gerenteGestaoParsed.id;
    const gerenteGestaoNome = gerenteGestaoParsed.nome || gerenteGestaoParsed.label || gerenteGestaoId;
    const gerenteIdRaw = raw.gerente_id;
    const gerenteNomeRaw = raw.gerente_nome;
    const gerenteParsed = normalizeFuncionalPair(gerenteIdRaw, gerenteNomeRaw);
    const gerenteId = gerenteParsed.id;
    const gerenteNome = gerenteParsed.nome || gerenteParsed.label || gerenteId;

    let familiaId = raw.familia_id || "";
    let familiaNome = raw.familia_nome || familiaId;
    let produtoId = raw.id_indicador;
    let produtoNome = raw.ds_indicador || produtoId;
    let subproduto = raw.subproduto || "";

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
    const carteira = raw.carteira;
    const canalVenda = raw.canal_venda;
    const tipoVenda = raw.tipo_venda;
    const modalidadePagamento = raw.modalidade_pagamento;
    const metaMens = toNumber(raw.meta_mensal);
    const metaAcum = toNumber(raw.meta_acumulada);
    const variavelMeta = toNumber(raw.variavel_meta);
    const peso = toNumber(raw.peso);
    let data = converterDataISO(raw.data);
    let competencia = converterDataISO(raw.competencia);
    if (!data && competencia) {
      data = competencia;
    }
    if (!competencia && data) {
      competencia = `${data.slice(0, 7)}-01`;
    }

    const scenarioHint = getSegmentScenarioFromValue(segmento) || getSegmentScenarioFromValue(segmentoId) || "";
    const indicadorRes = resolveIndicatorFromDimension([produtoId, produtoNome, indicadorCodigoExtra], scenarioHint);
    if (indicadorRes) {
      if (indicadorRes.indicadorId) produtoId = indicadorRes.indicadorId;
      if (indicadorRes.indicadorNome) produtoNome = indicadorRes.indicadorNome;
      if (indicadorRes.familiaId) familiaId = indicadorRes.familiaId;
      if (indicadorRes.familiaNome) familiaNome = indicadorRes.familiaNome;
    }

    let resolvedSubId = subSlug;
    let resolvedSubNome = subproduto;
    const subRes = resolveSubIndicatorFromDimension([subSlug, subCodigo, subproduto], indicadorRes, scenarioHint);
    if (subRes) {
      if (subRes.subId) resolvedSubId = subRes.subId;
      if (subRes.subNome) resolvedSubNome = subRes.subNome;
      if (subRes.familiaId && !familiaId) familiaId = subRes.familiaId;
      if (subRes.familiaNome && !familiaNome) familiaNome = subRes.familiaNome;
    }

    const base = {
      registroId,
      segmento,
      segmentoId,
      diretoria: diretoriaId || diretoriaNome,
      diretoriaId: diretoriaId || diretoriaNome,
      diretoriaNome,
      gerenciaRegional: gerenciaId || gerenciaNome,
      gerenciaId: gerenciaId || gerenciaNome,
      gerenciaNome,
      regional: regionalNome,
      agencia: agenciaId,
      agenciaId,
      agenciaNome,
      agenciaCodigo,
      gerenteGestao: gerenteGestaoId,
      gerenteGestaoId,
      gerenteGestaoNome,
      gerenteGestaoLabel: gerenteGestaoParsed.label,
      gerente: gerenteId,
      gerenteId,
      gerenteNome,
      gerenteLabel: gerenteParsed.label,
      familiaId,
      familiaNome,
      subproduto,
      carteira,
      canalVenda,
      tipoVenda,
      modalidadePagamento,
      data,
      competencia,
      meta: metaMens,
      meta_mens: metaMens,
      meta_acum: metaAcum || metaMens,
      variavelMeta,
      peso,
    };
    if (scenarioHint) base.segmentoScenario = scenarioHint;
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
    base.prodOrSub = resolvedSubNome || subproduto || base.produtoNome || base.produtoId;
    if (!base.gerenteGestaoId && base.agenciaId) {
      const derivedGg = deriveGerenteGestaoIdFromAgency(base.agenciaId || base.agencia);
      if (derivedGg) {
        base.gerenteGestao = derivedGg;
        base.gerenteGestaoId = derivedGg;
        if (!base.gerenteGestaoLabel) {
          base.gerenteGestaoLabel = labelGerenteGestao(derivedGg);
        }
        if (!base.gerenteGestaoNome || base.gerenteGestaoNome === base.gerenteGestao) {
          const ggEntry = getGerenteGestaoEntry(derivedGg);
          if (ggEntry) {
            base.gerenteGestaoNome = ggEntry.nome || extractNameFromLabel(ggEntry.label) || base.gerenteGestaoNome;
          }
        }
      }
    }
    return base;
  }).filter(Boolean);
}

/* ===== Função para carregar dados de metas da API ===== */
async function loadMetasData(filterParams = {}){
  try {
    const response = await apiGet('/metas', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de metas:', error);
    return [];
  }
}

/* ===== Função para processar dados de metas ===== */
function processMetasData(metasRaw = []) {
  FACT_METAS = normalizarLinhasFatoMetas(Array.isArray(metasRaw) ? metasRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.FACT_METAS = FACT_METAS;
  }
  
  return FACT_METAS;
}

// END metas.js

