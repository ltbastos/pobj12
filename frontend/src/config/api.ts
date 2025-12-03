export function getApiBaseUrl(): string {
  // 1. Prioridade máxima: variável global injetada pelo backend (SSR, Nginx, index.html)
  if (typeof window !== 'undefined' && (window as any).API_HTTP_BASE) {
    const base = String((window as any).API_HTTP_BASE).trim()
    if (base) return base.replace(/\/$/, '')
  }

  // 2. Prioridade do frontend (sempre funciona)
  const envUrl = import.meta.env.VITE_API_URL
  if (envUrl) return envUrl.replace(/\/$/, '')

  // 3. Ambiente de desenvolvimento (vite)
  if (import.meta.env.DEV && typeof window !== 'undefined') {
    const host = window.location.hostname
    const protocol = window.location.protocol
    const port = import.meta.env.VITE_API_PORT

    // Acesso por IP diretamente no browser
    const isIp = /^\d+\.\d+\.\d+\.\d+$/.test(host)

    if (isIp) {
      if (port) return `${protocol}//${host}:${port}`.replace(/\/$/, '')
      return window.location.origin.replace(/\/$/, '')
    }

    // localhost → usar origin (proxy do Vite resolve)
    return window.location.origin.replace(/\/$/, '')
  }

  // 4. Produção
  if (typeof window !== 'undefined') {
    return window.location.origin.replace(/\/$/, '')
  }

  // 5. SSR/fallback
  return 'http://localhost:8081'
}

/**
 * Obtém a API Key para autenticação
 * Prioridade:
 * 1. Variável global injetada (window.API_KEY)
 * 2. Variável de ambiente VITE_API_KEY
 */
export function getApiKey(): string | null {
  if (typeof window !== 'undefined' && (window as any).API_KEY) {
    const key = String((window as any).API_KEY).trim()
    if (key) return key
  }

  const envKey = import.meta.env.VITE_API_KEY
  if (envKey) return String(envKey).trim()

  return null
}

export const API_BASE_URL = getApiBaseUrl()
