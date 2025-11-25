// BEGIN api/detalhes.js
/* =========================================================
   POBJ • API Detalhes  —  Carregamento e processamento de dados de detalhes de contratos
   Endpoint: /api/detalhes
   ========================================================= */

/* ===== Variáveis globais relacionadas a detalhes ===== */
var FACT_DETALHES = [];
var DETAIL_BY_REGISTRO = new Map();
var DETAIL_CONTRACT_IDS = new Set();

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_DETALHES = FACT_DETALHES;
  window.DETAIL_BY_REGISTRO = DETAIL_BY_REGISTRO;
  window.DETAIL_CONTRACT_IDS = DETAIL_CONTRACT_IDS;
}

/* ===== Função para normalizar linhas de detalhes ===== */
function normalizarLinhasFatoDetalhes(rows){
  if (!Array.isArray(rows)) return [];

  return rows.map(raw => {
    const contratoId = limparTexto(raw.contrato_id || raw.contratoId || raw.contrato || "");
    const registroId = limparTexto(raw.registro_id || raw.registroId || raw.registro || "");
    if (!contratoId || !registroId) return null;

    const segmentoId = limparTexto(raw.segmento_id || raw.segmentoId || "");
    const segmento = limparTexto(raw.segmento || "") || segmentoId;
    const diretoriaId = limparTexto(raw.diretoria_id || raw.diretoriaId || "");
    const diretoriaNome = limparTexto(raw.diretoria_nome || raw.diretoriaNome || raw.diretoria_regional || "") || diretoriaId;
    const gerenciaId = limparTexto(raw.gerencia_regional_id || raw.gerenciaRegionalId || raw.gerencia_id || raw.gerenciaId || raw.regional_id || "");
    const gerenciaNome = limparTexto(raw.gerencia_regional_nome || raw.gerenciaRegionalNome || raw.regional_nome || raw.regionalNome || "") || gerenciaId;
    const agenciaId = limparTexto(raw.agencia_id || raw.agenciaId || "");
    const agenciaNome = limparTexto(raw.agencia_nome || raw.agenciaNome || "") || agenciaId;
    const agenciaCodigo = limparTexto(raw.agencia_codigo || raw.agenciaCodigo || "")
      || agenciaId
      || agenciaNome;
    // Suporta tanto estrutura plana quanto objeto aninhado {id, nome}
    let gerenteGestaoId = "";
    let gerenteGestaoNome = "";
    const gerenteGestaoObj = raw.gerente_gestao || raw.gerenteGestao;
    if (gerenteGestaoObj && typeof gerenteGestaoObj === "object" && !Array.isArray(gerenteGestaoObj)) {
      gerenteGestaoId = limparTexto(gerenteGestaoObj.id || "");
      gerenteGestaoNome = limparTexto(gerenteGestaoObj.nome || "");
    } else {
      gerenteGestaoId = limparTexto(raw.gerente_gestao_id || raw.gerenteGestaoId || "");
      gerenteGestaoNome = limparTexto(raw.gerente_gestao_nome || raw.gerenteGestaoNome || "");
    }
    let gerenteId = limparTexto(raw.gerente_id || raw.gerenteId || "");
    let gerenteNome = limparTexto(raw.gerente_nome || raw.gerenteNome || raw.gerente || "") || gerenteId;

    if (!gerenteGestaoId) {
      if (typeof deriveGerenteGestaoIdFromAgency === "function") {
        const derivedGg = deriveGerenteGestaoIdFromAgency(agenciaId || agenciaCodigo || agenciaNome);
        if (derivedGg) {
          gerenteGestaoId = derivedGg;
        }
      }
    }

    if (!gerenteGestaoNome && gerenteGestaoId) {
      if (typeof getGerenteGestaoEntry === "function") {
        const entry = getGerenteGestaoEntry(gerenteGestaoId);
        if (entry) {
          gerenteGestaoNome = limparTexto(entry.nome) || (typeof extractNameFromLabel === "function" ? extractNameFromLabel(entry.label) : "") || gerenteGestaoId;
        }
      }
    }

    if (gerenteGestaoNome && gerenteGestaoNome.includes(" - ")) {
      gerenteGestaoNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(gerenteGestaoNome) : gerenteGestaoNome;
    }

    if (!gerenteNome && gerenteId) {
      if (typeof getGerenteEntry === "function") {
        const entry = getGerenteEntry(gerenteId);
        if (entry) {
          gerenteNome = limparTexto(entry.nome) || (typeof extractNameFromLabel === "function" ? extractNameFromLabel(entry.label) : "") || gerenteId;
        }
      }
    }

    if (gerenteNome && gerenteNome.includes(" - ")) {
      gerenteNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(gerenteNome) : gerenteNome;
    }

    let familiaId = limparTexto(raw.familia_id || raw.familiaId || raw.familia || "");
    let familiaNome = limparTexto(raw.familia_nome || raw.familiaNome || "") || familiaId;

    let indicadorId = limparTexto(raw.id_indicador || raw.indicador_id || raw.indicadorId || raw.indicador || "");
    let indicadorNome = limparTexto(raw.ds_indicador || raw.indicador_nome || raw.indicadorNome || "") || indicadorId;

    let subId = limparTexto(raw.id_subindicador || raw.subindicador_id || raw.subindicadorId || raw.sub_produto_id || raw.subproduto_id || raw.subprodutoId || raw.subproduto || "");
    let subNome = limparTexto(raw.subindicador_nome || raw.subindicadorNome || raw.subindicador || raw.subproduto || "") || subId;

    const carteira = limparTexto(raw.carteira || "");
    const canalVenda = limparTexto(raw.canal_venda || raw.canalVenda || raw.canal || "");
    const tipoVenda = limparTexto(raw.tipo_venda || raw.tipoVenda || raw.tipo || "");
    const modalidade = limparTexto(raw.modalidade_pagamento || raw.modalidadePagamento || raw.modalidade || "");

    let data = typeof converterDataISO === "function" ? converterDataISO(raw.data || raw.data_movimento || "") : "";
    let competencia = typeof converterDataISO === "function" ? converterDataISO(raw.competencia || "") : "";
    if (!competencia && data) competencia = `${data.slice(0, 7)}-01`;
    if (!data && competencia) data = competencia;

    const dataVencimento = typeof converterDataISO === "function" ? converterDataISO(raw.data_vencimento || raw.dataVencimento || "") : "";
    const dataCancelamento = typeof converterDataISO === "function" ? converterDataISO(raw.data_cancelamento || raw.dataCancelamento || "") : "";
    const motivoCancelamento = limparTexto(raw.motivo_cancelamento || raw.motivoCancelamento || raw.motivo || "");

    const valorMeta = toNumber(raw.meta || raw.meta_mensal || raw.metaMensal || raw.meta_contrato || raw.metaContrato || raw.meta_valor || raw.metaValor || 0);
    const valorReal = toNumber(raw.realizado || raw.real_mensal || raw.realMensal || raw.valor_realizado || raw.valorRealizado || raw.realizado_valor || raw.realizadoValor || 0);
    const quantidade = toNumber(raw.quantidade || raw.qtd || raw.quantidade_contrato || raw.quantidadeContrato || 0);
    const peso = toNumber(raw.peso || raw.pontos_meta || raw.pontosMeta || 0);
    const pontos = toNumber(raw.pontos || raw.pontos_cumpridos || raw.pontosCumpridos || 0);
    const statusId = limparTexto(raw.status_id || raw.statusId || raw.status || "");

    const scenarioHint = typeof getSegmentScenarioFromValue === "function" 
      ? (getSegmentScenarioFromValue(segmento) || getSegmentScenarioFromValue(segmentoId) || "")
      : "";
    const indicadorRes = typeof resolveIndicatorFromDimension === "function"
      ? resolveIndicatorFromDimension([indicadorId, indicadorNome], scenarioHint)
      : null;
    if (indicadorRes) {
      if (indicadorRes.indicadorId) indicadorId = indicadorRes.indicadorId;
      if (indicadorRes.indicadorNome) indicadorNome = indicadorRes.indicadorNome;
      if (indicadorRes.familiaId) familiaId = indicadorRes.familiaId;
      if (indicadorRes.familiaNome) familiaNome = indicadorRes.familiaNome;
    }

    const subRes = typeof resolveSubIndicatorFromDimension === "function"
      ? resolveSubIndicatorFromDimension([subId, subNome], indicadorRes, scenarioHint)
      : null;
    if (subRes) {
      if (subRes.subId) subId = subRes.subId;
      if (subRes.subNome) subNome = subRes.subNome;
      if (subRes.familiaId && !familiaId) familiaId = subRes.familiaId;
      if (subRes.familiaNome && !familiaNome) familiaNome = subRes.familiaNome;
    }

    const detail = {
      id: contratoId,
      registroId,
      segmento,
      segmentoId,
      diretoria: diretoriaId,
      diretoriaId,
      diretoriaNome,
      gerenciaRegional: gerenciaId,
      gerenciaId,
      gerenciaNome,
      regional: gerenciaNome,
      agencia: agenciaId || agenciaCodigo || agenciaNome,
      agenciaId: agenciaId || agenciaCodigo || agenciaNome,
      agenciaNome: agenciaNome || agenciaId || agenciaCodigo,
      agenciaCodigo: agenciaCodigo || agenciaId || agenciaNome,
      gerenteGestao: gerenteGestaoId,
      gerenteGestaoId,
      gerenteGestaoNome: gerenteGestaoNome || gerenteGestaoId,
      gerente: gerenteId,
      gerenteId,
      gerenteNome,
      familiaId,
      familiaNome,
      carteira,
      canalVenda,
      tipoVenda,
      modalidadePagamento: modalidade,
      data,
      competencia,
      realizado: Number.isFinite(valorReal) ? valorReal : 0,
      meta: Number.isFinite(valorMeta) ? valorMeta : 0,
      qtd: Number.isFinite(quantidade) && quantidade > 0 ? quantidade : 1,
      peso: Number.isFinite(peso) ? peso : 0,
      pontos: Number.isFinite(pontos) ? pontos : undefined,
      dataVencimento,
      dataCancelamento,
      motivoCancelamento,
      statusId,
    };

    // Usa resolveMapLabel se disponível, caso contrário usa valores diretos
    if (typeof resolveMapLabel === "function") {
      const segMapRef = typeof segMap !== "undefined" ? segMap : null;
      const dirMapRef = typeof dirMap !== "undefined" ? dirMap : null;
      const regMapRef = typeof regMap !== "undefined" ? regMap : null;
      const agMapRef = typeof agMap !== "undefined" ? agMap : null;
      
      detail.segmentoLabel = resolveMapLabel(segMapRef, segmentoId, segmento, segmentoId);
      detail.diretoriaLabel = resolveMapLabel(dirMapRef, diretoriaId, diretoriaNome, diretoriaId);
      detail.gerenciaLabel = resolveMapLabel(regMapRef, gerenciaId, gerenciaNome, gerenciaId);
      detail.agenciaLabel = resolveMapLabel(agMapRef, agenciaId, agenciaNome, agenciaId);
    } else {
      detail.segmentoLabel = segmento;
      detail.diretoriaLabel = diretoriaNome;
      detail.gerenciaLabel = gerenciaNome;
      detail.agenciaLabel = agenciaNome;
    }
    
    if (typeof labelGerenteGestao === "function") {
      detail.gerenteGestaoLabel = labelGerenteGestao(gerenteGestaoId, gerenteGestaoNome);
    } else {
      detail.gerenteGestaoLabel = gerenteGestaoNome || gerenteGestaoId;
    }
    
    if (typeof labelGerente === "function") {
      detail.gerenteLabel = labelGerente(gerenteId, gerenteNome);
    } else {
      detail.gerenteLabel = gerenteNome || gerenteId;
    }

    if (detail.segmentoLabel) detail.segmento = detail.segmentoLabel;
    if (detail.diretoriaLabel) detail.diretoriaNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(detail.diretoriaLabel) || detail.diretoriaNome : detail.diretoriaNome;
    if (detail.gerenciaLabel) detail.gerenciaNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(detail.gerenciaLabel) || detail.gerenciaNome : detail.gerenciaNome;
    if (detail.agenciaLabel) detail.agenciaNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(detail.agenciaLabel) || detail.agenciaNome : detail.agenciaNome;
    if (detail.gerenteGestaoLabel) detail.gerenteGestaoNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(detail.gerenteGestaoLabel) || detail.gerenteGestaoNome : detail.gerenteGestaoNome;
    if (detail.gerenteLabel) detail.gerenteNome = typeof extractNameFromLabel === "function" ? extractNameFromLabel(detail.gerenteLabel) || detail.gerenteNome : detail.gerenteNome;

    if (typeof aplicarIndicadorAliases === "function") {
      aplicarIndicadorAliases(detail, indicadorId, indicadorNome);
    }
    if (subId) {
      detail.subproduto = subNome || subId;
      detail.subIndicadorId = subId;
      detail.subIndicadorNome = subNome || subId;
    }
    if (!detail.familiaId) detail.familiaId = familiaId;
    if (!detail.familiaNome) detail.familiaNome = familiaNome || familiaId;
    detail.prodOrSub = detail.subproduto || detail.produtoNome || detail.produtoId;
    detail.ating = detail.meta ? (detail.realizado / detail.meta) : 0;
    if (detail.pontos === undefined) {
      const pontosCalc = Math.max(0, detail.peso || 0) * (detail.ating || 0);
      detail.pontos = Number.isFinite(pontosCalc) ? pontosCalc : 0;
    }

    return detail;
  }).filter(Boolean);
}

/* ===== Função para carregar dados de detalhes da API ===== */
async function loadDetalhesData(filterParams = {}){
  try {
    const response = await apiGet('/detalhes', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de detalhes:', error);
    return [];
  }
}

/* ===== Função para processar dados de detalhes ===== */
function processDetalhesData(detalhesRaw = []) {
  FACT_DETALHES = normalizarLinhasFatoDetalhes(Array.isArray(detalhesRaw) ? detalhesRaw : []);
  
  // Atualiza referências globais
  if (typeof window !== "undefined") {
    window.FACT_DETALHES = FACT_DETALHES;
  }
  
  // Aplica fallback de hierarquia se a função estiver disponível
  if (typeof applyHierarchyFallback === "function") {
    applyHierarchyFallback(FACT_DETALHES);
  }
  
  // Constrói índices de detalhes
  DETAIL_BY_REGISTRO = new Map();
  DETAIL_CONTRACT_IDS = new Set();
  FACT_DETALHES.forEach(row => {
    if (!row) return;
    const registroKey = limparTexto(row.registroId);
    if (registroKey) {
      const bucket = DETAIL_BY_REGISTRO.get(registroKey) || [];
      bucket.push({ ...row });
      DETAIL_BY_REGISTRO.set(registroKey, bucket);
    }
    const contratoKey = limparTexto(row.id);
    if (contratoKey) DETAIL_CONTRACT_IDS.add(contratoKey);
  });
  
  // Atualiza referências globais
  if (typeof window !== "undefined") {
    window.DETAIL_BY_REGISTRO = DETAIL_BY_REGISTRO;
    window.DETAIL_CONTRACT_IDS = DETAIL_CONTRACT_IDS;
  }
  
  return FACT_DETALHES;
}

// END detalhes.js

