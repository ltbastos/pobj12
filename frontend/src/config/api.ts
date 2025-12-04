export function getApiBaseUrl(): string {
  
  if (typeof window !== 'undefined' && (window as any).API_HTTP_BASE) {
    const base = String((window as any).API_HTTP_BASE).trim()
    if (base) return base.replace(/\/$/, '')
  }

  const envUrl = import.meta.env.VITE_API_URL
  if (envUrl) return envUrl.replace(/\/$/, '')

  if (import.meta.env.DEV && typeof window !== 'undefined') {
    const host = window.location.hostname
    const protocol = window.location.protocol
    const port = import.meta.env.VITE_API_PORT

    const isIp = /^\d+\.\d+\.\d+\.\d+$/.test(host)

    if (isIp) {
      if (port) return `${protocol}//${host}:${port}`.replace(/\/$/, '')
      return window.location.origin.replace(/\/$/, '')
    }

    return window.location.origin.replace(/\/$/, '')
  }

  if (typeof window !== 'undefined') {
    return window.location.origin.replace(/\/$/, '')
  }

  return 'http://localhost:8081'
}

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
