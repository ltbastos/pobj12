// BEGIN api/historico.js
/* =========================================================
   POBJ • API Histórico  —  Carregamento e processamento de dados de histórico de ranking
   Endpoint: /api/historico
   ========================================================= */

/* ===== Variável global relacionada a histórico ===== */
var FACT_HISTORICO_RANKING_POBJ = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_HISTORICO_RANKING_POBJ = FACT_HISTORICO_RANKING_POBJ;
}

/* ===== Função para normalizar linhas de histórico ===== */
function normalizarLinhasHistoricoRankingPobj(rows){
  if (!Array.isArray(rows)) return [];

  const mapNivel = (value) => {
    const simple = simplificarTexto(value);
    if (simple === "diretoria") return "diretoria";
    if (simple === "gerencia" || simple === "gerenciaregional") return "gerencia";
    if (simple === "agencia") return "agencia";
    if (simple === "gerente") return "gerente";
    return "";
  };

  return rows.map(raw => {
    const nivel = mapNivel(raw.nivel || raw.level || "");
    if (!nivel) return null;

    const anoText = raw.ano || raw.year || "";
    const anoNum = Number(anoText);
    const ano = Number.isFinite(anoNum) ? anoNum : null;
    const database = typeof converterDataISO === "function" 
      ? converterDataISO(raw.database || raw.competencia || raw.data || "")
      : "";

    const segmento = raw.segmento || "";
    const segmentoId = raw.segmento_id || raw.segmentoId || "";
    const diretoria = raw.diretoria_id || raw.diretoriaId || raw.diretoria || "";
    const diretoriaNome = raw.diretoria_nome || raw.diretoriaNome || "";
    const gerenciaRegional = raw.gerencia_id || raw.gerenciaId || raw.gerencia_regional || raw.gerenciaRegional || raw.gerencia || "";
    const gerenciaNome = raw.gerencia_nome || raw.gerenciaNome || raw.regional_nome || raw.regionalNome || "";
    const agencia = raw.agencia_id || raw.agenciaId || raw.agencia || "";
    const agenciaNome = raw.agencia_nome || raw.agenciaNome || "";
    const agenciaCodigo = raw.agencia_codigo || raw.agenciaCodigo || "";
    const gerenteGestao = raw.gerente_gestao_id || raw.gerenteGestaoId || raw.gerente_gestao || raw.gerenteGestao || "";
    const gerenteGestaoNome = raw.gerente_gestao_nome || raw.gerenteGestaoNome || "";
    const gerente = raw.gerente_id || raw.gerenteId || raw.gerente || "";
    const gerenteNome = raw.gerente_nome || raw.gerenteNome || "";

    const participantesNum = Number(raw.participantes || raw.total_participantes || raw.totalParticipantes || 0);
    const participantes = Number.isFinite(participantesNum) && participantesNum > 0 ? participantesNum : null;

    const rankNum = Number(raw.rank || raw.posicao || raw.posição || raw.classificacao || 0);
    const rank = Number.isFinite(rankNum) && rankNum > 0 ? rankNum : null;

    const pontosNum = Number(raw.pontos || raw.pontuacao || raw.p_acum || 0);
    const pontos = Number.isFinite(pontosNum) ? pontosNum : null;

    const realizadoNum = Number(raw.realizado || raw.real_acum || raw.resultado || 0);
    const metaNum = Number(raw.meta || raw.meta_acum || 0);

    return {
      nivel,
      ano,
      database: database || (ano ? `${ano}-12-31` : ""),
      segmento,
      segmentoId,
      diretoria,
      diretoriaId: diretoria || diretoriaNome,
      diretoriaNome: diretoriaNome || diretoria,
      gerenciaRegional,
      gerenciaId: gerenciaRegional || gerenciaNome,
      gerenciaNome: gerenciaNome || gerenciaRegional,
      agencia,
      agenciaId: agencia || agenciaCodigo || agenciaNome,
      agenciaNome: agenciaNome || agencia,
      agenciaCodigo: agenciaCodigo || agencia,
      gerenteGestao,
      gerenteGestaoId: gerenteGestao || gerenteGestaoNome,
      gerenteGestaoNome: gerenteGestaoNome || gerenteGestao,
      gerente,
      gerenteId: gerente || gerenteNome,
      gerenteNome: gerenteNome || gerente,
      participantes,
      rank,
      pontos,
      realizado: Number.isFinite(realizadoNum) ? realizadoNum : null,
      meta: Number.isFinite(metaNum) ? metaNum : null,
    };
  }).filter(Boolean);
}

/* ===== Função para carregar dados de histórico da API ===== */
async function loadHistoricoData(filterParams = {}){
  try {
    const response = await apiGet('/historico', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de histórico:', error);
    return [];
  }
}

/* ===== Função para processar dados de histórico ===== */
function processHistoricoData(historicoRaw = []) {
  FACT_HISTORICO_RANKING_POBJ = normalizarLinhasHistoricoRankingPobj(Array.isArray(historicoRaw) ? historicoRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.FACT_HISTORICO_RANKING_POBJ = FACT_HISTORICO_RANKING_POBJ;
  }
  
  return FACT_HISTORICO_RANKING_POBJ;
}

// END historico.js

