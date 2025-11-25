// BEGIN api/campanhas.js
/* =========================================================
   POBJ • API Campanhas  —  Carregamento e processamento de dados de campanhas
   Endpoint: /api/campanhas
   ========================================================= */

/* ===== Variáveis globais relacionadas a campanhas ===== */
var FACT_CAMPANHAS = [];
var CAMPAIGN_UNIT_DATA = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_CAMPANHAS = FACT_CAMPANHAS;
  window.CAMPAIGN_UNIT_DATA = CAMPAIGN_UNIT_DATA;
}

/* ===== Dados padrão de unidades de campanha ===== */
const DEFAULT_CAMPAIGN_UNIT_DATA = [
  { id: "nn-atlas", diretoria: "DR 01", diretoriaNome: "Norte & Nordeste", gerenciaRegional: "GR 01", regional: "Regional Fortaleza", gerenteGestao: "GG 01", agenciaCodigo: "Ag 1001", agencia: "Agência 1001 • Fortaleza Centro", segmento: "Negócios", produtoId: "captacao_bruta", subproduto: "Aplicação", gerente: "Gerente 1", gerenteNome: "Ana Lima", carteira: "Carteira Atlas", linhas: 132.4, cash: 118.2, conquista: 112.6, atividade: true, data: "2025-09-15" },
  { id: "nn-delta", diretoria: "DR 01", diretoriaNome: "Norte & Nordeste", gerenciaRegional: "GR 01", regional: "Regional Fortaleza", gerenteGestao: "GG 01", agenciaCodigo: "Ag 1001", agencia: "Agência 1001 • Fortaleza Centro", segmento: "Negócios", produtoId: "captacao_liquida", subproduto: "Resgate", gerente: "Gerente 1", gerenteNome: "Ana Lima", carteira: "Carteira Delta", linhas: 118.3, cash: 109.5, conquista: 104.1, atividade: true, data: "2025-09-16" },
  { id: "nn-iguatu", diretoria: "DR 01", diretoriaNome: "Norte & Nordeste", gerenciaRegional: "GR 02", regional: "Regional Recife", gerenteGestao: "GG 02", agenciaCodigo: "Ag 1002", agencia: "Agência 1002 • Recife Boa Vista", segmento: "Empresas", produtoId: "prod_credito_pj", subproduto: "À vista", gerente: "Gerente 2", gerenteNome: "Paulo Nunes", carteira: "Carteira Iguatu", linhas: 124.2, cash: 110.3, conquista: 102.1, atividade: true, data: "2025-09-12" },
  { id: "nn-sertao", diretoria: "DR 01", diretoriaNome: "Norte & Nordeste", gerenciaRegional: "GR 02", regional: "Regional Recife", gerenteGestao: "GG 02", agenciaCodigo: "Ag 1002", agencia: "Agência 1002 • Recife Boa Vista", segmento: "Empresas", produtoId: "centralizacao", subproduto: "Parcelado", gerente: "Gerente 2", gerenteNome: "Paulo Nunes", carteira: "Carteira Sertão", linhas: 98.4, cash: 94.6, conquista: 96.8, atividade: false, data: "2025-09-09" },
  { id: "sd-horizonte", diretoria: "DR 02", diretoriaNome: "Sudeste", gerenciaRegional: "GR 03", regional: "Regional São Paulo", gerenteGestao: "GG 03", agenciaCodigo: "Ag 1004", agencia: "Agência 1004 • Avenida Paulista", segmento: "Empresas", produtoId: "rotativo_pj_vol", subproduto: "Aplicação", gerente: "Gerente 3", gerenteNome: "Juliana Prado", carteira: "Carteira Horizonte", linhas: 115.2, cash: 120.5, conquista: 108.4, atividade: true, data: "2025-09-14" },
  { id: "sd-paulista", diretoria: "DR 02", diretoriaNome: "Sudeste", gerenciaRegional: "GR 03", regional: "Regional São Paulo", gerenteGestao: "GG 03", agenciaCodigo: "Ag 1004", agencia: "Agência 1004 • Avenida Paulista", segmento: "Empresas", produtoId: "rotativo_pj_qtd", subproduto: "Resgate", gerente: "Gerente 3", gerenteNome: "Juliana Prado", carteira: "Carteira Paulista", linhas: 104.8, cash: 99.1, conquista: 101.3, atividade: true, data: "2025-09-06" },
  { id: "sd-bandeirantes", diretoria: "DR 02", diretoriaNome: "Sudeste", gerenciaRegional: "GR 03", regional: "Regional São Paulo", gerenteGestao: "GG 03", agenciaCodigo: "Ag 1004", agencia: "Agência 1004 • Avenida Paulista", segmento: "Negócios", produtoId: "consorcios", subproduto: "Parcelado", gerente: "Gerente 4", gerenteNome: "Bruno Garcia", carteira: "Carteira Bandeirantes", linhas: 92.7, cash: 88.6, conquista: 94.2, atividade: true, data: "2025-09-10" },
  { id: "sd-capital", diretoria: "DR 02", diretoriaNome: "Sudeste", gerenciaRegional: "GR 03", regional: "Regional São Paulo", gerenteGestao: "GG 03", agenciaCodigo: "Ag 1004", agencia: "Agência 1004 • Avenida Paulista", segmento: "Negócios", produtoId: "cartoes", subproduto: "À vista", gerente: "Gerente 4", gerenteNome: "Bruno Garcia", carteira: "Carteira Capital", linhas: 105.6, cash: 102.4, conquista: 100.2, atividade: true, data: "2025-09-18" },
  { id: "sc-curitiba", diretoria: "DR 03", diretoriaNome: "Sul & Centro-Oeste", gerenciaRegional: "GR 04", regional: "Regional Curitiba", gerenteGestao: "GG 02", agenciaCodigo: "Ag 1003", agencia: "Agência 1003 • Curitiba Batel", segmento: "MEI", produtoId: "seguros", subproduto: "Aplicação", gerente: "Gerente 5", gerenteNome: "Carla Menezes", carteira: "Carteira Curitiba", linhas: 109.6, cash: 101.2, conquista: 98.5, atividade: true, data: "2025-09-11" },
  { id: "sc-litoral", diretoria: "DR 03", diretoriaNome: "Sul & Centro-Oeste", gerenciaRegional: "GR 04", regional: "Regional Curitiba", gerenteGestao: "GG 02", agenciaCodigo: "Ag 1003", agencia: "Agência 1003 • Curitiba Batel", segmento: "MEI", produtoId: "bradesco_expresso", subproduto: "Resgate", gerente: "Gerente 5", gerenteNome: "Carla Menezes", carteira: "Carteira Litoral", linhas: 95.4, cash: 90.1, conquista: 92.8, atividade: true, data: "2025-09-07" },
  { id: "sc-vale", diretoria: "DR 03", diretoriaNome: "Sul & Centro-Oeste", gerenciaRegional: "GR 04", regional: "Regional Curitiba", gerenteGestao: "GG 02", agenciaCodigo: "Ag 1003", agencia: "Agência 1003 • Curitiba Batel", segmento: "MEI", produtoId: "rec_credito", subproduto: "À vista", gerente: "Gerente 5", gerenteNome: "Carla Menezes", carteira: "Carteira Vale", linhas: 120.2, cash: 115.6, conquista: 110.4, atividade: true, data: "2025-09-17" },
  { id: "nn-manaus", diretoria: "DR 01", diretoriaNome: "Norte & Nordeste", gerenciaRegional: "GR 05", regional: "Regional Manaus", gerenteGestao: "GG 05", agenciaCodigo: "Ag 2001", agencia: "Agência 2001 • Manaus Centro", segmento: "Negócios", produtoId: "captacao_bruta", subproduto: "Aplicação", gerente: "Lara Costa", gerenteNome: "Lara Costa", carteira: "Carteira Amazônia", linhas: 119.4, cash: 111.8, conquista: 103.2, atividade: true, data: "2025-09-12" },
  { id: "sc-floripa", diretoria: "DR 03", diretoriaNome: "Sul & Centro-Oeste", gerenciaRegional: "GR 06", regional: "Regional Florianópolis", gerenteGestao: "GG 06", agenciaCodigo: "Ag 2002", agencia: "Agência 2002 • Florianópolis Beira-Mar", segmento: "Empresas", produtoId: "rotativo_pj_vol", subproduto: "Volume", gerente: "Sofia Martins", gerenteNome: "Sofia Martins", carteira: "Carteira Litoral", linhas: 108.6, cash: 116.3, conquista: 105.5, atividade: true, data: "2025-09-16" },
  { id: "sc-goiania", diretoria: "DR 03", diretoriaNome: "Sul & Centro-Oeste", gerenciaRegional: "GR 07", regional: "Regional Goiânia", gerenteGestao: "GG 07", agenciaCodigo: "Ag 2003", agencia: "Agência 2003 • Goiânia Setor Bueno", segmento: "MEI", produtoId: "bradesco_expresso", subproduto: "Expresso", gerente: "Tiago Andrade", gerenteNome: "Tiago Andrade", carteira: "Carteira Centro-Oeste", linhas: 102.5, cash: 94.2, conquista: 97.1, atividade: true, data: "2025-09-15" },
  { id: "sd-campinas", diretoria: "DR 02", diretoriaNome: "Sudeste", gerenciaRegional: "GR 05", regional: "Regional Campinas", gerenteGestao: "GG 05", agenciaCodigo: "Ag 2004", agencia: "Agência 2004 • Campinas Tech", segmento: "Negócios", produtoId: "centralizacao", subproduto: "Cash", gerente: "Eduardo Freitas", gerenteNome: "Eduardo Freitas", carteira: "Carteira Inovação", linhas: 123.1, cash: 129.4, conquista: 111.7, atividade: true, data: "2025-09-09" }
];

// Aplica aliases aos dados padrão
if (typeof aplicarIndicadorAliases === "function") {
  DEFAULT_CAMPAIGN_UNIT_DATA.forEach(unit => aplicarIndicadorAliases(unit, unit.produtoId, unit.produtoNome || unit.produtoId));
}

/* ===== Função para normalizar linhas de campanhas ===== */
// Aqui eu padronizo os dados das campanhas porque preciso ligar sprint, unidade e indicadores rapidamente.
function normalizarLinhasFatoCampanhas(rows){
  return rows.map(raw => {
    const id = raw.campanha_id || raw.id || raw.campanhaId;
    if (!id) return null;
    const sprintId = raw.sprint_id || raw.sprintId || raw.sprint;
    const diretoriaId = raw.diretoria_id || raw.diretoriaId || raw.diretoria;
    const diretoriaNome = raw.diretoria_nome || raw.diretoriaNome || raw.diretoria_regional || diretoriaId;
    const gerenciaId = raw.gerencia_id || raw.gerenciaId || raw.gerencia_regional || raw.gerenciaRegional;
    const regionalNome = raw.regional_nome || raw.regionalNome || raw.regional || gerenciaId;
    const agenciaCodigoRaw = raw.agencia_codigo || raw.agenciaCodigo || raw.agencia_id || raw.agenciaId;
    const agenciaNome = raw.agencia_nome || raw.agenciaNome || raw.agencia || agenciaCodigoRaw;
    const agenciaId = agenciaCodigoRaw || agenciaNome;
    // Suporta tanto estrutura plana quanto objeto aninhado {id, nome}
    let gerenteGestaoIdRaw = "";
    let gerenteGestaoNome = "";
    const gerenteGestaoObj = raw.gerente_gestao || raw.gerenteGestao;
    if (gerenteGestaoObj && typeof gerenteGestaoObj === "object" && !Array.isArray(gerenteGestaoObj)) {
      gerenteGestaoIdRaw = limparTexto(gerenteGestaoObj.id || "");
      gerenteGestaoNome = limparTexto(gerenteGestaoObj.nome || "");
    } else {
      gerenteGestaoIdRaw = raw.gerente_gestao_id || raw.gerenteGestaoId || raw.gerente_gestao || raw.gerenteGestao;
      gerenteGestaoNome = raw.gerente_gestao_nome || raw.gerenteGestaoNome || gerenteGestaoIdRaw;
    }
    const gerenteGestaoId = gerenteGestaoIdRaw || gerenteGestaoNome;
    const gerenteIdRaw = raw.gerente_id || raw.gerenteId || raw.gerente;
    const gerenteNome = raw.gerente_nome || raw.gerenteNome || gerenteIdRaw;
    const gerenteId = gerenteIdRaw || gerenteNome;
    const segmento = raw.segmento;
    let familiaId = raw.familia_id || raw.familiaId || raw.familia || "";
    let familiaNome = raw.familia_nome || raw.familiaNome || familiaId;
    let produtoId = raw.id_indicador || raw.produto_id || raw.produtoId || raw.produto;
    if (!produtoId) return null;
    const produtoNome = raw.ds_indicador || raw.produto_nome || raw.produtoNome || produtoId;
    const subproduto = raw.subproduto || raw.sub_produto || "";
    const familiaCodigoExtra = raw.familia_codigo || raw.familiaCodigo || raw.familiaCod;
    const indicadorCodigoExtra = raw.indicador_codigo || raw.indicadorCodigo || raw.indicadorCod;
    const subCodigoExtra = raw.subindicador_codigo || raw.subindicadorCodigo || raw.subindicadorCod;
    const familiaCodigo = limparTexto(familiaCodigoExtra);
    if (familiaCodigo) {
      const familiaSlug = typeof FAMILIA_CODE_TO_SLUG !== "undefined" ? FAMILIA_CODE_TO_SLUG.get(familiaCodigo) : null;
      if (familiaSlug && (!familiaId || familiaId === familiaCodigo)) {
        familiaId = familiaSlug;
        if (!familiaNome || familiaNome === familiaCodigo) {
          const famMeta = typeof FAMILIA_BY_ID !== "undefined" ? FAMILIA_BY_ID.get(familiaSlug) : null;
          if (famMeta?.nome) familiaNome = famMeta.nome;
        }
      }
    }
    const indicadorCodigo = limparTexto(indicadorCodigoExtra);
    if (indicadorCodigo) {
      const indicadorSlug = typeof INDICADOR_CODE_TO_SLUG !== "undefined" ? INDICADOR_CODE_TO_SLUG.get(indicadorCodigo) : null;
      if (indicadorSlug) {
        produtoId = indicadorSlug;
      }
    }
    const subCodigo = limparTexto(subCodigoExtra);
    const subSlug = subCodigo && typeof SUB_CODE_TO_SLUG !== "undefined" ? SUB_CODE_TO_SLUG.get(subCodigo) : "";
    const carteira = raw.carteira || "";
    const linhas = toNumber(raw.linhas || 0);
    const cash = toNumber(raw.cash || 0);
    const conquista = toNumber(raw.conquista || 0);
    const atividade = converterBooleano(raw.atividade || raw.ativo || raw.status, true);
    let data = converterDataISO(raw.data || "");
    let competencia = converterDataISO(raw.competencia || "");
    if (!data && competencia) {
      data = competencia;
    }
    if (!competencia && data) {
      competencia = `${data.slice(0, 7)}-01`;
    }

    const base = {
      id,
      sprintId,
      diretoria: diretoriaId || diretoriaNome,
      diretoriaId: diretoriaId || diretoriaNome,
      diretoriaNome,
      gerenciaRegional: gerenciaId || regionalNome,
      gerenciaId: gerenciaId || regionalNome,
      regional: regionalNome,
      agenciaCodigo: agenciaCodigoRaw || agenciaId,
      agencia: agenciaId,
      agenciaId,
      agenciaNome,
      gerenteGestao: gerenteGestaoId,
      gerenteGestaoId,
      gerenteGestaoNome,
      gerente: gerenteId,
      gerenteId,
      gerenteNome,
      segmento,
      familiaId,
      familiaNome,
      subproduto,
      carteira,
      linhas,
      cash,
      conquista,
      atividade,
      data,
      competencia,
    };
    if (familiaCodigo) base.familiaCodigo = familiaCodigo;
    if (indicadorCodigo) base.indicadorCodigo = indicadorCodigo;
    if (subCodigo) base.subindicadorCodigo = subCodigo;
    if (subSlug) base.subprodutoId = subSlug;
    if (typeof aplicarIndicadorAliases === "function") {
      aplicarIndicadorAliases(base, produtoId, produtoNome);
    }
    return base;
  }).filter(Boolean);
}

/* ===== Função para substituir dados de unidade de campanha ===== */
function replaceCampaignUnitData(rows = []) {
  if (typeof converterDataISO === "undefined") {
    console.warn('converterDataISO não está disponível ainda. replaceCampaignUnitData será chamado novamente quando app.js carregar.');
    return;
  }
  
  CAMPAIGN_UNIT_DATA.length = 0;
  const source = Array.isArray(rows) && rows.length ? rows : DEFAULT_CAMPAIGN_UNIT_DATA;
  source.forEach(item => {
    const dataISO = converterDataISO(item.data);
    let competencia = converterDataISO(item.competencia);
    const resolvedData = dataISO || "";
    if (!competencia && resolvedData) {
      competencia = `${resolvedData.slice(0, 7)}-01`;
    }
    CAMPAIGN_UNIT_DATA.push({ ...item, data: resolvedData, competencia: competencia || "" });
  });
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.CAMPAIGN_UNIT_DATA = CAMPAIGN_UNIT_DATA;
  }
}

// Inicialização será feita após app.js carregar
// A função initializeCampaignUnitData será chamada em app.js após converterDataISO e PRODUCT_INDEX estarem disponíveis
function initializeCampaignUnitData() {
  if (typeof converterDataISO === "undefined") {
    return;
  }
  
  // Inicializa com dados padrão apenas se ainda não foi inicializado
  if (CAMPAIGN_UNIT_DATA.length === 0) {
    replaceCampaignUnitData(DEFAULT_CAMPAIGN_UNIT_DATA);
  }
  
  // Processa dados de unidade de campanha após produtos estarem disponíveis
  // Esta parte será feita em app.js onde já existe código para isso (linha ~2317)
  // Não precisa fazer aqui porque já é feito em montarCatalogoDeProdutos
}

/* ===== Função para carregar dados de campanhas da API ===== */
async function loadCampanhasData(filterParams = {}){
  try {
    const response = await apiGet('/campanhas', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de campanhas:', error);
    return [];
  }
}

/* ===== Função para processar dados de campanhas ===== */
function processCampanhasData(campanhasRaw = []) {
  FACT_CAMPANHAS = normalizarLinhasFatoCampanhas(Array.isArray(campanhasRaw) ? campanhasRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.FACT_CAMPANHAS = FACT_CAMPANHAS;
  }
  
  if (FACT_CAMPANHAS.length) {
    replaceCampaignUnitData(FACT_CAMPANHAS);
  }
  
  return FACT_CAMPANHAS;
}

// END campanhas.js

