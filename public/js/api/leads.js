// BEGIN api/leads.js
/* =========================================================
   POBJ • API Leads  —  Carregamento e processamento de dados de leads da API
   Endpoint: /api/leads
   ========================================================= */

/* ===== Função para carregar dados de leads da API ===== */
async function loadLeadsData(filterParams = {}){
  try {
    const response = await apiGet('/leads', filterParams).catch(() => null);
    if (!response) return [];
    
    // Verifica se a resposta está no novo formato { success, data }
    if (response && typeof response === 'object' && 'success' in response && 'data' in response) {
      return response.success && Array.isArray(response.data) ? response.data : [];
    }
    
    // Fallback para formato antigo (array direto)
    return Array.isArray(response) ? response : [];
  } catch (error) {
    console.error('Erro ao carregar dados de leads:', error);
    return [];
  }
}

/* ===== Função para processar dados de leads ===== */
function processLeadsData(leadsRaw = []) {
  // O processamento é feito pela função ingestOpportunityLeadRows que está em features/opportunities.js
  // Esta função apenas prepara os dados para serem processados
  const leadsArray = Array.isArray(leadsRaw) ? leadsRaw : [];
  
  // Atualiza OPPORTUNITY_LEADS_RAW se estiver disponível (definido em features/opportunities.js)
  if (typeof window !== "undefined" && typeof window.OPPORTUNITY_LEADS_RAW !== "undefined") {
    window.OPPORTUNITY_LEADS_RAW = leadsArray;
  } else if (typeof OPPORTUNITY_LEADS_RAW !== "undefined") {
    OPPORTUNITY_LEADS_RAW = leadsArray;
  }
  
  // Chama a função de ingestão se estiver disponível (definida em features/opportunities.js)
  if (typeof ingestOpportunityLeadRows === "function") {
    ingestOpportunityLeadRows(leadsArray);
  } else if (typeof window !== "undefined" && typeof window.ingestOpportunityLeadRows === "function") {
    window.ingestOpportunityLeadRows(leadsArray);
  }
  
  return leadsArray;
}

// END api/leads.js

