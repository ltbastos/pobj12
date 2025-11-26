// BEGIN core/formatters.js
/* =========================================================
   POBJ • Formatadores
   ========================================================= */

// Aqui eu converto qualquer valor para número sem deixar NaN escapar.
function toNumber(value) {
  const n = Number(value);
  return Number.isFinite(n) ? n : 0;
}

// Aqui eu fujo de problemas de XSS escapando HTML sempre que crio strings manualmente.
var escapeHTML = typeof escapeHTML === "function"
  ? escapeHTML
  : (value = "") => String(value).replace(/[&<>"']/g, (ch) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  }[ch]));

// Aqui eu deixo um formatador genérico para exibir números grandes com sufixo (mil, milhão...).
function formatNumberWithSuffix(value, { currency = false } = {}) {
  const n = toNumber(value);
  if (!Number.isFinite(n)) return currency ? fmtBRL.format(0) : fmtINT.format(0);
  const abs = Math.abs(n);
  if (abs < 1000) {
    return currency ? fmtBRL.format(n) : fmtINT.format(Math.round(n));
  }
  const rule = SUFFIX_RULES.find(r => abs >= r.value);
  if (!rule) {
    return currency ? fmtBRL.format(n) : fmtINT.format(Math.round(n));
  }
  const absScaled = abs / rule.value;
  const nearInteger = Math.abs(absScaled - Math.round(absScaled)) < 0.05;
  let digits;
  if (absScaled >= 100) digits = 0;
  else if (absScaled >= 10) digits = nearInteger ? 0 : 1;
  else digits = nearInteger ? 0 : 1;
  const numberFmt = new Intl.NumberFormat("pt-BR", { minimumFractionDigits: digits, maximumFractionDigits: digits });
  const formatted = numberFmt.format(absScaled);
  const isSingular = Math.abs(absScaled - 1) < 0.05;
  const label = isSingular ? rule.singular : rule.plural;
  if (currency) {
    const sign = n < 0 ? "-" : "";
    return `${sign}${CURRENCY_SYMBOL}${CURRENCY_LITERAL}${formatted} ${label}`;
  }
  const sign = n < 0 ? "-" : "";
  return `${sign}${formatted} ${label}`;
}

// Aqui eu reaproveito o formatador para mostrar números grandes sem estourar layout.
function formatIntReadable(value){
  return formatNumberWithSuffix(value, { currency: false });
}
function formatBRLReadable(value){
  return formatNumberWithSuffix(value, { currency: true });
}

function formatPoints(value, { withUnit = false } = {}) {
  const n = Math.round(toNumber(value));
  const formatted = fmtINT.format(n);
  return withUnit ? `${formatted} pts` : formatted;
}

function formatPeso(value) {
  const n = toNumber(value);
  if (!Number.isFinite(n)) return "0";
  // Para valores menores que 1, preserva até 2 casas decimais
  if (n < 1 && n > 0) {
    return n.toFixed(2);
  }
  // Para valores maiores ou iguais a 1, arredonda normalmente
  return fmtINT.format(Math.round(n));
}

function formatMetricFull(metric, value){
  const n = Math.round(toNumber(value));
  if(metric === "perc") return `${toNumber(value).toFixed(1)}%`;
  if(metric === "qtd")  return fmtINT.format(n);
  return fmtBRL.format(n);
}
function formatByMetric(metric, value){
  if(metric === "perc") return `${toNumber(value).toFixed(1)}%`;
  if(metric === "qtd")  return formatIntReadable(value);
  return formatBRLReadable(value);
}
function makeRandomForMetric(metric){
  if(metric === "perc"){
    const meta = 100;
    const realizado = Math.round(45 + Math.random()*75);
    const variavelMeta = Math.round(160_000 + Math.random()*180_000);
    return { meta, realizado, variavelMeta };
  }
  if(metric === "qtd"){
    const meta = Math.round(1_000 + Math.random()*19_000);
    const realizado = Math.round(meta * (0.75 + Math.random()*0.6));
    const variavelMeta = Math.round(150_000 + Math.random()*220_000);
    return { meta, realizado, variavelMeta };
  }
  const meta = Math.round(4_000_000 + Math.random()*16_000_000);
  const realizado = Math.round(meta * (0.75 + Math.random()*0.6));
  const variavelMeta = Math.round(320_000 + Math.random()*420_000);
  return { meta, realizado, variavelMeta };
}

// Expor no window para compatibilidade
if (typeof window !== "undefined") {
  window.toNumber = toNumber;
  window.escapeHTML = escapeHTML;
  window.formatNumberWithSuffix = formatNumberWithSuffix;
  window.formatIntReadable = formatIntReadable;
  window.formatBRLReadable = formatBRLReadable;
  window.formatPoints = formatPoints;
  window.formatPeso = formatPeso;
  window.formatMetricFull = formatMetricFull;
  window.formatByMetric = formatByMetric;
  window.makeRandomForMetric = makeRandomForMetric;
}



