/**
 * Configuração da API
 * Define a URL base da API independente do servidor de desenvolvimento do Vite
 */

/**
 * Obtém a URL base da API
 * Prioridade:
 * 1. window.API_HTTP_BASE (definido no HTML antes do script)
 * 2. VITE_API_URL (variável de ambiente)
 * 3. Em desenvolvimento: usa proxy do Vite (mesma origem)
 * 4. Em produção: usa window.location.origin + base path do Vite
 * 5. Padrão: http://localhost:8080 (porta padrão do backend PHP)
 */
export function getApiBaseUrl(): string {
  // 1. Verifica se foi definido no window (mais flexível para produção)
  if (typeof window !== 'undefined' && (window as any).API_HTTP_BASE) {
    const base = String((window as any).API_HTTP_BASE).trim()
    if (base) {
      return base.endsWith('/') ? base.slice(0, -1) : base
    }
  }

  // 2. Verifica variável de ambiente do Vite
  const envUrl = import.meta.env.VITE_API_URL
  if (envUrl) {
    return envUrl.endsWith('/') ? envUrl.slice(0, -1) : envUrl
  }

  // 3. Em desenvolvimento (modo dev do Vite), usa proxy (mesma origem)
  if (import.meta.env.DEV) {
    return window.location.origin
  }

  // 4. Em produção, usa window.location.origin + base path do Vite
  if (typeof window !== 'undefined') {
    const basePath = import.meta.env.BASE_URL || '/'
    const origin = window.location.origin
    const base = basePath === '/' ? origin : `${origin}${basePath.replace(/\/$/, '')}`
    return base
  }

  // 5. Padrão: localhost:8080 (porta do backend PHP)
  return 'http://localhost:8080'
}

/**
 * URL base da API
 */
export const API_BASE_URL = getApiBaseUrl()

