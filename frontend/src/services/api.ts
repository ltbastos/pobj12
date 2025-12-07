import { API_BASE_URL, getApiKey } from '../config/api'
import type { ApiResponse } from '../types'

function buildUrl(path: string, params?: Record<string, any>) {
  const cleanPath = path.startsWith('/') ? path : `/${path}`
  const base = API_BASE_URL.replace(/\/$/, '')
  const url = new URL(base + cleanPath)

  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        url.searchParams.append(key, String(value))
      }
    })
  }

  return url.toString()
}

function buildHeaders(customHeaders?: Record<string, string>): Record<string, string> {
  const headers: Record<string, string> = {
    ...customHeaders
  }

  const apiKey = getApiKey()
  if (apiKey) {
    headers['X-API-Key'] = apiKey
  }

  // Tenta obter o funcional do usuário do localStorage ou sessionStorage
  const userFuncional = localStorage.getItem('userFuncional') || sessionStorage.getItem('userFuncional')
  if (userFuncional) {
    headers['X-User-Funcional'] = userFuncional
  }

  return headers
}

export async function apiGet<T>(
  path: string,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    const url = buildUrl(path, params)

    const response = await fetch(url, {
      method: 'GET',
      headers: buildHeaders(),
      cache: 'no-store'
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()

    return data.success !== undefined
      ? data
      : { success: true, data }
  } catch (e) {
    return {
      success: false,
      error: e instanceof Error ? e.message : 'Unknown error'
    }
  }
}

export async function apiPost<T>(
  path: string,
  body?: Record<string, any>,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    const url = buildUrl(path, params)

    const response = await fetch(url, {
      method: 'POST',
      headers: buildHeaders({
        'Content-Type': 'application/json'
      }),
      body: body ? JSON.stringify(body) : undefined,
      cache: 'no-store'
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()

    return data.success !== undefined
      ? data
      : { success: true, data }
  } catch (e) {
    return {
      success: false,
      error: e instanceof Error ? e.message : 'Unknown error'
    }
  }
}

export async function apiPut<T>(
  path: string,
  body?: Record<string, any>,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    const url = buildUrl(path, params)

    const response = await fetch(url, {
      method: 'PUT',
      headers: buildHeaders({
        'Content-Type': 'application/json'
      }),
      body: body ? JSON.stringify(body) : undefined,
      cache: 'no-store'
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()

    return data.success !== undefined
      ? data
      : { success: true, data }
  } catch (e) {
    return {
      success: false,
      error: e instanceof Error ? e.message : 'Unknown error'
    }
  }
}

export async function apiDelete<T>(
  path: string,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    const url = buildUrl(path, params)

    const response = await fetch(url, {
      method: 'DELETE',
      headers: buildHeaders(),
      cache: 'no-store'
    })

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }

    const data = await response.json()

    return data.success !== undefined
      ? data
      : { success: true, data }
  } catch (e) {
    return {
      success: false,
      error: e instanceof Error ? e.message : 'Unknown error'
    }
  }
}
