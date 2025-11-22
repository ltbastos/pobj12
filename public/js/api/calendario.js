// BEGIN api/calendario.js
/* =========================================================
   POBJ • API Calendário  —  Carregamento e processamento de dados de calendário
   Endpoint: /api/calendario
   ========================================================= */

/* ===== Variável de calendário ===== */
var DIM_CALENDARIO = [];

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.DIM_CALENDARIO = DIM_CALENDARIO;
}

/* ===== Função para normalizar linhas de calendário ===== */
function normalizarLinhasCalendario(rows){
  return rows.map(raw => {
    const data = converterDataISO(raw.data || "");
    if (!data) return null;
    const competencia = converterDataISO(raw.competencia || "") || `${data.slice(0, 7)}-01`;
    const ano = raw.ano || data.slice(0, 4);
    const mes = raw.mes || data.slice(5, 7);
    const mesNome = raw.mes_nome || raw.mesNome || "";
    const dia = raw.dia || data.slice(8, 10);
    const diaSemana = raw.dia_semana || raw.diaSemana || "";
    const semana = raw.semana || "";
    const trimestre = raw.trimestre || "";
    const semestre = raw.semestre || "";
    const ehDiaUtil = converterBooleano(raw.eh_dia_util || raw.ehDiaUtil || raw.dia_util || raw.diaUtil, false) ? 1 : 0;
    const mesAnoCurto = construirEtiquetaMesAno(ano, mes, mesNome);
    return { data, competencia, ano, mes, mesNome, mesAnoCurto, dia, diaSemana, semana, trimestre, semestre, ehDiaUtil };
  }).filter(Boolean).sort((a, b) => (a.data || "").localeCompare(b.data || ""));
}

/* ===== Função para carregar dados de calendário da API ===== */
async function loadCalendarioData(){
  try {
    const calendario = await apiGet('/calendario').catch(() => []);
    return Array.isArray(calendario) ? calendario : [];
  } catch (error) {
    console.error('Erro ao carregar dados de calendário:', error);
    return [];
  }
}

/* ===== Função para processar dados de calendário ===== */
function processCalendarioData(calendarioRaw = []) {
  DIM_CALENDARIO = normalizarLinhasCalendario(Array.isArray(calendarioRaw) ? calendarioRaw : []);
  
  // Atualiza referência global
  if (typeof window !== "undefined") {
    window.DIM_CALENDARIO = DIM_CALENDARIO;
  }
  
  return DIM_CALENDARIO;
}

// END calendario.js

