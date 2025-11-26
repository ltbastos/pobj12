// BEGIN core/dates.js
/* =========================================================
   POBJ • Funções de Data
   ========================================================= */

// Aqui eu gero a data de hoje em ISO (aaaa-mm-dd).
function __pad2(n){ return String(n).padStart(2, "0"); }
function fmtISO(date){
  if (!(date instanceof Date) || Number.isNaN(date?.getTime?.())) return "";
  return `${date.getFullYear()}-${__pad2(date.getMonth() + 1)}-${__pad2(date.getDate())}`;
}
function todayISO(){
  return fmtISO(new Date());
}
function resolveCalendarToday(){
  const cap = (typeof window !== "undefined" && typeof window.calendarCapISO === "string")
    ? window.calendarCapISO.trim()
    : "";
  const isoPattern = /^\d{4}-\d{2}-\d{2}$/;
  if (cap && isoPattern.test(cap)) return cap;

  const calendarioArray = typeof DIM_CALENDARIO !== "undefined" ? DIM_CALENDARIO : [];
  if (Array.isArray(calendarioArray) && calendarioArray.length) {
    const ordered = calendarioArray
      .map(entry => (typeof entry?.data === "string" ? entry.data.slice(0, 10) : ""))
      .filter(Boolean)
      .sort();
    const last = ordered[ordered.length - 1];
    if (last && isoPattern.test(last)) return last;
  }

  return todayISO();
}
function getCurrentMonthPeriod(){
  // No primeiro carregamento, sempre usa a data de hoje (não a última data do calendário)
  const end = todayISO();
  const start = `${end.slice(0, 7)}-01`;
  return { from: start, to: end };
}
function getDefaultPeriodRange(){
  const { from, to } = getCurrentMonthPeriod();
  return { start: from, end: to };
}
// Aqui eu descubro os limites (início e fim) do mês referente a uma data ISO qualquer.
function getMonthBoundsForISO(baseISO){
  const fallbackToday = todayISO();
  const iso = baseISO || fallbackToday;
  const ref = dateUTCFromISO(iso);
  if (!(ref instanceof Date) || Number.isNaN(ref?.getTime?.())) {
    const todayRef = dateUTCFromISO(fallbackToday);
    const startFallback = `${todayRef.getUTCFullYear()}-${String(todayRef.getUTCMonth()+1).padStart(2,"0")}-01`;
    const endFallbackDate = new Date(Date.UTC(todayRef.getUTCFullYear(), todayRef.getUTCMonth()+1, 0));
    return { start:startFallback, end: isoFromUTCDate(endFallbackDate) };
  }
  const start = `${ref.getUTCFullYear()}-${String(ref.getUTCMonth()+1).padStart(2,"0")}-01`;
  const endDate = new Date(Date.UTC(ref.getUTCFullYear(), ref.getUTCMonth()+1, 0));
  const end = isoFromUTCDate(endDate);
  return { start, end };
}
// Aqui eu calculo um panorama rápido de dias úteis do mês corrente usando o calendário completo.
function getCurrentMonthBusinessSnapshot(){
  const today = todayISO();
  const { start: monthStart, end: monthEnd } = getMonthBoundsForISO(today);
  const monthKey = today.slice(0,7);
  let total = 0;
  let elapsed = 0;
  const calendarioArray = typeof DIM_CALENDARIO !== "undefined" ? DIM_CALENDARIO : [];
  if (Array.isArray(calendarioArray) && calendarioArray.length) {
    const entries = calendarioArray.filter(entry => {
      const data = entry.data || entry.dt || "";
      return typeof data === "string" && data.startsWith(monthKey);
    });
    const businessEntries = entries.filter(entry => {
      const utilFlag = entry.ehDiaUtil ?? entry.util ?? entry.diaUtil ?? "";
      const value = typeof utilFlag === "string" ? utilFlag.trim() : utilFlag;
      if (value === true || value === 1 || value === "1") return true;
      if (typeof value === "string" && value.toLowerCase() === "sim") return true;
      return false;
    });
    total = businessEntries.length;
    const todayFiltered = businessEntries.filter(entry => (entry.data || entry.dt || "") <= today);
    elapsed = todayFiltered.length;
  }
  if (!total) {
    total = businessDaysBetweenInclusive(monthStart, monthEnd);
    const cappedToday = today < monthStart ? monthStart : (today > monthEnd ? monthEnd : today);
    elapsed = businessDaysBetweenInclusive(monthStart, cappedToday);
  }
  const remaining = Math.max(0, total - elapsed);
  return { total, elapsed, remaining, monthStart, monthEnd };
}
// Aqui eu descubro rapidamente quantos meses devo voltar em cada visão acumulada.
function getAccumulatedViewMonths(view){
  const match = ACCUMULATED_VIEW_OPTIONS.find(opt => opt.value === view);
  return match ? match.monthsBack : 0;
}
// Aqui eu calculo o período inicial/final com base na visão acumulada escolhida.
function computeAccumulatedPeriod(view = state.accumulatedView || "mensal", referenceEndISO = ""){
  const today = todayISO();
  const datasetMax = AVAILABLE_DATE_MAX || "";
  const cap = datasetMax || today;
  let endISO = referenceEndISO || state.period?.end || cap;
  if (!endISO) endISO = cap;
  if (datasetMax && endISO > datasetMax) {
    endISO = datasetMax;
  } else if (!datasetMax && endISO > today) {
    endISO = today;
  }
  let endDate = dateUTCFromISO(endISO);
  if (!(endDate instanceof Date) || Number.isNaN(endDate?.getTime?.())) {
    endDate = dateUTCFromISO(cap);
    endISO = isoFromUTCDate(endDate);
  }
  const monthsBack = getAccumulatedViewMonths(view);
  let startDate;
  if (view === "anual") {
    startDate = new Date(Date.UTC(endDate.getUTCFullYear(), 0, 1));
  } else {
    startDate = new Date(Date.UTC(endDate.getUTCFullYear(), endDate.getUTCMonth() - monthsBack, 1));
  }
  const startISO = isoFromUTCDate(startDate);
  const endIsoFinal = isoFromUTCDate(endDate);
  return { start: startISO, end: endIsoFinal };
}
// Aqui eu aplico a visão acumulada escolhida direto no estado e atualizo o rótulo do período.
function syncPeriodFromAccumulatedView(view = state.accumulatedView || "mensal", referenceEndISO = ""){
  let period;
  if (view === "mensal") {
    const ref = referenceEndISO || state.period?.end || todayISO();
    const safeRef = ref || todayISO();
    period = { start: `${safeRef.slice(0, 7)}-01`, end: safeRef };
  } else {
    period = computeAccumulatedPeriod(view, referenceEndISO);
  }
  state.period.start = period.start;
  state.period.end = period.end;
  updatePeriodLabels();
  return period;
}
// Aqui eu atualizo os textos "De xx/xx/xxxx até yy/yy/yyyy" sempre que o período mudar.
function updatePeriodLabels(){
  const startEl = document.getElementById("lbl-periodo-inicio");
  const endEl = document.getElementById("lbl-periodo-fim");
  if (startEl) startEl.textContent = formatBRDate(state.period.start);
  if (endEl) endEl.textContent = formatBRDate(state.period.end);
}
// Aqui eu calculo o período que alimenta os gráficos mensais da visão executiva.
function getExecutiveMonthlyPeriod(){
  const today = todayISO();
  const datasetMax = AVAILABLE_DATE_MAX || "";
  const datasetYear = datasetMax ? datasetMax.slice(0,4) : "";
  const currentYear = today.slice(0,4);
  const useCurrentYear = !datasetYear || datasetYear === currentYear;
  let end = useCurrentYear ? today : (datasetMax || today);
  if (!end) end = today;
  let year = useCurrentYear ? currentYear : (datasetYear || currentYear);
  let start = `${year}-01-01`;
  if (end && start && start > end) {
    start = `${end.slice(0, 7)}-01`;
  }
  return { start, end };
}
// Aqui eu formato uma data ISO para o padrão BR.
function formatBRDate(iso){ if(!iso) return ""; const [y,m,day]=iso.split("-"); return `${day}/${m}/${y}`; }
// Aqui eu converto uma data ISO para um Date em UTC.
function dateUTCFromISO(iso){ const [y,m,d]=iso.split("-").map(Number); return new Date(Date.UTC(y,m-1,d)); }
// Aqui eu faço o caminho inverso: Date UTC para string ISO.
function isoFromUTCDate(d){ return `${d.getUTCFullYear()}-${String(d.getUTCMonth()+1).padStart(2,"0")}-${String(d.getUTCDate()).padStart(2,"0")}`; }
// Aqui eu conto quantos dias úteis existem entre duas datas (inclusive).
function businessDaysBetweenInclusive(startISO,endISO){
  if(!startISO || !endISO) return 0;
  let s = dateUTCFromISO(startISO), e = dateUTCFromISO(endISO);
  if(s > e) return 0;
  let cnt=0;
  for(let d=new Date(s); d<=e; d.setUTCDate(d.getUTCDate()+1)){
    const wd = d.getUTCDay(); if(wd!==0 && wd!==6) cnt++;
  }
  return cnt;
}
// Aqui eu calculo quantos dias úteis já se passaram dentro do intervalo até hoje (incluindo hoje).
function businessDaysElapsedUntilToday(startISO,endISO){
  if(!startISO || !endISO) return 0;
  const todayISOValue = todayISO();
  let start = dateUTCFromISO(startISO), end = dateUTCFromISO(endISO), today = dateUTCFromISO(todayISOValue);
  if(!start || !end || !today) return 0;
  if(today < start) return 0;
  if(today > end) today = end;
  return businessDaysBetweenInclusive(startISO, isoFromUTCDate(today));
}

// Funções auxiliares de mês
function resolverMesAbreviado(mes, mesNome){
  const mesLimpo = limparTexto(mes);
  const numero = Number.parseInt(mesLimpo, 10);
  if (Number.isFinite(numero) && numero >= 1 && numero <= 12) {
    return MONTH_ABBREVIATIONS_PT_BR[numero - 1] || "";
  }

  const candidatos = [mesNome, mesLimpo];
  for (const candidato of candidatos){
    const simplificado = simplificarTexto(candidato);
    if (!simplificado) continue;
    for (let idx = 0; idx < MONTH_ABBREVIATION_ALIASES.length; idx += 1){
      const aliases = MONTH_ABBREVIATION_ALIASES[idx];
      if (aliases.some(alias => alias === simplificado)) {
        return MONTH_ABBREVIATIONS_PT_BR[idx];
      }
    }
  }
  return "";
}

function construirEtiquetaMesAno(ano, mes, mesNome){
  const anoLimpo = limparTexto(ano);
  const mesAbreviado = resolverMesAbreviado(mes, mesNome);
  if (mesAbreviado && anoLimpo) return `${mesAbreviado}-${anoLimpo}`;
  if (mesAbreviado) return mesAbreviado;
  if (anoLimpo && limparTexto(mes)) return `${limparTexto(mes)}-${anoLimpo}`;
  if (anoLimpo && limparTexto(mesNome)) return `${limparTexto(mesNome)}-${anoLimpo}`;
  return "";
}

// Expor no window para compatibilidade
if (typeof window !== "undefined") {
  window.todayISO = todayISO;
  window.resolveCalendarToday = resolveCalendarToday;
  window.getCurrentMonthPeriod = getCurrentMonthPeriod;
  window.getDefaultPeriodRange = getDefaultPeriodRange;
  window.getMonthBoundsForISO = getMonthBoundsForISO;
  window.getCurrentMonthBusinessSnapshot = getCurrentMonthBusinessSnapshot;
  window.getAccumulatedViewMonths = getAccumulatedViewMonths;
  window.computeAccumulatedPeriod = computeAccumulatedPeriod;
  window.syncPeriodFromAccumulatedView = syncPeriodFromAccumulatedView;
  window.updatePeriodLabels = updatePeriodLabels;
  window.getExecutiveMonthlyPeriod = getExecutiveMonthlyPeriod;
  window.formatBRDate = formatBRDate;
  window.dateUTCFromISO = dateUTCFromISO;
  window.isoFromUTCDate = isoFromUTCDate;
  window.businessDaysBetweenInclusive = businessDaysBetweenInclusive;
  window.businessDaysElapsedUntilToday = businessDaysElapsedUntilToday;
  window.resolverMesAbreviado = resolverMesAbreviado;
  window.construirEtiquetaMesAno = construirEtiquetaMesAno;
}



