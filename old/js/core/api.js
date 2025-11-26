// BEGIN core/api.js
/* =========================================================
   POBJ • API Client
   ========================================================= */

function ensureHttpContext(){
  if (typeof window === "undefined") return;
  if (window.location.protocol !== "file:") return;

  const targetBase = (typeof API_HTTP_BASE === "string" && API_HTTP_BASE.startsWith("http"))
    ? API_HTTP_BASE
    : DEFAULT_HTTP_BASE;

  try {
    const fallbackBase = typeof window !== "undefined" && window.location.origin
      ? window.location.origin
      : "http://localhost:8000";
    const candidate = new URL(targetBase, fallbackBase);
    if (candidate.protocol === "http:" || candidate.protocol === "https:") {
      const candidateHref = candidate.href;
      const alreadyThere = candidateHref.replace(/\/?$/, "/") === window.location.href.replace(/\/?$/, "/");
      if (!alreadyThere) {
        window.location.href = candidateHref;
      }
    }
  } catch (error) {
    console.warn("Não foi possível redirecionar automaticamente para o Apache", error);
  }
}

function resolveApiBaseUrl(){
  const normalizedPath = typeof API_PATH === "string" ? API_PATH.trim() : "";
  const fallbackBase = typeof API_HTTP_BASE === "string" && API_HTTP_BASE
    ? API_HTTP_BASE
    : DEFAULT_HTTP_BASE;

  const attempts = [];

  if (normalizedPath) {
    attempts.push(() => new URL(normalizedPath, window.location.href));
  } else {
    attempts.push(() => new URL("../src/index.php", window.location.href));
  }

  if (fallbackBase) {
    attempts.push(() => new URL(normalizedPath || "../src/index.php", fallbackBase));
  }

  let lastError;
  for (const build of attempts){
    try {
      const url = build();
      if (url.protocol === "file:") {
        continue;
      }
      return url;
    } catch (err) {
      lastError = err;
    }
  }

  const error = new Error("Não foi possível resolver o endereço da API PHP.");
  if (lastError) {
    error.cause = lastError;
  }
  throw error;
}

const AGENT_ENDPOINT = (() => {
  try {
    const url = resolveApiBaseUrl();
    url.searchParams.set(API_ENDPOINT_PARAM, "agent");
    return url.toString();
  } catch (err) {
    console.warn("Não foi possível montar o endpoint do agente", err);
    return null;
  }
})();

function prepareApiUrl(baseUrl, path, params){
  // Sempre usa a raiz do site para construir URLs da API com prefixo /api/
  // Considera o pathname base caso o site não esteja na raiz (ex: /pobj9/)
  // Remove 'public' do pathname se presente
  let basePath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
  basePath = basePath.replace(/\/public$/, ''); // Remove /public se presente
  const url = new URL(window.location.origin + basePath);
  const searchParams = new URLSearchParams();

  if (params && typeof params === "object"){
    Object.entries(params).forEach(([key, value]) => {
      if (key === API_ENDPOINT_PARAM) return;
      if (value === undefined || value === null) return;
      searchParams.append(key, value);
    });
  }

  const normalized = typeof path === "string" ? path.trim() : "";
  const endpoint = normalized.replace(/^\/+/, "").replace(/^api\//, ""); // Remove /api/ se já estiver presente
  if (endpoint){
    // Usa /api/endpoint como pathname - o backend lê do PATH_INFO, não precisa do parâmetro na query string
    url.pathname = `/api/${endpoint}`;
  }

  const queryString = searchParams.toString();
  url.search = queryString ? `?${queryString}` : "";

  return { url: url.toString() };
}

// Aqui eu faço uma chamada GET simples contra a API com tratamento básico de erro.
async function apiGet(path, params){
  let baseUrl;
  try {
    baseUrl = resolveApiBaseUrl();
  } catch (err) {
    const error = new Error("Não foi possível resolver o endereço da API PHP.");
    error.cause = err;
    throw error;
  }
  const { url } = prepareApiUrl(baseUrl, path, params);

  let response;
  try {
    response = await fetch(url, { cache: "no-store" });
  } catch (err) {
    const error = new Error("Não foi possível contactar a API PHP em src/index.php.");
    error.cause = err;
    throw error;
  }

  let text;
  try {
    text = await response.text();
  } catch (err) {
    const error = new Error("Falha ao ler a resposta da API PHP.");
    error.cause = err;
    throw error;
  }

  let json;
  try {
    json = text ? JSON.parse(text) : null;
  } catch (err) {
    const error = new Error("A API PHP retornou um JSON inválido.");
    error.cause = err;
    error.responseText = text;
    throw error;
  }

  if (!response.ok) {
    const message = (json && typeof json === "object" && json.error)
      ? String(json.error)
      : `Falha ao carregar dados (HTTP ${response.status})`;
    const error = new Error(message);
    error.code = "HTTP_ERROR";
    error.status = response.status;
    error.payload = json;
    throw error;
  }

  if (json && typeof json === "object" && json.error) {
    const error = new Error(String(json.error));
    error.code = "API_ERROR";
    error.payload = json;
    throw error;
  }

  return json;
}

// Aqui eu faço uma chamada POST simples contra a API para enviar JSON.
async function apiPost(path, body = {}, params){
  let baseUrl;
  try {
    baseUrl = resolveApiBaseUrl();
  } catch (err) {
    const error = new Error("Não foi possível resolver o endereço da API PHP.");
    error.cause = err;
    throw error;
  }

  const { url } = prepareApiUrl(baseUrl, path, params);

  let response;
  try {
    response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body === undefined ? "{}" : JSON.stringify(body),
      cache: "no-store",
    });
  } catch (err) {
    const error = new Error("Não foi possível contactar a API PHP em src/index.php.");
    error.cause = err;
    throw error;
  }

  let text;
  try {
    text = await response.text();
  } catch (err) {
    const error = new Error("Falha ao ler a resposta da API PHP.");
    error.cause = err;
    throw error;
  }

  let json;
  try {
    json = text ? JSON.parse(text) : null;
  } catch (err) {
    const error = new Error("A API PHP retornou um JSON inválido.");
    error.cause = err;
    error.responseText = text;
    throw error;
  }

  if (!response.ok) {
    const message = (json && typeof json === "object" && json.error)
      ? String(json.error)
      : `Falha ao enviar dados (HTTP ${response.status})`;
    const error = new Error(message);
    error.code = "HTTP_ERROR";
    error.status = response.status;
    error.payload = json;
    throw error;
  }

  if (json && typeof json === "object" && json.error) {
    const error = new Error(String(json.error));
    error.code = "API_ERROR";
    error.payload = json;
    throw error;
  }

  return json;
}

// Inicializar contexto HTTP
ensureHttpContext();

// Expor no window para compatibilidade
if (typeof window !== "undefined") {
  window.resolveApiBaseUrl = resolveApiBaseUrl;
  window.prepareApiUrl = prepareApiUrl;
  window.apiGet = apiGet;
  window.apiPost = apiPost;
  window.AGENT_ENDPOINT = AGENT_ENDPOINT;
}



