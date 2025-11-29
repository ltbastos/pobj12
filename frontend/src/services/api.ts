/**
 * Cliente HTTP genérico para chamadas à API
 * Abstrai a lógica de comunicação com o backend
 */

import { API_BASE_URL } from '../config/api'
import type { ApiResponse } from '../types'

/**
 * Faz uma requisição GET genérica à API
 * @param path - Caminho da rota (pode ser completo ou relativo)
 * @param params - Parâmetros de query string
 * @returns Promise com a resposta da API
 */
export async function apiGet<T = any>(
  path: string,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    // Remove barra inicial do path se presente
    const cleanPath = path.startsWith('/') ? path : `/${path}`

    // Usa a URL base da API (já configurada com porta própria)
    const baseUrl = API_BASE_URL
    // Constrói a URL corretamente: base + path
    const fullUrl = baseUrl.endsWith('/') 
      ? `${baseUrl}${cleanPath.startsWith('/') ? cleanPath.slice(1) : cleanPath}`
      : `${baseUrl}${cleanPath}`
    const url = new URL(fullUrl)

    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, String(value))
        }
      })
    }

    const response = await fetch(url.toString(), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    // Se a resposta já tem success/data, retorna como está
    if (data.success !== undefined) {
      return data
    }

    // Caso contrário, assume que é sucesso
    return {
      success: true,
      data: data
    }
  } catch (error) {
    console.error('API Error:', error)
    return {
      success: false,
      error: error instanceof Error ? error.message : 'Unknown error'
    }
  }
}

/**
 * Faz uma requisição POST genérica à API
 * @param path - Caminho da rota
 * @param body - Corpo da requisição
 * @param params - Parâmetros de query string
 * @returns Promise com a resposta da API
 */
export async function apiPost<T = any>(
  path: string,
  body?: Record<string, any>,
  params?: Record<string, any>
): Promise<ApiResponse<T>> {
  try {
    const cleanPath = path.startsWith('/') ? path : `/${path}`

    // Usa a URL base da API (já configurada com porta própria)
    const baseUrl = API_BASE_URL
    // Constrói a URL corretamente: base + path
    const fullUrl = baseUrl.endsWith('/') 
      ? `${baseUrl}${cleanPath.startsWith('/') ? cleanPath.slice(1) : cleanPath}`
      : `${baseUrl}${cleanPath}`
    const url = new URL(fullUrl)

    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, String(value))
        }
      })
    }

    const response = await fetch(url.toString(), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: body ? JSON.stringify(body) : undefined,
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    if (data.success !== undefined) {
      return data
    }

    return {
      success: true,
      data: data
    }
  } catch (error) {
    console.error('API Error:', error)
    return {
      success: false,
      error: error instanceof Error ? error.message : 'Unknown error'
    }
  }
}

