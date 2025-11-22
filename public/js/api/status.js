// BEGIN api/status.js
/* =========================================================
   POBJ • API Status  —  Carregamento e processamento de dados de status de indicadores
   Endpoint: /api/status_indicadores
   ========================================================= */

/* ===== Constantes e variáveis de status ===== */
const STATUS_LABELS = {
  todos: "Todos",
  atingidos: "Atingidos",
  nao: "Não atingidos",
};

// Aqui eu defino uma ordem padrão de status.
const DEFAULT_STATUS_ORDER = ["todos", "atingidos", "nao"];
const DEFAULT_STATUS_INDICADORES = DEFAULT_STATUS_ORDER.map((key, idx) => ({
  id: key,
  codigo: key,
  nome: STATUS_LABELS[key] || key,
  key,
  ordem: idx,
}));

var STATUS_INDICADORES_DATA = DEFAULT_STATUS_INDICADORES.map(item => ({ ...item }));
// Aqui eu mantenho um Map para buscar status pelo código sem precisar ficar percorrendo arrays.
var STATUS_BY_KEY = new Map(DEFAULT_STATUS_INDICADORES.map(entry => [entry.key, { ...entry }]));

// Disponibiliza globalmente se window estiver disponível
if (typeof window !== "undefined") {
  window.STATUS_INDICADORES_DATA = STATUS_INDICADORES_DATA;
  window.STATUS_BY_KEY = STATUS_BY_KEY;
  window.DEFAULT_STATUS_INDICADORES = DEFAULT_STATUS_INDICADORES;
}

/* ===== Funções auxiliares para normalização de status ===== */
function normalizarChaveStatus(value) {
  const text = limparTexto(value);
  if (!text) return "";
  const ascii = text.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  const lower = ascii.toLowerCase().replace(/\s+/g, " ").trim();
  if (!lower) return "";
  // Verifica padrões numéricos com zeros à esquerda também
  if (/^(?:0?1|todos?)$/.test(lower) || lower.includes("todos")) return "todos";
  if (/^(?:0?2)$/.test(lower)) return "atingidos";
  if (/^(?:0?3)$/.test(lower)) return "nao";
  if (/(?:^|\b)(?:nao|na|no)\s+atingid/.test(lower)) return "nao";
  if (lower.includes("atingid")) return "atingidos";
  if (lower.includes("nao")) return "nao";
  const slug = lower.replace(/[^a-z0-9]+/g, "_").replace(/^_+|_+$/g, "");
  if (slug === "no_atingidos") return "nao";
  return slug;
}

// Aqui eu traduzo a chave do status para o rótulo certo exibido na tela, sempre tentando usar as descrições oficiais.
function obterRotuloStatus(key, fallback = "") {
  const normalized = normalizarChaveStatus(key);
  if (normalized && STATUS_BY_KEY.has(normalized)) {
    const entry = STATUS_BY_KEY.get(normalized);
    if (entry?.nome) return entry.nome;
  }
  if (normalized && STATUS_LABELS[normalized]) return STATUS_LABELS[normalized];
  if (STATUS_LABELS[key]) return STATUS_LABELS[key];
  const fallbackText = limparTexto(fallback);
  if (fallbackText) return fallbackText;
  return normalized || key;
}

/* ===== Função para normalizar linhas de status ===== */
function normalizarLinhasStatus(rows){
  const normalized = [];
  const seen = new Set();
  const missing = new Set();
  const list = Array.isArray(rows) ? rows : [];

  if (!list.length) {
  }

  const register = (candidate = {}) => {
    const rawKey = pegarPrimeiroPreenchido(
      candidate.key,
      candidate.slug,
      candidate.id,
      candidate.codigo,
      candidate.nome
    );
    const resolvedKey = normalizarChaveStatus(rawKey);
    if (!resolvedKey) return false;
    if (seen.has(resolvedKey)) return true;

    const codigo = limparTexto(candidate.codigo)
      || limparTexto(candidate.id)
      || resolvedKey;
    const nome = limparTexto(candidate.nome)
      || STATUS_LABELS[resolvedKey]
      || limparTexto(candidate.label)
      || codigo
      || resolvedKey;
    const originalId = limparTexto(candidate.id) || codigo || resolvedKey;
    const ordemRaw = limparTexto(candidate.ordem);
    const ordemNum = Number(ordemRaw);
    const ordem = ordemRaw !== "" && Number.isFinite(ordemNum) ? ordemNum : undefined;

    const entry = { id: originalId, codigo, nome, key: resolvedKey };
    if (ordem !== undefined) entry.ordem = ordem;

    normalized.push(entry);
    seen.add(resolvedKey);
    return true;
  };

  list.forEach(raw => {
    if (!raw || typeof raw !== "object") return;
    // Padronizado: backend retorna 'label', com fallback apenas para compatibilidade temporária
    const nome = raw.label || raw.nome || "";
    const codigo = raw.codigo || raw.id || "";
    const chave = raw.chave || raw.key || "";
    const ordem = raw.ordem || raw.order || "";
    // Prioriza o nome (label) para normalizar a chave, pois é mais descritivo
    const key = pegarPrimeiroPreenchido(chave, nome, codigo);
    const ok = register({ id: codigo || key, codigo, nome, key, ordem });
    if (!ok) {
      const fallback = pegarPrimeiroPreenchido(nome, codigo, chave);
      if (fallback) missing.add(fallback);
    }
  });

  DEFAULT_STATUS_INDICADORES.forEach(item => register(item));

  if (missing.size) {
    console.warn(`Status sem identificador válido: ${Array.from(missing).join(", ")}`);
  }

  return normalized;
}

/* ===== Função para reconstruir índice de status ===== */
function rebuildStatusIndex(rows) {
  const cleaned = [];
  const map = new Map();
  const source = Array.isArray(rows) ? rows : [];

  source.forEach(item => {
    if (!item || typeof item !== "object") return;
    const key = item.key || normalizarChaveStatus(item.id ?? item.codigo ?? item.nome);
    if (!key || map.has(key)) return;
    const ordemRaw = limparTexto(item.ordem);
    const ordemNum = Number(ordemRaw);
    const ordem = ordemRaw !== "" && Number.isFinite(ordemNum) ? ordemNum : undefined;
    const entry = { ...item, key };
    if (ordem !== undefined) entry.ordem = ordem;
    cleaned.push(entry);
    map.set(key, entry);
  });

  DEFAULT_STATUS_INDICADORES.forEach(defaultItem => {
    if (map.has(defaultItem.key)) return;
    const entry = { ...defaultItem };
    cleaned.push(entry);
    map.set(entry.key, entry);
  });

  cleaned.sort((a, b) => {
    if (a.key === "todos") return -1;
    if (b.key === "todos") return 1;
    const ordA = Number.isFinite(a.ordem) ? a.ordem : Number.MAX_SAFE_INTEGER;
    const ordB = Number.isFinite(b.ordem) ? b.ordem : Number.MAX_SAFE_INTEGER;
    if (ordA !== ordB) return ordA - ordB;
    return String(a.nome || "").localeCompare(String(b.nome || ""), "pt-BR", { sensitivity: "base" });
  });

  STATUS_INDICADORES_DATA = cleaned;
  STATUS_BY_KEY = map;
  
  // Atualiza referências globais
  if (typeof window !== "undefined") {
    window.STATUS_INDICADORES_DATA = STATUS_INDICADORES_DATA;
    window.STATUS_BY_KEY = STATUS_BY_KEY;
  }
  
  updateStatusFilterOptions();
}

/* ===== Função para obter entrada de status ===== */
function getStatusEntry(key) {
  const normalized = normalizarChaveStatus(key);
  if (!normalized) return null;
  return STATUS_BY_KEY.get(normalized) || null;
}

/* ===== Função para construir entradas de filtro de status ===== */
function buildStatusFilterEntries() {
  const base = Array.isArray(STATUS_INDICADORES_DATA) ? STATUS_INDICADORES_DATA : [];
  const entries = base.map(st => {
    const key = st?.key || normalizarChaveStatus(st?.id ?? st?.codigo ?? st?.nome);
    if (!key) return null;
    const label = st?.nome || obterRotuloStatus(key, st?.codigo ?? st?.id ?? key);
    const codigo = st?.codigo ?? st?.id ?? key;
    let ordem = st?.ordem;
    if (typeof ordem === "string" && ordem !== "") {
      const parsed = Number(ordem);
      ordem = Number.isFinite(parsed) ? parsed : undefined;
    }
    if (!Number.isFinite(ordem)) {
      ordem = Number.isFinite(st?.ordem) ? st.ordem : Number.MAX_SAFE_INTEGER;
    }
    return {
      key,
      value: key,
      label,
      codigo,
      id: st?.id ?? codigo,
      ordem,
    };
  }).filter(Boolean);

  // Remove duplicatas baseadas na chave 'key'
  const seenKeys = new Set();
  const uniqueEntries = [];
  entries.forEach(entry => {
    if (!seenKeys.has(entry.key)) {
      seenKeys.add(entry.key);
      uniqueEntries.push(entry);
    }
  });

  if (!uniqueEntries.some(entry => entry.key === "todos")) {
    uniqueEntries.unshift({
      key: "todos",
      value: "todos",
      label: STATUS_LABELS.todos,
      codigo: "todos",
      id: "todos",
      ordem: -Infinity,
    });
  }

  uniqueEntries.sort((a, b) => {
    if (a.key === "todos") return -1;
    if (b.key === "todos") return 1;
    if (a.ordem !== b.ordem) return a.ordem - b.ordem;
    return String(a.label || "").localeCompare(String(b.label || ""), "pt-BR", { sensitivity: "base" });
  });

  return uniqueEntries;
}

/* ===== Função para atualizar opções de filtro de status ===== */
let _updatingStatusFilter = false;
function updateStatusFilterOptions(preserveSelection = true) {
  const select = document.getElementById("f-status-kpi");
  if (!select) return;
  
  // Evita execuções simultâneas
  if (_updatingStatusFilter) return;
  _updatingStatusFilter = true;
  
  try {

  const previousOption = select.selectedOptions?.[0] || null;
  const previousKey = preserveSelection
    ? (previousOption?.dataset.statusKey || normalizarChaveStatus(select.value) || "")
    : "";

  const entries = buildStatusFilterEntries();
  select.innerHTML = "";

  // Garante que não há duplicatas antes de adicionar
  const addedValues = new Set();
  entries.forEach(entry => {
    // Pula se já foi adicionado (proteção adicional)
    if (addedValues.has(entry.value)) return;
    addedValues.add(entry.value);
    
    const opt = document.createElement("option");
    opt.value = entry.value;
    opt.textContent = entry.label;
    opt.dataset.statusKey = entry.key;
    if (entry.codigo !== undefined) opt.dataset.statusCodigo = entry.codigo;
    if (entry.id !== undefined) opt.dataset.statusId = entry.id;
    select.appendChild(opt);
  });

  if (preserveSelection && previousKey) {
    const match = entries.find(entry => entry.key === previousKey);
    if (match) {
      select.value = match.value;
    }
  }

  if (!entries.some(entry => entry.value === select.value)) {
    const fallback = entries.find(entry => entry.key === "todos") || entries[0];
    if (fallback) {
      select.value = fallback.value;
    }
  }
  
  } finally {
    _updatingStatusFilter = false;
  }
}

/* ===== Função para carregar dados de status da API ===== */
async function loadStatusData(){
  try {
    const status = await apiGet('/status_indicadores').catch(() => []);
    return Array.isArray(status) ? status : [];
  } catch (error) {
    console.error('Erro ao carregar dados de status:', error);
    return [];
  }
}

/* ===== Função para processar dados de status ===== */
function processStatusData(statusRaw = []) {
  const statusRows = normalizarLinhasStatus(Array.isArray(statusRaw) ? statusRaw : []);
  if (statusRows.length) {
    rebuildStatusIndex(statusRows);
  } else {
    rebuildStatusIndex(DEFAULT_STATUS_INDICADORES);
  }
  return STATUS_INDICADORES_DATA;
}

// END status.js

