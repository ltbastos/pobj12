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
function normalizarLinhasFatoVariavel(rows){
  return rows.map(raw => {
    if (!raw || (!raw.id && !raw.registro_id)) return null;
    
    // Campos principais
    const id = raw.id || raw.registro_id;
    const funcional = raw.funcional || "";
    // Usa variavel_meta e variavel_real da API (campos retornados pelo DTO)
    const variavelMeta = toNumber(raw.variavel_meta ?? raw.meta) || 0;
    const variavelReal = toNumber(raw.variavel_real ?? raw.variavel) || 0;
    const dtAtualizacao = converterDataISO(raw.dt_atualizacao);
    
    // Campos de estrutura (vêm diretamente da API)
    const segmento = raw.segmento || "";
    const segmentoId = raw.segmento_id || "";
    const diretoriaNome = raw.diretoria_nome || "";
    const diretoriaId = raw.diretoria_id || "";
    const regionalNome = raw.regional_nome || "";
    const gerenciaId = raw.gerencia_id || "";
    const agenciaNome = raw.agencia_nome || "";
    const agenciaId = raw.agencia_id || "";
    const nomeFuncional = raw.nome_funcional || "";
    
    // Tenta resolver produto/família se houver campos relacionados
    let produtoId = raw.id_indicador || "";
    let produtoNome = raw.ds_indicador || "";
    let familiaId = raw.familia_id || "";
    let familiaNome = raw.familia_nome || "";
    
    const base = {
      id,
      funcional,
      variavelMeta,
      variavelReal,
      dtAtualizacao,
      nomeFuncional,
      segmento,
      segmentoId,
      diretoriaNome,
      diretoriaId,
      regionalNome,
      gerenciaId,
      agenciaNome,
      agenciaId,
      produtoId,
      produtoNome,
      familiaId,
      familiaNome,
    };
    
    return base;
  }).filter(Boolean);
}

/* ===== Função para carregar dados de variável da API ===== */
async function loadVariavelData(filterParams = {}){
  try {
    const response = await apiGet('/variavel', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
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

