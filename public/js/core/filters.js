// BEGIN core/filters.js
/* =========================================================
   POBJ • Filtros  —  Controle de filtros e hierarquia de seleção
   ========================================================= */

/* ===== Constantes de Configuração de Filtros ===== */
const FILTER_LEVEL_CONFIG = [
  { key: "diretoria", selector: "#f-diretoria", levelKey: "diretoria" },
  { key: "gerencia", selector: "#f-gerencia", levelKey: "gerencia" },
  { key: "agencia", selector: "#f-agencia", levelKey: "agencia" },
  { key: "ggestao", selector: "#f-gerente-gestao", levelKey: "gGestao" },
  { key: "gerente", selector: "#f-gerente", levelKey: "gerente" },
];

const HIERARCHY_FIELDS_DEF = [
  { key: "segmento",  select: "#f-segmento",  defaultValue: "", defaultLabel: "",  idKey: "segmentoId",    labelKey: "segmentoNome",    fallback: () => typeof SEGMENTOS_DATA !== "undefined" ? SEGMENTOS_DATA : [] },
  { key: "diretoria", select: "#f-diretoria", defaultValue: "", defaultLabel: "", idKey: "diretoriaId",   labelKey: "diretoriaNome",   fallback: () => typeof RANKING_DIRECTORIAS !== "undefined" ? RANKING_DIRECTORIAS : [] },
  { key: "gerencia",  select: "#f-gerencia",  defaultValue: "", defaultLabel: "", idKey: "regionalId",    labelKey: "regionalNome",    fallback: () => typeof RANKING_GERENCIAS !== "undefined" ? RANKING_GERENCIAS : [] },
  { key: "agencia",   select: "#f-agencia",   defaultValue: "Todas", defaultLabel: "Todas", idKey: "agenciaId",     labelKey: "agenciaNome",     fallback: () => typeof RANKING_AGENCIAS !== "undefined" ? RANKING_AGENCIAS : [] },
  { key: "ggestao",   select: "#f-gerente-gestao",   defaultValue: "Todos", defaultLabel: "Todos", idKey: "gerenteGestaoId", labelKey: "gerenteGestaoNome", fallback: () => typeof GERENTES_GESTAO !== "undefined" ? GERENTES_GESTAO : [] },
  { key: "gerente",   select: "#f-gerente",   defaultValue: "Todos", defaultLabel: "Todos", idKey: "gerenteId",      labelKey: "gerenteNome",      fallback: () => typeof RANKING_GERENTES !== "undefined" ? RANKING_GERENTES : [] }
];

const HIERARCHY_FIELD_MAP = new Map(HIERARCHY_FIELDS_DEF.map(field => [field.key, field]));

/* ===== Funções Auxiliares de Filtros ===== */
function buildHierarchyLabel(id, nome){
  const codigo = limparTexto(id);
  const label = limparTexto(nome);
  if (codigo && label){
    if (label.startsWith(`${codigo} `) || label.startsWith(`${codigo}-`)) return label;
    if (label.includes(`${codigo} -`) || label.includes(`${codigo}-`)) return label;
    if (/^\d/.test(codigo)) return `${codigo} - ${label}`;
  }
  if (label) return label;
  if (codigo) return codigo;
  return "";
}

function matchesSelection(filterValue, ...candidates){
  const esperado = limparTexto(filterValue);
  if (!esperado) return false;
  const esperadoSimple = simplificarTexto(esperado);
  const lista = [];
  candidates.forEach(item => {
    if (Array.isArray(item)) lista.push(...item);
    else lista.push(item);
  });
  return lista.some(candidate => {
    const valor = limparTexto(candidate);
    if (!valor) return false;
    if (valor === esperado) return true;
    return simplificarTexto(valor) === esperadoSimple;
  });
}

function matchesSegmentFilter(filterValue, ...candidates){
  const esperado = limparTexto(filterValue);
  if (!esperado) return false;
  if (matchesSelection(filterValue, ...candidates)) return true;
  const esperadoScenario = typeof getSegmentScenarioFromValue === "function" ? getSegmentScenarioFromValue(esperado) : null;
  if (!esperadoScenario) return false;
  const lista = [];
  candidates.forEach(item => {
    if (Array.isArray(item)) lista.push(...item);
    else lista.push(item);
  });
  return lista.some(candidate => {
    const valor = limparTexto(candidate);
    if (!valor) return false;
    const scenario = typeof getSegmentScenarioFromValue === "function" ? getSegmentScenarioFromValue(valor) : null;
    return scenario === esperadoScenario;
  });
}

function resolveSelectLabel(selector, value){
  if (!selector || value == null || selecaoPadrao(value)) return "";
  const select = document.querySelector(selector);
  if (!select) return "";
  const text = limparTexto(value);
  if (!text) return "";
  const simple = simplificarTexto(text);
  const options = Array.from(select.options || []);
  for (const option of options) {
    const optionValue = option?.value ?? "";
    const optionText = option?.textContent?.trim() || "";
    if (matchesSelection(text, optionValue, optionText)) return optionText;
    if (simple && simplificarTexto(optionText) === simple) return optionText;
  }
  return "";
}

function buildLineageFromFilters(filters = {}){
  const lineage = [];
  FILTER_LEVEL_CONFIG.forEach(({ key, selector, levelKey }) => {
    const value = filters?.[key];
    if (!value || selecaoPadrao(value)) return;
    const label = resolveSelectLabel(selector, value) || limparTexto(value);
    lineage.push({ levelKey, value, label });
  });
  return lineage;
}

/* ===== Funções de Hierarquia ===== */
function hierarchyDefaultSelection(){
  const defaults = {};
  HIERARCHY_FIELDS_DEF.forEach(field => { defaults[field.key] = field.defaultValue; });
  return defaults;
}

function getHierarchySelectionFromDOM(){
  const values = hierarchyDefaultSelection();
  HIERARCHY_FIELDS_DEF.forEach(field => {
    const select = $(field.select);
    if (!select) return;
    const value = limparTexto(select.value);
    values[field.key] = value || field.defaultValue;
  });
  return values;
}

function resolveFallbackMeta(fieldKey, item = {}) {
  const base = item?.id ?? item?.value ?? item?.codigo ?? item?.nome ?? item?.name ?? item?.label;
  switch (fieldKey) {
    case "segmento":
      return typeof findSegmentoMeta === "function" ? (findSegmentoMeta(base) || item) : item;
    case "diretoria":
      return typeof findDiretoriaMeta === "function" ? (findDiretoriaMeta(base) || item) : item;
    case "gerencia":
      return typeof findGerenciaMeta === "function" ? (findGerenciaMeta(base) || item) : item;
    case "agencia":
      return typeof findAgenciaMeta === "function" ? (findAgenciaMeta(base) || item) : item;
    case "ggestao":
      return typeof findGerenteGestaoMeta === "function" ? (findGerenteGestaoMeta(base) || item) : item;
    case "gerente":
      return typeof findGerenteMeta === "function" ? (findGerenteMeta(base) || item) : item;
    default:
      return item;
  }
}

function buildHierarchyFallbackRow(fieldKey, item) {
  const def = HIERARCHY_FIELD_MAP.get(fieldKey);
  if (!def) return null;
  const meta = resolveFallbackMeta(fieldKey, item) || {};
  const full = {};
  if (item && typeof item === "object") {
    Object.entries(item).forEach(([key, value]) => {
      if (value == null) return;
      full[key] = value;
    });
  }
  if (meta && typeof meta === "object") {
    Object.entries(meta).forEach(([key, value]) => {
      if (value == null) return;
      if (typeof value === "string" && !value.trim()) return;
      full[key] = value;
    });
  }

  const row = {
    segmentoId: full.segmentoId || full.segmento || "",
    segmentoNome: full.segmentoNome || full.segmento || "",
    segmentoLabel: full.segmentoLabel || full.label || full.rotulo || full.segmentoNome || full.segmento || "",
    segmentoNomeOriginal: full.segmentoNomeOriginal || full.segmentoNome || full.segmento || "",
    segmentoOrder: Number.isFinite(full.segmentoOrder) ? Number(full.segmentoOrder) : undefined,
    segmentoHidden: full.segmentoHidden,
    segmentoAliases: Array.isArray(full.segmentoAliases) ? [...full.segmentoAliases] : undefined,
    segmentoSlug: full.segmentoSlug,
    segmentoScenario: full.segmentoScenario,
    diretoriaId: full.diretoriaId || full.diretoria || "",
    diretoriaNome: full.diretoriaNome || full.diretoria || "",
    diretoriaLabel: full.diretoriaLabel || full.label || full.rotulo || full.diretoriaNome || full.diretoria || "",
    diretoriaNomeOriginal: full.diretoriaNomeOriginal || full.diretoriaNome || full.diretoria || "",
    regionalId: full.regionalId || full.regional || full.gerenciaId || full.gerencia || "",
    regionalNome: full.regionalNome || full.gerenciaNome || full.regional || "",
    regionalLabel: full.regionalLabel || full.label || full.rotulo || full.regionalNome || full.gerenciaNome || full.regional || "",
    regionalNomeOriginal: full.regionalNomeOriginal || full.regionalNome || full.regional || full.gerenciaNome || "",
    agenciaId: full.agenciaId || full.agencia || full.agenciaCodigo || "",
    agenciaNome: full.agenciaNome || full.agencia || "",
    agenciaLabel: full.agenciaLabel || full.label || full.rotulo || full.agenciaNome || full.agencia || "",
    agenciaCodigo: full.agenciaCodigo || full.codigo || "",
    gerenteGestaoId: full.gerenteGestaoId || full.gerenteGestao || "",
    gerenteGestaoNome: full.gerenteGestaoNome || full.nomeGerenteGestao || "",
    gerenteGestaoLabel: full.gerenteGestaoLabel || full.label || full.rotulo || full.gerenteGestaoNome || full.gerenteGestao || "",
    gerenteId: full.gerenteId || full.gerente || "",
    gerenteNome: full.gerenteNome || full.nomeGerente || "",
    gerenteLabel: full.gerenteLabel || full.label || full.rotulo || full.gerenteNome || full.gerente || "",
  };

  switch (fieldKey) {
    case "segmento":
      if (full.id) row.segmentoId = full.id;
      if (full.nome) row.segmentoNome = full.nome;
      if (full.label) row.segmentoLabel = full.label;
      break;
    case "diretoria":
      if (full.id) row.diretoriaId = full.id;
      if (full.nome) row.diretoriaNome = full.nome;
      if (full.label) row.diretoriaLabel = full.label;
      break;
    case "gerencia":
      if (full.id) row.regionalId = full.id;
      if (full.nome) row.regionalNome = full.nome;
      if (full.label) row.regionalLabel = full.label;
      break;
    case "agencia":
      if (full.id) row.agenciaId = full.id;
      if (full.nome) row.agenciaNome = full.nome;
      if (full.label) row.agenciaLabel = full.label;
      if (!row.agenciaCodigo) row.agenciaCodigo = row.agenciaId;
      break;
    case "ggestao":
      if (full.id) row.gerenteGestaoId = full.id;
      if (full.nome) row.gerenteGestaoNome = full.nome;
      if (full.label) row.gerenteGestaoLabel = full.label;
      if (!row.agenciaId && full.agencia) row.agenciaId = full.agencia;
      if (!row.agenciaNome && full.agenciaNome) row.agenciaNome = full.agenciaNome;
      break;
    case "gerente":
      if (full.id) row.gerenteId = full.id;
      if (full.nome) row.gerenteNome = full.nome;
      if (full.label) row.gerenteLabel = full.label;
      if (!row.gerenteGestaoId && full.gerenteGestao) row.gerenteGestaoId = full.gerenteGestao;
      if (!row.agenciaId && full.agencia) row.agenciaId = full.agencia;
      if (!row.agenciaNome && full.agenciaNome) row.agenciaNome = full.agenciaNome;
      break;
    default:
      break;
  }

  if (typeof applyHierarchyFallbackToRow === "function") {
    applyHierarchyFallbackToRow(row);
  }

  const fallbackValue = row[def.idKey]
    || full[def.idKey]
    || full.id
    || full.value
    || full.codigo
    || "";
  const fallbackLabel = row[def.labelKey]
    || full[def.labelKey]
    || full.nome
    || full.name
    || full.label
    || fallbackValue;

  if (!row[def.idKey]) row[def.idKey] = fallbackValue;
  if (!row[def.labelKey]) row[def.labelKey] = fallbackLabel;

  const aliasSet = new Set();
  const addAlias = (val) => {
    if (val == null) return;
    if (Array.isArray(val)) { val.forEach(addAlias); return; }
    const clean = limparTexto(val);
    if (clean) aliasSet.add(clean);
  };
  addAlias(full.aliases);
  addAlias(full.alias);
  addAlias(full.apelidos);
  addAlias(full.slug);
  addAlias(full.codigo);
  addAlias(full.value);
  addAlias(full.id);
  if (item && typeof item === "object" && item !== full) addAlias(item.aliases);
  if (meta && typeof meta === "object" && meta !== full) addAlias(meta.aliases);

  const cleanValue = limparTexto(row[def.idKey]);
  const cleanLabel = limparTexto(row[def.labelKey]);
  const aliases = Array.from(aliasSet).filter(alias => alias && alias !== cleanValue && alias !== cleanLabel);
  if (aliases.length) {
    row[`${def.labelKey}Aliases`] = aliases;
    row[`${fieldKey}Aliases`] = aliases;
    if (!Array.isArray(row.aliases) || !row.aliases.length) row.aliases = aliases;
  }

  if (full.hidden != null) row.hidden = Boolean(full.hidden);
  if (Number.isFinite(full.order)) row.order = Number(full.order);
  if (full.slug) row.slug = full.slug;
  if (full.scenario) row.scenario = full.scenario;

  return row;
}

function buildHierarchyFallbackRows(fieldKey) {
  const def = HIERARCHY_FIELD_MAP.get(fieldKey);
  if (!def || typeof def.fallback !== "function") return [];
  const fallback = def.fallback();
  if (!Array.isArray(fallback) || !fallback.length) return [];
  const rows = [];
  fallback.forEach(item => {
    const row = buildHierarchyFallbackRow(fieldKey, item);
    if (row) rows.push(row);
  });
  return rows;
}

function buildHierarchyRowsFromEstrutura(){
  const rows = [];
  
  // Verifica se temos dados de estrutura disponíveis
  if (typeof DIM_SEGMENTOS_LOOKUP === "undefined" || typeof DIM_DIRETORIAS_LOOKUP === "undefined" || 
      typeof DIM_REGIONAIS_LOOKUP === "undefined" || typeof DIM_AGENCIAS_LOOKUP === "undefined" || 
      typeof DIM_GGESTAO_LOOKUP === "undefined" || typeof DIM_GERENTES_LOOKUP === "undefined") {
    return rows;
  }

  // Cria mapas para lookup rápido
  const segmentosMap = new Map();
  DIM_SEGMENTOS_LOOKUP.forEach((seg, id) => {
    segmentosMap.set(String(seg.id || id), seg);
  });

  const diretoriasMap = new Map();
  DIM_DIRETORIAS_LOOKUP.forEach((dir, id) => {
    diretoriasMap.set(String(dir.id || id), dir);
  });

  const regionaisMap = new Map();
  DIM_REGIONAIS_LOOKUP.forEach((reg, id) => {
    regionaisMap.set(String(reg.id || id), reg);
  });

  const agenciasMap = new Map();
  DIM_AGENCIAS_LOOKUP.forEach((ag, id) => {
    agenciasMap.set(String(ag.id || id), ag);
  });

  const gerentesGestaoMap = new Map();
  DIM_GGESTAO_LOOKUP.forEach((gg, id) => {
    gerentesGestaoMap.set(String(gg.id || id), gg);
  });

  const gerentesMap = new Map();
  DIM_GERENTES_LOOKUP.forEach((ger, id) => {
    gerentesMap.set(String(ger.id || id), ger);
  });

  // Constrói rows baseado nas relações hierárquicas
  // Começa pelos gerentes e sobe a hierarquia
  gerentesMap.forEach((gerente, gerenteId) => {
    const gerenteIdStr = String(gerente.id || gerenteId);
    const agenciaIdStr = String(gerente.id_agencia || gerente.agencia || "");
    const gestorIdStr = String(gerente.id_gestor || gerente.gerenteGestao || "");
    
    if (!agenciaIdStr) return;

    const agencia = agenciasMap.get(agenciaIdStr);
    if (!agencia) return;

    const regionalIdStr = String(agencia.id_regional || agencia.regional_id || agencia.gerencia_regional_id || "");
    const regional = regionalIdStr ? regionaisMap.get(regionalIdStr) : null;

    const diretoriaIdStr = regional 
      ? String(regional.id_diretoria || regional.diretoria_id || "")
      : String(agencia.id_diretoria || agencia.diretoria_id || "");
    const diretoria = diretoriaIdStr ? diretoriasMap.get(diretoriaIdStr) : null;

    const segmentoIdStr = diretoria
      ? String(diretoria.id_segmento || diretoria.segmento_id || "")
      : (regional ? String(regional.id_segmento || regional.segmento_id || "") : "");
    const segmento = segmentoIdStr ? segmentosMap.get(segmentoIdStr) : null;

    const gerenteGestao = gestorIdStr ? gerentesGestaoMap.get(gestorIdStr) : null;

    // Cria uma row para cada combinação gerente-agência
    const agenciaNomeRaw = agencia ? (agencia.label || agencia.nome || agenciaIdStr) : agenciaIdStr;
    const agenciaNome = agenciaIdStr && agenciaNomeRaw && agenciaNomeRaw !== agenciaIdStr 
      ? buildHierarchyLabel(agenciaIdStr, agenciaNomeRaw) || agenciaNomeRaw 
      : agenciaNomeRaw;
    
    const gerenteGestaoNomeRaw = gerenteGestao ? (gerenteGestao.label || gerenteGestao.nome || String(gerenteGestao.id || "")) : "";
    const gerenteGestaoIdStr = gerenteGestao ? String(gerenteGestao.id || "") : "";
    const gerenteGestaoNome = gerenteGestaoIdStr && gerenteGestaoNomeRaw && gerenteGestaoNomeRaw !== gerenteGestaoIdStr
      ? buildHierarchyLabel(gerenteGestaoIdStr, gerenteGestaoNomeRaw) || gerenteGestaoNomeRaw
      : gerenteGestaoNomeRaw;
    
    const gerenteNomeRaw = gerente ? (gerente.label || gerente.nome || gerenteIdStr) : gerenteIdStr;
    const gerenteNome = gerenteIdStr && gerenteNomeRaw && gerenteNomeRaw !== gerenteIdStr
      ? buildHierarchyLabel(gerenteIdStr, gerenteNomeRaw) || gerenteNomeRaw
      : gerenteNomeRaw;
    
    const segmentoIdStrFinal = segmento ? String(segmento.id || "") : segmentoIdStr || "";
    const segmentoNomeRaw = segmento ? (segmento.label || segmento.nome || segmentoIdStrFinal) : "";
    const segmentoNome = segmentoIdStrFinal && segmentoNomeRaw && segmentoNomeRaw !== segmentoIdStrFinal
      ? buildHierarchyLabel(segmentoIdStrFinal, segmentoNomeRaw) || segmentoNomeRaw
      : segmentoNomeRaw;
    
    const diretoriaIdStrFinal = diretoria ? String(diretoria.id || "") : diretoriaIdStr || "";
    const diretoriaNomeRaw = diretoria ? (diretoria.label || diretoria.nome || diretoriaIdStrFinal) : "";
    const diretoriaNome = diretoriaIdStrFinal && diretoriaNomeRaw && diretoriaNomeRaw !== diretoriaIdStrFinal
      ? buildHierarchyLabel(diretoriaIdStrFinal, diretoriaNomeRaw) || diretoriaNomeRaw
      : diretoriaNomeRaw;
    
    rows.push({
      segmentoId: segmentoIdStrFinal,
      segmentoNome: segmentoNome,
      diretoriaId: diretoriaIdStrFinal,
      diretoriaNome: diretoriaNome,
      regionalId: regional ? String(regional.id || "") : "",
      regionalNome: regional ? (regional.label || regional.nome || String(regional.id || "")) : "",
      agenciaId: agenciaIdStr,
      agenciaNome: agenciaNome,
      gerenteGestaoId: gerenteGestaoIdStr,
      gerenteGestaoNome: gerenteGestaoNome,
      gerenteId: gerenteIdStr,
      gerenteNome: gerenteNome,
    });
  });

  // Adiciona agências que não têm gerentes mas podem ter gerentes de gestão
  agenciasMap.forEach((agencia, agenciaId) => {
    const agenciaIdStr = String(agencia.id || agenciaId);
    
    // Verifica se já existe uma row para esta agência
    const exists = rows.some(row => row.agenciaId === agenciaIdStr);
    if (exists) return;

    const regionalIdStr = String(agencia.id_regional || agencia.regional_id || agencia.gerencia_regional_id || "");
    const regional = regionalIdStr ? regionaisMap.get(regionalIdStr) : null;

    const diretoriaIdStr = regional 
      ? String(regional.id_diretoria || regional.diretoria_id || "")
      : String(agencia.id_diretoria || agencia.diretoria_id || "");
    const diretoria = diretoriaIdStr ? diretoriasMap.get(diretoriaIdStr) : null;

    const segmentoIdStr = diretoria
      ? String(diretoria.id_segmento || diretoria.segmento_id || "")
      : (regional ? String(regional.id_segmento || regional.segmento_id || "") : "");
    const segmento = segmentoIdStr ? segmentosMap.get(segmentoIdStr) : null;

    // Busca gerente gestão vinculado a esta agência
    const gerenteGestaoIdStr = String(agencia.id_gerente_gestao || agencia.gerenteGestao || "");
    const gerenteGestao = gerenteGestaoIdStr ? gerentesGestaoMap.get(gerenteGestaoIdStr) : null;
    
    // Se não encontrou pelo campo da agência, busca pelos gerentes gestão que têm esta agência
    let gerenteGestaoFinal = gerenteGestao;
    if (!gerenteGestaoFinal) {
      gerentesGestaoMap.forEach((gg, ggId) => {
        const ggAgenciaIdStr = String(gg.id_agencia || gg.agencia || "");
        if (ggAgenciaIdStr === agenciaIdStr) {
          gerenteGestaoFinal = gg;
        }
      });
    }

    const agenciaNomeRaw2 = agencia ? (agencia.label || agencia.nome || agenciaIdStr) : agenciaIdStr;
    const agenciaNome2 = agenciaIdStr && agenciaNomeRaw2 && agenciaNomeRaw2 !== agenciaIdStr 
      ? buildHierarchyLabel(agenciaIdStr, agenciaNomeRaw2) || agenciaNomeRaw2 
      : agenciaNomeRaw2;
    
    const gerenteGestaoIdStr2 = gerenteGestaoFinal ? String(gerenteGestaoFinal.id || "") : "";
    const gerenteGestaoNomeRaw2 = gerenteGestaoFinal ? (gerenteGestaoFinal.label || gerenteGestaoFinal.nome || gerenteGestaoIdStr2) : "";
    const gerenteGestaoNome2 = gerenteGestaoIdStr2 && gerenteGestaoNomeRaw2 && gerenteGestaoNomeRaw2 !== gerenteGestaoIdStr2
      ? buildHierarchyLabel(gerenteGestaoIdStr2, gerenteGestaoNomeRaw2) || gerenteGestaoNomeRaw2
      : gerenteGestaoNomeRaw2;
    
    const segmentoIdStrFinal2 = segmento ? String(segmento.id || "") : segmentoIdStr || "";
    const segmentoNomeRaw2 = segmento ? (segmento.label || segmento.nome || segmentoIdStrFinal2) : "";
    const segmentoNome2 = segmentoIdStrFinal2 && segmentoNomeRaw2 && segmentoNomeRaw2 !== segmentoIdStrFinal2
      ? buildHierarchyLabel(segmentoIdStrFinal2, segmentoNomeRaw2) || segmentoNomeRaw2
      : segmentoNomeRaw2;
    
    const diretoriaIdStrFinal2 = diretoria ? String(diretoria.id || "") : diretoriaIdStr || "";
    const diretoriaNomeRaw2 = diretoria ? (diretoria.label || diretoria.nome || diretoriaIdStrFinal2) : "";
    const diretoriaNome2 = diretoriaIdStrFinal2 && diretoriaNomeRaw2 && diretoriaNomeRaw2 !== diretoriaIdStrFinal2
      ? buildHierarchyLabel(diretoriaIdStrFinal2, diretoriaNomeRaw2) || diretoriaNomeRaw2
      : diretoriaNomeRaw2;
    
    rows.push({
      segmentoId: segmentoIdStrFinal2,
      segmentoNome: segmentoNome2,
      diretoriaId: diretoriaIdStrFinal2,
      diretoriaNome: diretoriaNome2,
      regionalId: regional ? String(regional.id || "") : "",
      regionalNome: regional ? (regional.label || regional.nome || String(regional.id || "")) : "",
      agenciaId: agenciaIdStr,
      agenciaNome: agenciaNome2,
      gerenteGestaoId: gerenteGestaoIdStr2,
      gerenteGestaoNome: gerenteGestaoNome2,
      gerenteId: "",
      gerenteNome: "",
    });
  });

  // Adiciona gerentes de gestão que não estão vinculados a agências ainda processadas
  gerentesGestaoMap.forEach((gg, ggId) => {
    const ggIdStr = String(gg.id || ggId);
    const agenciaIdStr = String(gg.id_agencia || gg.agencia || "");
    
    if (!agenciaIdStr) return;
    
    // Verifica se já existe uma row para esta combinação agência-gerente gestão
    const exists = rows.some(row => 
      row.agenciaId === agenciaIdStr && row.gerenteGestaoId === ggIdStr
    );
    if (exists) return;

    const agencia = agenciasMap.get(agenciaIdStr);
    if (!agencia) return;

    const regionalIdStr = String(agencia.id_regional || agencia.regional_id || agencia.gerencia_regional_id || "");
    const regional = regionalIdStr ? regionaisMap.get(regionalIdStr) : null;

    const diretoriaIdStr = regional 
      ? String(regional.id_diretoria || regional.diretoria_id || "")
      : String(agencia.id_diretoria || agencia.diretoria_id || "");
    const diretoria = diretoriaIdStr ? diretoriasMap.get(diretoriaIdStr) : null;

    const segmentoIdStr = diretoria
      ? String(diretoria.id_segmento || diretoria.segmento_id || "")
      : (regional ? String(regional.id_segmento || regional.segmento_id || "") : "");
    const segmento = segmentoIdStr ? segmentosMap.get(segmentoIdStr) : null;

    const agenciaNomeRaw3 = agencia ? (agencia.label || agencia.nome || agenciaIdStr) : agenciaIdStr;
    const agenciaNome3 = agenciaIdStr && agenciaNomeRaw3 && agenciaNomeRaw3 !== agenciaIdStr 
      ? buildHierarchyLabel(agenciaIdStr, agenciaNomeRaw3) || agenciaNomeRaw3 
      : agenciaNomeRaw3;
    
    const gerenteGestaoNomeRaw3 = gg ? (gg.label || gg.nome || ggIdStr) : ggIdStr;
    const gerenteGestaoNome3 = ggIdStr && gerenteGestaoNomeRaw3 && gerenteGestaoNomeRaw3 !== ggIdStr
      ? buildHierarchyLabel(ggIdStr, gerenteGestaoNomeRaw3) || gerenteGestaoNomeRaw3
      : gerenteGestaoNomeRaw3;
    
    const segmentoIdStrFinal3 = segmento ? String(segmento.id || "") : segmentoIdStr || "";
    const segmentoNomeRaw3 = segmento ? (segmento.label || segmento.nome || segmentoIdStrFinal3) : "";
    const segmentoNome3 = segmentoIdStrFinal3 && segmentoNomeRaw3 && segmentoNomeRaw3 !== segmentoIdStrFinal3
      ? buildHierarchyLabel(segmentoIdStrFinal3, segmentoNomeRaw3) || segmentoNomeRaw3
      : segmentoNomeRaw3;
    
    const diretoriaIdStrFinal3 = diretoria ? String(diretoria.id || "") : diretoriaIdStr || "";
    const diretoriaNomeRaw3 = diretoria ? (diretoria.label || diretoria.nome || diretoriaIdStrFinal3) : "";
    const diretoriaNome3 = diretoriaIdStrFinal3 && diretoriaNomeRaw3 && diretoriaNomeRaw3 !== diretoriaIdStrFinal3
      ? buildHierarchyLabel(diretoriaIdStrFinal3, diretoriaNomeRaw3) || diretoriaNomeRaw3
      : diretoriaNomeRaw3;
    
    rows.push({
      segmentoId: segmentoIdStrFinal3,
      segmentoNome: segmentoNome3,
      diretoriaId: diretoriaIdStrFinal3,
      diretoriaNome: diretoriaNome3,
      regionalId: regional ? String(regional.id || "") : "",
      regionalNome: regional ? (regional.label || regional.nome || String(regional.id || "")) : "",
      agenciaId: agenciaIdStr,
      agenciaNome: agenciaNome3,
      gerenteGestaoId: ggIdStr,
      gerenteGestaoNome: gerenteGestaoNome3,
      gerenteId: "",
      gerenteNome: "",
    });
  });

  return rows;
}

function getHierarchyRows(){
  // Sempre retorna dados atualizados do banco (sem cache)
  if (typeof MESU_FALLBACK_ROWS !== "undefined" && MESU_FALLBACK_ROWS.length) return MESU_FALLBACK_ROWS;

  // Tenta construir rows a partir dos dados de estrutura primeiro
  const rowsFromEstrutura = buildHierarchyRowsFromEstrutura();
  if (rowsFromEstrutura.length) {
    if (typeof MESU_FALLBACK_ROWS !== "undefined") {
      MESU_FALLBACK_ROWS = rowsFromEstrutura;
    }
    return rowsFromEstrutura;
  }

  const rows = [];
  const dirMap = typeof RANKING_DIRECTORIAS !== "undefined" ? new Map(RANKING_DIRECTORIAS.map(dir => [dir.id, dir])) : new Map();
  const gerMap = typeof RANKING_GERENCIAS !== "undefined" ? new Map(RANKING_GERENCIAS.map(ger => [ger.id, ger])) : new Map();
  const segMap = typeof SEGMENTOS_DATA !== "undefined" ? new Map(SEGMENTOS_DATA.map(seg => [seg.id || seg.nome, seg])) : new Map();

  if (typeof RANKING_AGENCIAS !== "undefined" && RANKING_AGENCIAS.length){
    RANKING_AGENCIAS.forEach(ag => {
      const gerMeta = gerMap.get(ag.gerencia) || {};
      const dirMeta = dirMap.get(gerMeta.diretoria) || {};
      const segKey = dirMeta.segmento || gerMeta.segmentoId || ag.segmento || ag.segmentoId || "";
      const segMeta = segMap.get(segKey) || {};
      const ggMeta = (typeof GERENTES_GESTAO !== "undefined" ? GERENTES_GESTAO.find(gg => gg.agencia === ag.id) : null) || {};
      const gerenteMeta = (typeof RANKING_GERENTES !== "undefined" ? RANKING_GERENTES.find(ge => ge.agencia === ag.id) : null) || {};
      rows.push({
        segmentoId: segMeta.id || segMeta.nome || segKey || "",
        segmentoNome: segMeta.nome || segMeta.id || segKey || "",
        segmentoOrder: Number.isFinite(segMeta.order) ? Number(segMeta.order) : undefined,
        segmentoHidden: segMeta.hidden,
        segmentoAliases: Array.isArray(segMeta.aliases) ? [...segMeta.aliases] : undefined,
        segmentoSlug: segMeta.slug,
        segmentoScenario: segMeta.scenario,
        diretoriaId: dirMeta.id || dirMeta.nome || "",
        diretoriaNome: dirMeta.nome || dirMeta.id || dirMeta.segmento || "",
        regionalId: gerMeta.id || gerMeta.nome || "",
        regionalNome: gerMeta.nome || gerMeta.id || "",
        agenciaId: ag.id,
        agenciaNome: ag.nome || ag.id,
        gerenteGestaoId: ggMeta.id || "",
        gerenteGestaoNome: ggMeta.nome || ggMeta.id || "",
        gerenteId: gerenteMeta.id || "",
        gerenteNome: gerenteMeta.nome || gerenteMeta.id || "",
      });
    });
  }

  if (!rows.length){
    rows.push({
      segmentoId: "",
      segmentoNome: "",
      diretoriaId: "",
      diretoriaNome: "",
      regionalId: "",
      regionalNome: "",
      agenciaId: "",
      agenciaNome: "",
      gerenteGestaoId: "",
      gerenteGestaoNome: "",
      gerenteId: "",
      gerenteNome: "",
    });
  }

  if (typeof MESU_FALLBACK_ROWS !== "undefined") {
    MESU_FALLBACK_ROWS = rows;
  }
  return rows;
}

function hierarchyRowMatchesField(row, field, value){
  if (!field) return true;
  const def = HIERARCHY_FIELD_MAP.get(field);
  if (!def) return true;
  if (selecaoPadrao(value) || value === def.defaultValue) return true;
  const rowId = limparTexto(row[def.idKey]);
  const rowLabel = limparTexto(row[def.labelKey]);
  if (field === "segmento") {
    return matchesSegmentFilter(value, rowId, rowLabel);
  }
  return matchesSelection(value, rowId, rowLabel);
}

function filterHierarchyRowsForField(targetField, selection, rows){
  return rows.filter(row => HIERARCHY_FIELDS_DEF.every(field => {
    if (field.key === targetField) return true;
    
    // Caso especial: se estamos construindo opções para "ggestao" e não há gerente selecionado,
    // não filtrar por ggestao (mostrar todos os gerentes de gestão disponíveis)
    if (targetField === "ggestao" && field.key === "ggestao") {
      const gerenteDef = HIERARCHY_FIELD_MAP.get("gerente");
      const gerenteValue = selection.gerente;
      const gerenteIsDefault = !gerenteValue || gerenteValue === gerenteDef?.defaultValue || selecaoPadrao(gerenteValue);
      if (gerenteIsDefault) return true; // Não filtrar por ggestao se não há gerente selecionado
    }
    
    return hierarchyRowMatchesField(row, field.key, selection[field.key]);
  }));
}

function buildHierarchyOptions(fieldKey, selection, rows){
  const def = HIERARCHY_FIELD_MAP.get(fieldKey);
  if (!def) return [];
  
  // Campos que sempre devem mostrar ID junto com o nome
  const fieldsWithIdRequired = new Set(["segmento", "diretoria", "agencia", "ggestao", "gerente"]);
  
  // Mapeamento de fieldKey para a chave correta em DIMENSION_FILTER_OPTIONS
  const dimensionKeyMap = {
    'ggestao': 'gerenteGestao',
    'gerente': 'gerente',
    'segmento': 'segmento',
    'diretoria': 'diretoria',
    'gerencia': 'gerencia',
    'agencia': 'agencia'
  };
  const dimensionKey = dimensionKeyMap[fieldKey] || fieldKey;
  
  // Se há opções de dimensão pré-definidas, usa-as diretamente após filtrar
  const hasDimensionPreset = typeof DIMENSION_FILTER_OPTIONS !== "undefined" && 
    Array.isArray(DIMENSION_FILTER_OPTIONS[dimensionKey]) &&
    DIMENSION_FILTER_OPTIONS[dimensionKey].length > 0;
  
  // Se há preset e não há rows, usa diretamente as opções de dimensão
  if (hasDimensionPreset && (!Array.isArray(rows) || !rows.length)) {
    const options = [];
    // Só adiciona opção padrão se defaultValue não for vazio
    if (def.defaultValue && def.defaultLabel) {
      options.push({ value: def.defaultValue, label: def.defaultLabel });
    }
    options.push(...DIMENSION_FILTER_OPTIONS[dimensionKey].map(opt => {
        const normalized = typeof normOpt === "function" ? normOpt(opt) : opt;
        let label = normalized.label || normalized.id;
        
        // Para segmento, diretoria, agência, gerente gestão e gerente, garantir que o label inclua o ID
        if (fieldsWithIdRequired.has(fieldKey) && normalized.id) {
          const optId = limparTexto(normalized.id);
          const optLabel = limparTexto(normalized.label);
          const optName = typeof extractNameFromLabel === "function" ? extractNameFromLabel(optLabel) : optLabel;
          
          // Se o label não contém o ID, adiciona usando buildHierarchyLabel
          if (optId && optName && optId !== optName && !optLabel.includes(optId)) {
            label = buildHierarchyLabel(optId, optName) || `${optId} - ${optName}`;
          } else if (optId && !optLabel.includes(optId)) {
            label = buildHierarchyLabel(optId, optLabel) || `${optId} - ${optLabel}`;
          }
        }
        
        return {
          value: normalized.id || normalized.label,
          label: label || normalized.id,
          aliases: Array.isArray(opt.aliases) ? opt.aliases : [],
        };
      })
    );
    return typeof uniqById === "function" ? uniqById(options) : options;
  }
  
  const fallbackRows = buildHierarchyFallbackRows(fieldKey);
  const sourceRows = Array.isArray(rows)
    ? (fallbackRows.length ? rows.concat(fallbackRows) : rows)
    : fallbackRows;
  const filtered = filterHierarchyRowsForField(fieldKey, selection, sourceRows);
  const dimensionOptionMap = typeof DIMENSION_FILTER_OPTIONS !== "undefined" ? new Map(
    (DIMENSION_FILTER_OPTIONS[dimensionKey] || [])
      .map(opt => typeof normOpt === "function" ? normOpt(opt) : opt)
      .filter(opt => opt.id)
      .map(opt => [limparTexto(opt.id), opt.label])
  ) : new Map();
  const candidateOptions = filtered.map(row => {
    const rawId = row[def.idKey]
      ?? row[`${fieldKey}Id`]
      ?? row.id
      ?? row.value
      ?? row.codigo;
    const normalizedId = limparTexto(rawId);
    const labelSource = dimensionOptionMap.get(normalizedId)
      || limparTexto(row[`${fieldKey}Label`])
      || limparTexto(row[`${def.labelKey}Label`])
      || limparTexto(row.label)
      || limparTexto(row[def.labelKey])
      || limparTexto(row.nome)
      || normalizedId;
    return typeof normOpt === "function" ? normOpt({ id: normalizedId, label: labelSource }) : { id: normalizedId, label: labelSource };
  });
  const uniqueOptions = typeof uniqById === "function" ? uniqById(candidateOptions) : candidateOptions;
  const orderById = new Map();
  uniqueOptions.forEach((opt, idx) => {
    const id = limparTexto(opt.id);
    if (id) orderById.set(id, idx);
  });
  const labelIndex = new Map();
  const valueIndex = new Map();
  const options = [];

  const mergeEntry = (entry, safeValue, safeLabel, meta = {}) => {
    if (!entry) return entry;
    if (safeLabel && entry.label !== safeLabel) {
      if (entry.label && entry.label !== safeLabel && !entry.aliases.includes(entry.label)) {
        entry.aliases.push(entry.label);
      }
      entry.label = safeLabel;
    }
    if (safeValue && safeValue !== entry.value && !entry.aliases.includes(safeValue)) {
      entry.aliases.push(safeValue);
    }
    if (Array.isArray(meta.aliases)) {
      meta.aliases.forEach(alias => {
        const clean = limparTexto(alias);
        if (clean && !entry.aliases.includes(clean)) entry.aliases.push(clean);
      });
    }
    if (meta.hidden != null && entry.hidden == null) entry.hidden = Boolean(meta.hidden);
    if (meta.slug && !entry.slug) entry.slug = meta.slug;
    if (meta.scenario && !entry.scenario) entry.scenario = meta.scenario;
    if (Number.isFinite(meta.order)) {
      const order = Number(meta.order);
      if (!Number.isFinite(entry.order) || order < entry.order) entry.order = order;
    } else if (safeValue && orderById.has(safeValue) && !Number.isFinite(entry.order)) {
      entry.order = orderById.get(safeValue);
    }
    return entry;
  };

  const register = (value, label, meta = {}) => {
    const safeLabel = limparTexto(label) || limparTexto(value);
    const safeValue = limparTexto(value);
    if (!safeLabel && !safeValue) return null;
    if (safeValue && valueIndex.has(safeValue)) {
      return mergeEntry(valueIndex.get(safeValue), safeValue, safeLabel, meta);
    }
    const key = simplificarTexto(safeLabel || safeValue);
    if (labelIndex.has(key)) {
      const existing = labelIndex.get(key);
      const merged = mergeEntry(existing, safeValue, safeLabel, meta);
      if (safeValue && merged) valueIndex.set(safeValue, merged);
      return merged;
    }
    if (!safeValue) return null;
    const optionValue = safeValue || safeLabel;
    const entry = {
      value: optionValue,
      label: safeLabel || optionValue,
      aliases: [],
    };
    if (safeLabel && safeLabel !== optionValue && !entry.aliases.includes(safeLabel)) entry.aliases.push(safeLabel);
    if (safeValue && safeValue !== optionValue && !entry.aliases.includes(safeValue)) entry.aliases.push(safeValue);
    if (Array.isArray(meta.aliases)) {
      meta.aliases.forEach(alias => {
        const clean = limparTexto(alias);
        if (clean && !entry.aliases.includes(clean)) entry.aliases.push(clean);
      });
    }
    if (meta.hidden != null) entry.hidden = Boolean(meta.hidden);
    if (meta.slug) entry.slug = meta.slug;
    if (meta.scenario) entry.scenario = meta.scenario;
    if (Number.isFinite(meta.order)) entry.order = Number(meta.order);
    else if (safeValue && orderById.has(safeValue)) entry.order = orderById.get(safeValue);
    labelIndex.set(key, entry);
    valueIndex.set(safeValue, entry);
    options.push(entry);
    return entry;
  };

  const comboFieldsWithConcat = new Set(["segmento", "diretoria", "gerencia", "agencia"]);

  filtered.forEach(row => {
    const valueCandidates = [
      row[def.idKey],
      row[`${fieldKey}Id`],
      row.id,
      row.value,
      row.codigo,
      row[def.labelKey]
    ];
    const labelCandidates = [
      row[`${fieldKey}Label`],
      row[`${def.labelKey}Label`],
      row.label,
      row.nome,
      row[def.labelKey],
      row[def.idKey]
    ];
    const rawValue = valueCandidates.find(candidate => limparTexto(candidate)) || valueCandidates[0];
    const rawLabel = labelCandidates.find(candidate => limparTexto(candidate)) || labelCandidates[0] || rawValue;
    const aliasCandidates = [];
    [
      `${def.labelKey}Aliases`,
      `${fieldKey}Aliases`,
      `${def.labelKey}Alias`,
      `${fieldKey}Alias`
    ].forEach(key => {
      const data = row[key];
      if (Array.isArray(data)) aliasCandidates.push(...data);
      else if (typeof data === "string" && data) aliasCandidates.push(data);
    });
    const cleanValue = limparTexto(rawValue);
    const cleanLabel = limparTexto(rawLabel);
    const explicitLabel = limparTexto(row[`${fieldKey}Label`]) || limparTexto(row[`${def.labelKey}Label`]);
    const idForLabel = limparTexto(row[def.idKey] || row[`${fieldKey}Id`]);
    const nameForLabel = limparTexto(row[`${fieldKey}NomeOriginal`])
      || limparTexto(row[`${def.labelKey}NomeOriginal`])
      || limparTexto(row[`${fieldKey}Nome`])
      || limparTexto(row[def.labelKey])
      || limparTexto(row.nome);
    const dimensionLabel = cleanValue ? dimensionOptionMap.get(cleanValue) : undefined;
    let displayLabel = dimensionLabel || explicitLabel || cleanLabel || cleanValue;
    
    // Para segmento, diretoria, agência, gerente gestão e gerente, sempre incluir ID junto com o nome
    if (fieldsWithIdRequired.has(fieldKey) && idForLabel && nameForLabel && idForLabel !== nameForLabel) {
      // Verifica se o label já contém o ID
      const labelWithId = buildHierarchyLabel(idForLabel, nameForLabel);
      if (labelWithId && labelWithId !== displayLabel) {
        displayLabel = labelWithId;
      } else if (!explicitLabel || !displayLabel.includes(idForLabel)) {
        displayLabel = `${idForLabel} - ${nameForLabel}`;
      }
      aliasCandidates.push(nameForLabel);
    } else if (!explicitLabel && comboFieldsWithConcat.has(fieldKey) && idForLabel && nameForLabel && idForLabel !== nameForLabel) {
      // Para outros campos que não estão em fieldsWithIdRequired mas estão em comboFieldsWithConcat
      displayLabel = `${idForLabel} - ${nameForLabel}`;
      aliasCandidates.push(nameForLabel);
    }
    if (cleanLabel) aliasCandidates.push(cleanLabel);
    if (cleanValue) aliasCandidates.push(cleanValue);
    if (explicitLabel) aliasCandidates.push(explicitLabel);
    if (dimensionLabel) aliasCandidates.push(dimensionLabel);
    if (hasDimensionPreset && !cleanValue) {
      return;
    }

    const meta = fieldKey === "segmento"
      ? {
          order: row.segmentoOrder ?? row.order,
          hidden: row.segmentoHidden ?? row.hidden,
          aliases: row.segmentoAliases ?? row.aliases,
          slug: row.segmentoSlug ?? row.slug,
          scenario: row.segmentoScenario ?? row.scenario
        }
      : {};
    if (aliasCandidates.length) {
      const normalized = aliasCandidates
        .map(alias => limparTexto(alias))
        .filter(alias => alias && alias !== cleanLabel && alias !== cleanValue);
      if (normalized.length) {
        meta.aliases = Array.from(new Set(normalized));
      }
    }
    if (meta.aliases && !meta.aliases.length) delete meta.aliases;
    if (row.hidden != null && meta.hidden == null) meta.hidden = Boolean(row.hidden);
    if (Number.isFinite(row.order) && !Number.isFinite(meta.order)) meta.order = Number(row.order);
    if (row.slug && !meta.slug) meta.slug = row.slug;
    if (row.scenario && !meta.scenario) meta.scenario = row.scenario;
    register(cleanValue || rawValue, displayLabel || cleanLabel || cleanValue, meta);
  });

  if (!options.length && hasDimensionPreset) {
    DIMENSION_FILTER_OPTIONS[dimensionKey].forEach(opt => {
      const normalized = typeof normOpt === "function" ? normOpt(opt) : opt;
      if (!normalized.id) return;
      
      // Para segmento, diretoria, agência, gerente gestão e gerente, garantir que o label inclua o ID
      if (fieldsWithIdRequired.has(fieldKey)) {
        const optId = limparTexto(normalized.id);
        const optLabel = limparTexto(normalized.label);
        const optName = typeof extractNameFromLabel === "function" ? extractNameFromLabel(optLabel) : optLabel;
        
        // Se o label não contém o ID, adiciona usando buildHierarchyLabel
        if (optId && optName && optId !== optName && !optLabel.includes(optId)) {
          normalized.label = buildHierarchyLabel(optId, optName) || `${optId} - ${optName}`;
        } else if (optId && !optLabel.includes(optId)) {
          normalized.label = buildHierarchyLabel(optId, optLabel) || `${optId} - ${optLabel}`;
        }
      }
      register(normalized.id, normalized.label);
    });
  }

  const hasOrder = options.some(opt => Number.isFinite(opt.order));
  options.sort((a, b) => {
    if (hasOrder) {
      const orderA = Number.isFinite(a.order) ? Number(a.order) : Number.POSITIVE_INFINITY;
      const orderB = Number.isFinite(b.order) ? Number(b.order) : Number.POSITIVE_INFINITY;
      if (orderA !== orderB) return orderA - orderB;
    }
    return String(a.label || "").localeCompare(String(b.label || ""), "pt-BR", { sensitivity: "base" });
  });

  const visibleOptions = options.filter(opt => !opt.hidden);
  // Só adiciona opção padrão se defaultValue não for vazio
  if (def.defaultValue && def.defaultLabel) {
    const defaultEntry = {
      value: def.defaultValue,
      label: def.defaultLabel,
      aliases: [def.defaultValue]
    };
    return [defaultEntry].concat(visibleOptions);
  }
  return visibleOptions;
}

function setSelectOptions(select, options, desiredValue, defaultValue){
  const current = limparTexto(desiredValue);
  select.innerHTML = "";
  let chosen = null;
  options.forEach(opt => {
    const option = document.createElement("option");
    option.value = opt.value;
    option.textContent = opt.label;
    select.appendChild(option);
    if (!chosen && typeof optionMatchesValue === "function" && optionMatchesValue(opt, current)) {
      chosen = opt;
    }
  });
  if (!chosen) {
    // Se defaultValue for vazio, não seleciona automaticamente
    if (defaultValue && defaultValue !== "") {
      chosen = options.find(opt => typeof optionMatchesValue === "function" ? optionMatchesValue(opt, defaultValue) : (opt.value === defaultValue)) || null;
    }
    // Se ainda não escolheu e há opções, escolhe a primeira (mas não se defaultValue for vazio e desiredValue também for vazio)
    if (!chosen && options.length > 0 && (defaultValue !== "" || current !== "")) {
      chosen = options[0];
    }
  }
  const nextValue = chosen ? chosen.value : "";
  select.value = nextValue;
  if (select.value !== nextValue) {
    // Se não há valor selecionado e defaultValue é vazio, deixa vazio
    if (!nextValue && defaultValue === "") {
      select.selectedIndex = -1;
    } else {
      select.selectedIndex = 0;
    }
  }
  if (select.dataset.search === "true") {
    if (typeof ensureSelectSearch === "function") ensureSelectSearch(select);
    if (typeof storeSelectSearchOptions === "function") storeSelectSearchOptions(select, options);
    if (typeof syncSelectSearchInput === "function") syncSelectSearchInput(select);
  }
  return select.value || nextValue;
}

function refreshHierarchyCombos(opts = {}){
  const rows = getHierarchyRows();
  const baseSelection = { ...hierarchyDefaultSelection(), ...getHierarchySelectionFromDOM(), ...(opts.selection || {}) };
  const result = { ...baseSelection };
  HIERARCHY_FIELDS_DEF.forEach(field => {
    const select = $(field.select);
    if (!select) return;
    const options = buildHierarchyOptions(field.key, result, rows);
    const chosen = setSelectOptions(select, options, result[field.key], field.defaultValue);
    result[field.key] = chosen;
  });
  return result;
}

function adjustHierarchySelection(selection, changedField){
  const def = HIERARCHY_FIELD_MAP.get(changedField);
  if (!def) return selection;
  const value = limparTexto(selection[changedField]);
  const effective = value || def.defaultValue;
  selection[changedField] = effective;

  // Define a ordem hierárquica: segmento -> diretoria -> gerencia -> agencia -> ggestao -> gerente
  const hierarchyOrder = ["segmento", "diretoria", "gerencia", "agencia", "ggestao", "gerente"];
  const changedIndex = hierarchyOrder.indexOf(changedField);
  
  // Limpa todos os níveis inferiores ao campo alterado
  if (changedIndex >= 0) {
    for (let i = changedIndex + 1; i < hierarchyOrder.length; i++) {
      const lowerField = hierarchyOrder[i];
      const lowerDef = HIERARCHY_FIELD_MAP.get(lowerField);
      if (lowerDef) {
        selection[lowerField] = lowerDef.defaultValue;
      }
    }
  }

  const setIf = (key, next) => {
    if (!next) return;
    const meta = HIERARCHY_FIELD_MAP.get(key);
    const normalized = limparTexto(next);
    if (!meta) return;
    selection[key] = normalized || meta.defaultValue;
  };

  // Quando um nível é selecionado, preenche automaticamente os níveis superiores se possível
  if (changedField === "agencia" && effective !== def.defaultValue){
    const meta = typeof findAgenciaMeta === "function" ? (findAgenciaMeta(effective) || {}) : {};
    setIf("gerencia", meta.gerencia || meta.regionalId || meta.regional);
    setIf("diretoria", meta.diretoria || meta.diretoriaId);
    setIf("segmento", meta.segmento || meta.segmentoId);
  }

  if (changedField === "gerencia" && effective !== def.defaultValue){
    const meta = typeof findGerenciaMeta === "function" ? (findGerenciaMeta(effective) || {}) : {};
    setIf("diretoria", meta.diretoria);
    setIf("segmento", meta.segmentoId);
  }

  if (changedField === "diretoria" && effective !== def.defaultValue){
    const meta = typeof findDiretoriaMeta === "function" ? (findDiretoriaMeta(effective) || {}) : {};
    setIf("segmento", meta.segmento);
  }

  if (changedField === "ggestao" && effective !== def.defaultValue){
    // Busca primeiro nos dados de estrutura (DIM_GGESTAO_LOOKUP)
    const ggIdStr = limparTexto(effective);
    let agenciaIdStr = "";
    let regionalIdStr = "";
    let diretoriaIdStr = "";
    let segmentoIdStr = "";
    
    if (typeof DIM_GGESTAO_LOOKUP !== "undefined" && DIM_GGESTAO_LOOKUP.has(ggIdStr)) {
      const ggData = DIM_GGESTAO_LOOKUP.get(ggIdStr);
      agenciaIdStr = String(ggData?.id_agencia || ggData?.agencia || "");
      
      if (agenciaIdStr && typeof DIM_AGENCIAS_LOOKUP !== "undefined") {
        // Tenta buscar por ID primeiro
        let agenciaData = DIM_AGENCIAS_LOOKUP.get(agenciaIdStr);
        // Se não encontrou, tenta buscar por todas as chaves possíveis
        if (!agenciaData) {
          for (const [key, value] of DIM_AGENCIAS_LOOKUP.entries()) {
            if (String(value?.id || key) === agenciaIdStr) {
              agenciaData = value;
              break;
            }
          }
        }
        
        if (agenciaData) {
          regionalIdStr = String(agenciaData?.id_regional || agenciaData?.regional_id || agenciaData?.gerencia_regional_id || "");
          
          if (regionalIdStr && typeof DIM_REGIONAIS_LOOKUP !== "undefined") {
            let regionalData = DIM_REGIONAIS_LOOKUP.get(regionalIdStr);
            if (!regionalData) {
              for (const [key, value] of DIM_REGIONAIS_LOOKUP.entries()) {
                if (String(value?.id || key) === regionalIdStr) {
                  regionalData = value;
                  break;
                }
              }
            }
            
            if (regionalData) {
              diretoriaIdStr = String(regionalData?.id_diretoria || regionalData?.diretoria_id || "");
              
              if (diretoriaIdStr && typeof DIM_DIRETORIAS_LOOKUP !== "undefined") {
                let diretoriaData = DIM_DIRETORIAS_LOOKUP.get(diretoriaIdStr);
                if (!diretoriaData) {
                  for (const [key, value] of DIM_DIRETORIAS_LOOKUP.entries()) {
                    if (String(value?.id || key) === diretoriaIdStr) {
                      diretoriaData = value;
                      break;
                    }
                  }
                }
                
                if (diretoriaData) {
                  segmentoIdStr = String(diretoriaData?.id_segmento || diretoriaData?.segmento_id || "");
                }
              }
            }
          } else if (agenciaData?.id_diretoria) {
            // Se não tem regional, tenta buscar diretoria diretamente da agência
            diretoriaIdStr = String(agenciaData.id_diretoria || agenciaData.diretoria_id || "");
            if (diretoriaIdStr && typeof DIM_DIRETORIAS_LOOKUP !== "undefined") {
              let diretoriaData = DIM_DIRETORIAS_LOOKUP.get(diretoriaIdStr);
              if (!diretoriaData) {
                for (const [key, value] of DIM_DIRETORIAS_LOOKUP.entries()) {
                  if (String(value?.id || key) === diretoriaIdStr) {
                    diretoriaData = value;
                    break;
                  }
                }
              }
              
              if (diretoriaData) {
                segmentoIdStr = String(diretoriaData?.id_segmento || diretoriaData?.segmento_id || "");
              }
            }
          }
        }
      }
    }
    
    // Se não encontrou nos dados de estrutura, tenta buscar no meta antigo
    if (!agenciaIdStr) {
      const meta = typeof findGerenteGestaoMeta === "function" ? (findGerenteGestaoMeta(effective) || {}) : {};
      agenciaIdStr = meta.agencia || "";
      regionalIdStr = meta.gerencia || meta.regionalId || meta.regional || "";
      diretoriaIdStr = meta.diretoria || meta.diretoriaId || "";
      segmentoIdStr = meta.segmento || meta.segmentoId || "";
      
      // Se encontrou agência no meta, tenta buscar regional e diretoria através da agência
      if (agenciaIdStr && !regionalIdStr) {
        const agMeta = typeof findAgenciaMeta === "function" ? (findAgenciaMeta(agenciaIdStr) || {}) : {};
        regionalIdStr = agMeta.gerencia || agMeta.regionalId || agMeta.regional || regionalIdStr;
        diretoriaIdStr = agMeta.diretoria || agMeta.diretoriaId || diretoriaIdStr;
        segmentoIdStr = agMeta.segmento || agMeta.segmentoId || segmentoIdStr;
      }
    }
    
    setIf("agencia", agenciaIdStr);
    setIf("gerencia", regionalIdStr);
    setIf("diretoria", diretoriaIdStr);
    setIf("segmento", segmentoIdStr);
  }

  if (changedField === "gerente" && effective !== def.defaultValue){
    const meta = typeof findGerenteMeta === "function" ? (findGerenteMeta(effective) || {}) : {};
    setIf("agencia", meta.agencia);
    setIf("ggestao", meta.gerenteGestao || meta.id_gestor);
    setIf("gerencia", meta.gerencia);
    setIf("diretoria", meta.diretoria);
    const agMeta = meta.agencia ? (typeof findAgenciaMeta === "function" ? (findAgenciaMeta(meta.agencia) || {}) : {}) : {};
    setIf("segmento", agMeta.segmento || agMeta.segmentoId);
  }

  if (changedField === "segmento" && effective !== def.defaultValue){
    // Quando segmento é alterado, limpa diretoria, gerencia, agencia, ggestao e gerente
    // (já feito acima, mas garantindo)
  }

  return selection;
}

function handleHierarchySelectionChange(changedField){
  const selection = adjustHierarchySelection(getHierarchySelectionFromDOM(), changedField);
  refreshHierarchyCombos({ selection });
}

/* ===== Funções de Obtenção e Aplicação de Filtros ===== */
function getFilterValues() {
  const val = (sel) => $(sel)?.value || "";
  const statusSelect = $("#f-status-kpi");
  const statusOption = statusSelect?.selectedOptions?.[0] || null;
  const statusKey = statusOption?.dataset.statusKey || (typeof normalizarChaveStatus === "function" ? normalizarChaveStatus(statusSelect?.value) : "") || (statusSelect?.value || "");
  const statusCodigo = statusOption?.dataset.statusCodigo || statusOption?.value || "";
  const statusId = statusOption?.dataset.statusId || statusCodigo || "";
  return {
    segmento: val("#f-segmento"),
    diretoria: val("#f-diretoria"),
    gerencia:  val("#f-gerencia"),
    agencia:   val("#f-agencia"),
    ggestao:   val("#f-gerente-gestao"),
    gerente:   val("#f-gerente"),
    secaoId:   val("#f-secao"),
    familiaId: val("#f-familia"),
    produtoId: val("#f-produto"),
    status:    statusKey || "todos",
    statusCodigo,
    statusId,
    visao:     val("#f-visao") || (typeof state !== "undefined" && state.accumulatedView) || "mensal",
  };
}

function filterRowsExcept(rows, except = {}, opts = {}) {
  const f = getFilterValues();
  const {
    searchTerm: searchRaw = "",
    dateStart,
    dateEnd,
    ignoreDate = false,
  } = opts;
  const searchTerm = searchRaw.trim();
  const startISO = ignoreDate ? "" : (dateStart ?? (typeof state !== "undefined" && state.period ? state.period.start : ""));
  const endISO = ignoreDate ? "" : (dateEnd ?? (typeof state !== "undefined" && state.period ? state.period.end : ""));

  return rows.filter(r => {
    const okSeg = selecaoPadrao(f.segmento) || matchesSegmentFilter(f.segmento, r.segmento, r.segmentoId, r.segmentoNome);
    const okDR  = (except.diretoria) || selecaoPadrao(f.diretoria) || matchesSelection(f.diretoria, r.diretoria, r.diretoriaNome);
    const okGR  = (except.gerencia)  || selecaoPadrao(f.gerencia)  || matchesSelection(f.gerencia, r.gerenciaRegional, r.gerenciaNome, r.regional);
    const okAg  = (except.agencia)   || selecaoPadrao(f.agencia)   || matchesSelection(f.agencia, r.agencia, r.agenciaNome, r.agenciaCodigo);
    const okGG  = (except.gerenteGestao)
      || selecaoPadrao(f.ggestao)
      || matchesSelection(
        f.ggestao,
        r.gerente_gestao_id,
        r.gerenteGestaoId,
        r.gerenteGestao,
        r.gerenteGestaoNome,
        r.gerenteGestaoLabel
      );
    const okGer = (except.gerente)
      || selecaoPadrao(f.gerente)
      || matchesSelection(
        f.gerente,
        r.gerente_id,
        r.gerenteId,
        r.gerente,
        r.gerenteNome,
        r.gerenteLabel
      );
    const familiaMetaRow = r.produtoId && typeof PRODUTO_TO_FAMILIA !== "undefined" ? PRODUTO_TO_FAMILIA.get(r.produtoId) : null;
    const rowSecaoId = r.secaoId
      || familiaMetaRow?.secaoId
      || (r.produtoId && typeof PRODUCT_INDEX !== "undefined" ? PRODUCT_INDEX.get(r.produtoId)?.sectionId : "")
      || (typeof SECTION_IDS !== "undefined" && SECTION_IDS.has(r.familiaId) ? r.familiaId : "");
    const okSec = selecaoPadrao(f.secaoId) || matchesSelection(f.secaoId, rowSecaoId, r.secaoId, r.secaoNome, r.secao, typeof getSectionLabel === "function" ? getSectionLabel(rowSecaoId) : "");
    const okIndicador = selecaoPadrao(f.familiaId)
      || matchesSelection(f.familiaId,
        r.produtoId,
        r.indicadorId,
        r.produto,
        r.produtoNome,
        r.ds_indicador,
        r.indicadorNome);
    const okSub = selecaoPadrao(f.produtoId)
      || matchesSelection(f.produtoId,
        r.subIndicadorId,
        r.subIndicador,
        r.subIndicadorNome,
        r.subproduto,
        r.prodOrSub,
        r.linhaProdutoId,
        r.linhaProdutoNome);
    let rowDate = r.data || r.competencia || "";
    if (rowDate && typeof rowDate !== "string") {
      if (rowDate instanceof Date) {
        rowDate = typeof isoFromUTCDate === "function" ? isoFromUTCDate(rowDate) : "";
      } else {
        rowDate = String(rowDate);
      }
    }
    const okDt  = (!startISO || !rowDate || rowDate >= startISO) && (!endISO || !rowDate || rowDate <= endISO);

    const ating = r.meta ? (r.realizado / r.meta) : 0;
    const statusKey = typeof normalizarChaveStatus === "function" ? normalizarChaveStatus(f.status) : f.status || "todos";
    let okStatus = true;
    if (statusKey === "atingidos") {
      okStatus = ating >= 1;
    } else if (statusKey === "nao") {
      okStatus = ating < 1;
    }

    const okSearch = typeof rowMatchesSearch === "function" ? rowMatchesSearch(r, searchTerm) : true;

    return okSeg && okDR && okGR && okAg && okGG && okGer && okSec && okIndicador && okSub && okDt && okStatus && okSearch;
  });
}

function filterRows(rows) { 
  return filterRowsExcept(rows, {}, { 
    searchTerm: typeof state !== "undefined" && state.tableSearchTerm ? state.tableSearchTerm : "" 
  }); 
}

function autoSnapViewToFilters() {
  if (typeof state !== "undefined" && state.tableSearchTerm) return;
  const f = getFilterValues();
  let snap = null;
  if (f.produtoId && f.produtoId !== "Todos" && f.produtoId !== "Todas") snap = "prodsub";
  else if (f.familiaId && f.familiaId !== "Todas") snap = "familia";
  else if (f.secaoId && f.secaoId !== "Todas") snap = "secao";
  else if (f.gerente && f.gerente !== "Todos") snap = "gerente";
  else if (f.gerencia && f.gerencia !== "Todas") snap = "gerencia";
  else if (f.diretoria && f.diretoria !== "Todas") snap = "diretoria";
  if (snap && typeof state !== "undefined" && state.tableView !== snap) { 
    state.tableView = snap; 
    if (typeof setActiveChip === "function") setActiveChip(snap); 
  }
}

/* ===== Funções de UI de Filtros ===== */
function ensureSegmentoField() {
  if ($("#f-segmento")) return;
  const filters = $(".filters");
  if (!filters) return;
  const actions = filters.querySelector(".filters__actions");
  const wrap = document.createElement("div");
  wrap.className = "filters__group";
  wrap.innerHTML = `<label>Segmento</label><select id="f-segmento" class="input"></select>`;
  filters.insertBefore(wrap, actions);
}

function wireClearFiltersButton() {
  const btn = $("#btn-limpar");
  if (!btn || btn.dataset.wired === "1") return;
  btn.dataset.wired = "1";
  btn.addEventListener("click", async (ev) => {
    ev.preventDefault();
    btn.disabled = true;
    try { 
      await clearFilters(); 
    } finally { 
      setTimeout(() => (btn.disabled = false), 250); 
    }
  });
}

async function clearFilters() {
  // Limpa filtros de hierarquia (segmento, diretoria, gerencia) deixando vazios
  const hierarchyFilters = ["#f-segmento", "#f-diretoria", "#f-gerencia"];
  hierarchyFilters.forEach(sel => {
    const el = $(sel);
    if (el && el.tagName === "SELECT") {
      el.selectedIndex = -1; // Deixa vazio
      el.value = "";
    }
  });
  
  // Limpa outros filtros
  [
    "#f-gerente",
    "#f-agencia","#f-gerente-gestao","#f-secao","#f-familia","#f-produto",
    "#f-status-kpi","#f-visao"
  ].forEach(sel => {
    const el = $(sel);
    if (!el) return;
    if (el.tagName === "SELECT") el.selectedIndex = 0;
    if (el.tagName === "INPUT")  el.value = "";
  });

  // valores padrão explícitos
  const st = $("#f-status-kpi"); if (st) st.value = "todos";
  const visaoSelect = $("#f-visao");
  if (visaoSelect) visaoSelect.value = "mensal";
  if (typeof state !== "undefined") state.accumulatedView = "mensal";
  
  // Não dispara eventos "change" para evitar chamadas de API
  const secaoSelect = $("#f-secao");
  if (secaoSelect) {
    secaoSelect.value = "Todas";
    // Não dispara evento change
  }
  const familiaSelect = $("#f-familia");
  if (familiaSelect) {
    familiaSelect.value = "Todas";
    // Não dispara evento change
  }
  const produtoSelect = $("#f-produto");
  if (produtoSelect) {
    produtoSelect.value = "Todos";
    // Não dispara evento change
  }

  if (typeof refreshHierarchyCombos === "function") refreshHierarchyCombos();

  // limpa busca (contrato) e estado
  if (typeof state !== "undefined") state.tableSearchTerm = "";
  if ($("#busca")) $("#busca").value = "";
  if (typeof refreshContractSuggestions === "function") refreshContractSuggestions("");
  if (typeof getDefaultPeriodRange === "function") {
    const defaultPeriod = getDefaultPeriodRange();
    if (typeof state !== "undefined") state.period = defaultPeriod;
    if (typeof syncPeriodFromAccumulatedView === "function") {
      syncPeriodFromAccumulatedView(typeof state !== "undefined" ? state.accumulatedView : "mensal", defaultPeriod.end);
    }
  }
  if (typeof state !== "undefined" && state.tableView === "contrato") {
    state.tableView = "diretoria";
    state.lastNonContractView = "diretoria";
    if (typeof setActiveChip === "function") setActiveChip("diretoria");
  }

  await (typeof withSpinner === "function" ? withSpinner(async () => {
    // Limpa todos os dados de período/filtros
    if (typeof clearPeriodData === "function") clearPeriodData();
    // Re-renderiza com dados zerados
    if (typeof applyFiltersAndRender === "function") applyFiltersAndRender();
    if (typeof renderAppliedFilters === "function") renderAppliedFilters();
    if (typeof renderCampanhasView === "function") renderCampanhasView();
    if (typeof state !== "undefined" && state.activeView === "ranking" && typeof renderRanking === "function") renderRanking();
    // Limpa cards do dashboard
    if (typeof updateDashboardCards === "function") updateDashboardCards();
  }, "Limpando filtros…") : Promise.resolve());
  if (typeof closeMobileFilters === "function") closeMobileFilters();
}

function setMobileFiltersState(open) {
  const card = document.querySelector(".card--filters");
  if (!card) return;
  card.classList.toggle("is-mobile-open", open);
  card.setAttribute("aria-expanded", open ? "true" : "false");
  document.body.classList.toggle("filters-open", open);

  const hamburger = document.querySelector(".topbar-hamburger");
  if (hamburger) hamburger.setAttribute("aria-expanded", open ? "true" : "false");

  const backdrop = document.getElementById("filters-backdrop");
  if (backdrop) {
    if (open) {
      backdrop.hidden = false;
      backdrop.classList.add("is-show");
    } else {
      backdrop.classList.remove("is-show");
      backdrop.hidden = true;
    }
  }

  const carousel = document.getElementById("mobile-carousel");
  if (carousel) {
    carousel.classList.toggle("mobile-carousel--hidden", open);
    carousel.setAttribute("aria-hidden", open ? "true" : "false");
    const ctrl = carousel._carouselControl;
    if (ctrl) {
      if (open && typeof ctrl.stop === "function") ctrl.stop();
      if (!open && typeof ctrl.start === "function") ctrl.start();
    }
  }

  const toggle = document.getElementById("btn-mobile-filtros");
  if (toggle) toggle.setAttribute("aria-expanded", open ? "true" : "false");
}

function openMobileFilters(){ setMobileFiltersState(true); }
function closeMobileFilters(){ setMobileFiltersState(false); }

function setupMobileFilters(){
  const openBtn = document.getElementById("btn-mobile-filtros");
  const closeBtn = document.getElementById("btn-fechar-filtros");
  const backdrop = document.getElementById("filters-backdrop");

  if (openBtn && !openBtn.dataset.bound) {
    openBtn.dataset.bound = "1";
    openBtn.addEventListener("click", () => openMobileFilters());
  }
  if (closeBtn && !closeBtn.dataset.bound) {
    closeBtn.dataset.bound = "1";
    closeBtn.addEventListener("click", () => closeMobileFilters());
  }
  if (backdrop && !backdrop.dataset.bound) {
    backdrop.dataset.bound = "1";
    backdrop.addEventListener("click", () => closeMobileFilters());
  }

  if (!setupMobileFilters._escBound) {
    window.addEventListener("keydown", (ev) => {
      if (ev.key === "Escape") closeMobileFilters();
    });
    setupMobileFilters._escBound = true;
  }
}

function ensureStatusFilterInAdvanced() {
  const adv = $("#advanced-filters");
  if (!adv) return;
  const host = adv.querySelector(".adv__grid") || adv;

  if (!$("#f-status-kpi")) {
    const wrap = document.createElement("div");
    wrap.className = "filters__group";
    wrap.innerHTML = `
      <label for="f-status-kpi">Status dos indicadores</label>
      <select id="f-status-kpi" class="input"></select>`;
    host.appendChild(wrap);
    $("#f-status-kpi").addEventListener("change", async () => {
      await (typeof withSpinner === "function" ? withSpinner(async () => {
        if (typeof applyFiltersAndRender === "function") applyFiltersAndRender();
        if (typeof renderAppliedFilters === "function") renderAppliedFilters();
        if (typeof renderCampanhasView === "function") renderCampanhasView();
        if (typeof state !== "undefined" && state.activeView === "ranking" && typeof renderRanking === "function") renderRanking();
      }, "Atualizando filtros…") : Promise.resolve());
    });
  }
  if (typeof updateStatusFilterOptions === "function") updateStatusFilterOptions();
}

function renderAppliedFilters() {
  const bar = $("#applied-bar"); if (!bar) return;
  const vals = getFilterValues();
  const items = [];

  const push = (k, v, resetFn) => {
    const chip = document.createElement("div");
    chip.className = "applied-chip";
    chip.innerHTML = `
      <span class="k">${k}</span>
      <span class="v">${v}</span>
      <button type="button" title="Limpar" class="applied-x" aria-label="Remover ${k}"><i class="ti ti-x"></i></button>`;
    chip.querySelector("button").addEventListener("click", async () => {
      await (typeof withSpinner === "function" ? withSpinner(async () => {
        resetFn?.();
        if (typeof applyFiltersAndRender === "function") applyFiltersAndRender();
        if (typeof renderAppliedFilters === "function") renderAppliedFilters();
        if (typeof renderCampanhasView === "function") renderCampanhasView();
        if (typeof state !== "undefined" && state.activeView === "ranking" && typeof renderRanking === "function") renderRanking();
      }, "Atualizando filtros…") : Promise.resolve());
    });
    items.push(chip);
  };

  bar.innerHTML = "";

  if (vals.segmento && vals.segmento !== "Todos" && vals.segmento !== "") push("Segmento", vals.segmento, () => $("#f-segmento").selectedIndex = -1);
  if (vals.diretoria && vals.diretoria !== "Todas" && vals.diretoria !== "") {
    const label = $("#f-diretoria")?.selectedOptions?.[0]?.text || vals.diretoria;
    push("Diretoria", label, () => $("#f-diretoria").selectedIndex = -1);
  }
  if (vals.gerencia && vals.gerencia !== "Todas" && vals.gerencia !== "") {
    const label = $("#f-gerencia")?.selectedOptions?.[0]?.text || vals.gerencia;
    push("Gerência", label, () => $("#f-gerencia").selectedIndex = -1);
  }
  if (vals.agencia && vals.agencia !== "Todas") {
    const label = $("#f-agencia")?.selectedOptions?.[0]?.text || vals.agencia;
    push("Agência", label, () => $("#f-agencia").selectedIndex = 0);
  }
  if (vals.ggestao && vals.ggestao !== "Todos") {
    const label = $("#f-gerente-gestao")?.selectedOptions?.[0]?.text || (typeof labelGerenteGestao === "function" ? labelGerenteGestao(vals.ggestao) : vals.ggestao);
    push("Gerente de gestão", label, () => $("#f-gerente-gestao").selectedIndex = 0);
  }
  if (vals.gerente && vals.gerente !== "Todos") {
    const label = $("#f-gerente")?.selectedOptions?.[0]?.text || (typeof labelGerente === "function" ? labelGerente(vals.gerente) : vals.gerente);
    push("Gerente", label, () => $("#f-gerente").selectedIndex = 0);
  }
  if (vals.secaoId && vals.secaoId !== "Todas") {
    const secaoLabel = $("#f-secao")?.selectedOptions?.[0]?.text
      || (typeof getSectionLabel === "function" ? getSectionLabel(vals.secaoId) : "")
      || vals.secaoId;
    push("Família", secaoLabel, () => $("#f-secao").selectedIndex = 0);
  }
  if (vals.familiaId && vals.familiaId !== "Todas") {
    const familiaLabel = $("#f-familia")?.selectedOptions?.[0]?.text
      || (typeof INDICATOR_CARD_INDEX !== "undefined" ? INDICATOR_CARD_INDEX.get(vals.familiaId)?.nome : "")
      || (typeof PRODUCT_INDEX !== "undefined" ? PRODUCT_INDEX.get(vals.familiaId)?.name : "")
      || vals.familiaId;
    push("Indicador", familiaLabel, () => $("#f-familia").selectedIndex = 0);
  }
  if (vals.produtoId && vals.produtoId !== "Todos" && vals.produtoId !== "Todas") {
    const indicadorResolved = (typeof resolverIndicadorPorAlias === "function" ? resolverIndicadorPorAlias(vals.familiaId) : null) || vals.familiaId;
    const prodLabel = $("#f-produto")?.selectedOptions?.[0]?.text
      || (typeof resolveSubIndicatorLabel === "function" ? resolveSubIndicatorLabel(indicadorResolved, vals.produtoId) : "")
      || vals.produtoId;
    push("Subindicador", prodLabel, () => $("#f-produto").selectedIndex = 0);
  }
  if (vals.status && vals.status !== "todos") {
    const statusEntry = typeof getStatusEntry === "function" ? getStatusEntry(vals.status) : null;
    const statusLabel = statusEntry?.nome
      || $("#f-status-kpi")?.selectedOptions?.[0]?.text
      || (typeof obterRotuloStatus === "function" ? obterRotuloStatus(vals.status) : vals.status);
    push("Status", statusLabel, () => $("#f-status-kpi").selectedIndex = 0);
  }
  if (vals.visao && vals.visao !== "mensal") {
    const visaoEntry = typeof ACCUMULATED_VIEW_OPTIONS !== "undefined" ? ACCUMULATED_VIEW_OPTIONS.find(opt => opt.value === vals.visao) : null;
    const visaoLabel = visaoEntry?.label || $("#f-visao")?.selectedOptions?.[0]?.text || vals.visao;
    push("Visão", visaoLabel, () => {
      const sel = $("#f-visao");
      if (sel) sel.value = "mensal";
      if (typeof state !== "undefined") state.accumulatedView = "mensal";
      if (typeof syncPeriodFromAccumulatedView === "function") syncPeriodFromAccumulatedView("mensal");
    });
  }

  items.forEach(ch => bar.appendChild(ch));
}

// END filters.js

