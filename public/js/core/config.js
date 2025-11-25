// BEGIN core/config.js
/* =========================================================
   POBJ • Configurações Globais
   ========================================================= */

// Dimensões por cenário (globais)
// eslint-disable-next-line no-var
var segMap;
// eslint-disable-next-line no-var
var dirMap;
// eslint-disable-next-line no-var
var regMap;
// eslint-disable-next-line no-var
var ageMap;
// eslint-disable-next-line no-var
var agMap;
// eslint-disable-next-line no-var
var ggMap;
// eslint-disable-next-line no-var
var gerMap;
// eslint-disable-next-line no-var
var gaMap;

if (typeof window !== "undefined") {
  window.segMap = window.segMap || new Map();
  window.dirMap = window.dirMap || new Map();
  window.regMap = window.regMap || new Map();
  const sharedAgencyMap = (window.agMap instanceof Map && window.agMap)
    || (window.ageMap instanceof Map && window.ageMap)
    || new Map();
  window.ageMap = sharedAgencyMap;
  window.agMap = sharedAgencyMap;
  window.ggMap = window.ggMap || new Map();
  window.gerMap = window.gerMap || new Map();
  window.gaMap = window.gaMap || new Map();
  segMap = window.segMap;
  dirMap = window.dirMap;
  regMap = window.regMap;
  ageMap = window.ageMap;
  agMap = window.agMap;
  ggMap = window.ggMap;
  gerMap = window.gerMap;
  gaMap = window.gaMap;
} else {
  segMap = new Map();
  dirMap = new Map();
  regMap = new Map();
  ageMap = new Map();
  agMap = ageMap;
  ggMap = new Map();
  gerMap = new Map();
  gaMap = new Map();
  if (typeof globalThis !== "undefined") {
    if (!globalThis.ageMap) globalThis.ageMap = ageMap;
    if (!globalThis.agMap) globalThis.agMap = agMap;
    if (!globalThis.ggMap) globalThis.ggMap = ggMap;
    if (!globalThis.gerMap) globalThis.gerMap = gerMap;
    if (!globalThis.gaMap) globalThis.gaMap = gaMap;
  }
}

const DATA_SOURCE = "sql";
const API_PATH = typeof window !== "undefined" && window.API_URL
  ? String(window.API_URL)
  : "../src/index.php";
const DEFAULT_HTTP_BASE = typeof window !== "undefined" && window.location && window.location.origin
  ? window.location.origin
  : "http://localhost:8000";
const API_HTTP_BASE = (typeof window !== "undefined" && window.API_HTTP_BASE)
  ? String(window.API_HTTP_BASE)
  : DEFAULT_HTTP_BASE;
const API_ENDPOINT_PARAM = "endpoint";
const TICKET_URL = "/omega.html";

// Chat embutido
const CHAT_MODE = "http";  // "iframe" | "http"
const CHAT_IFRAME_URL = "";

// Formatadores
const fmtBRL = new Intl.NumberFormat("pt-BR", { style:"currency", currency:"BRL" });
const fmtINT = new Intl.NumberFormat("pt-BR");
const fmtONE = new Intl.NumberFormat("pt-BR", { minimumFractionDigits:1, maximumFractionDigits:1 });

// Cores da visão executiva
const EXEC_BAR_FILL = "#93c5fd";
const EXEC_BAR_STROKE = "#60a5fa";
const EXEC_META_COLOR = "#fca5a5";
const EXEC_SERIES_PALETTE = [
  "#2563eb", "#9333ea", "#0ea5e9", "#16a34a", "#f97316",
  "#ef4444", "#14b8a6", "#d946ef", "#f59e0b", "#22d3ee"
];

// Símbolo da moeda
const fmtBRLParts = fmtBRL.formatToParts(1);
const CURRENCY_SYMBOL = fmtBRLParts.find(p => p.type === "currency")?.value || "R$";
const CURRENCY_LITERAL = fmtBRLParts.find(p => p.type === "literal")?.value || " ";
const CURRENT_CALENDAR_YEAR = new Date().getFullYear();

// Regras de sufixo
const SUFFIX_RULES = [
  { value: 1_000_000_000_000, singular: "trilhão", plural: "trilhões" },
  { value: 1_000_000_000,     singular: "bilhão",  plural: "bilhões" },
  { value: 1_000_000,         singular: "milhão",  plural: "milhões" },
  { value: 1_000,             singular: "mil",     plural: "mil" }
];

// Meses
const MONTH_ABBREVIATIONS_PT_BR = [
  "Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"
];
const MONTH_ABBREVIATION_ALIASES = [
  ["jan", "janeiro"],
  ["fev", "fevereiro"],
  ["mar", "marco", "março"],
  ["abr", "abril"],
  ["mai", "maio"],
  ["jun", "junho"],
  ["jul", "julho"],
  ["ago", "agosto"],
  ["set", "setembro"],
  ["out", "outubro"],
  ["nov", "novembro"],
  ["dez", "dezembro"]
];

// Storage keys
const RESUMO_MODE_STORAGE_KEY = "pobj.resumoMode";

// Motivos de cancelamento
const MOTIVOS_CANCELAMENTO = [
  "Solicitação do cliente",
  "Inadimplência",
  "Renovação antecipada",
  "Ajuste comercial",
  "Migração de produto"
];

// Fatal error
const FATAL_ERROR_ID = "__fatal_error";
let FATAL_ERROR_VISIBLE = false;

// Segment scenario presets
const SEGMENT_SCENARIO_PRESETS = [
  { id: "D.R. VAREJO DIGITAL",          nome: "D.R. VAREJO DIGITAL",          slug: "dr_varejo_digital",          scenario: "varejo",   order: 10 },
  { id: "SUPER. PJ NEGÓCIOS DIG.",      nome: "SUPER. PJ NEGÓCIOS DIG.",      slug: "super_pj_negocios_dig",      scenario: "varejo",   order: 20 },
  { id: "SUPER. PF CLASSIC DIG.",       nome: "SUPER. PF CLASSIC DIG.",       slug: "super_pf_classic_dig",       scenario: "varejo",   order: 30 },
  { id: "D.R. EMPRESAS",                nome: "D.R. EMPRESAS",                slug: "dr_empresas",                scenario: "empresas", order: 40 },
  { id: "SUPER. VAREJO PRIME EMPRESAS", nome: "SUPER. VAREJO PRIME EMPRESAS", slug: "super_varejo_prime_empresas", scenario: "empresas", order: 50 },
  { id: "VAREJO + PF DIGITAL",          nome: "VAREJO + PF DIGITAL",          slug: "varejo_pf_digital",          scenario: "varejo",   order: 60 },
  { id: "Varejo",                       nome: "Varejo",                       slug: "varejo",                     scenario: "varejo",   order: 70, hidden: true },
  { id: "Empresas",                     nome: "Empresas",                       slug: "empresas",                   scenario: "empresas", order: 80, hidden: true },
];

// Selection markers
const DEFAULT_SELECTION_MARKERS = new Set(["", "todos", "todas", "todes", "all"]);

// Expor no window para compatibilidade
if (typeof window !== "undefined") {
  window.fmtBRL = fmtBRL;
  window.fmtINT = fmtINT;
  window.fmtONE = fmtONE;
  window.CURRENCY_SYMBOL = CURRENCY_SYMBOL;
  window.CURRENCY_LITERAL = CURRENCY_LITERAL;
  window.EXEC_BAR_FILL = EXEC_BAR_FILL;
  window.EXEC_BAR_STROKE = EXEC_BAR_STROKE;
  window.EXEC_META_COLOR = EXEC_META_COLOR;
  window.EXEC_SERIES_PALETTE = EXEC_SERIES_PALETTE;
}


