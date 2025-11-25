// BEGIN api/pontos.js
/* =========================================================
   POBJ • API Pontos  —  Carregamento e processamento de dados de pontos
   Endpoint: /api/pontos
   ========================================================= */

/* ===== Variável de pontos ===== */
var FACT_PONTOS = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.FACT_PONTOS = FACT_PONTOS;
}

/* ===== Função para normalizar linhas de pontos ===== */
function normalizarLinhasFatoPontos(rows){
  return rows.map(raw => {
    if (!raw || !raw.id_indicador) return null;

    return {
      id: raw.id,
      funcional: raw.funcional || "",
      idIndicador: raw.id_indicador,
      idFamilia: raw.id_familia,
      indicador: raw.indicador || "",
      meta: toNumber(raw.meta) || 0,
      realizado: toNumber(raw.realizado) || 0,
      dataRealizado: converterDataISO(raw.data_realizado),
      dtAtualizacao: converterDataISO(raw.dt_atualizacao)
    };
  }).filter(Boolean);
}

/* ===== Função para carregar dados de pontos da API ===== */
async function loadPontosData(filterParams = {}){
  try {
    const response = await apiGet('/pontos', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de pontos:', error);
    return [];
  }
}

/* ===== Função para processar dados de pontos ===== */
function processPontosData(pontosRaw = []) {
  FACT_PONTOS = normalizarLinhasFatoPontos(Array.isArray(pontosRaw) ? pontosRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.FACT_PONTOS = FACT_PONTOS;
  }
  
  return FACT_PONTOS;
}

// END pontos.js

