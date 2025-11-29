<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import omegaTemplate from '../legacy/omega-template.html?raw'
import '../assets/omega.css'
import { API_BASE_URL } from '../config/api'
import { apiGet, apiPost } from '../services/api'

type LegacyApiParams = Record<string, any> | undefined

const omegaTemplateHtml = omegaTemplate
const isReady = ref(false)
const errorMessage = ref<string | null>(null)
const isModalOpen = ref(false)

const rawBaseUrl = import.meta.env.BASE_URL || '/'
const legacyBaseUrl = rawBaseUrl.endsWith('/') ? rawBaseUrl : `${rawBaseUrl}/`
const LEGACY_SCRIPT_SRC = `${legacyBaseUrl}legacy/omega.js`
const addedBodyClasses = ['omega-standalone', 'has-omega-open']

let scriptPromise: Promise<void> | null = null
const previousGlobals: Record<string, any> = {}
let originalCloseOmega: ((...args: any[]) => any) | undefined
let originalCloseOmegaModule: ((...args: any[]) => any) | undefined
function normalizeLegacyPath(path: string): string {
  if (!path) return '/api'
  const prefixed = path.startsWith('/') ? path : `/${path}`
  if (prefixed.startsWith('/api/')) return prefixed
  if (prefixed.startsWith('/omega/')) return `/api${prefixed}`
  return prefixed.startsWith('/api') ? prefixed : `/api${prefixed}`
}

async function legacyGet(path: string, params?: LegacyApiParams) {
  const response = await apiGet(normalizeLegacyPath(path), params)
  if (!response.success) {
    throw new Error(response.error || 'Falha ao consultar a API do Omega.')
  }
  return response.data ?? null
}

async function legacyPost(path: string, body?: Record<string, any>, params?: LegacyApiParams) {
  const response = await apiPost(normalizeLegacyPath(path), body, params)
  if (!response.success) {
    throw new Error(response.error || 'Falha ao enviar dados para a API do Omega.')
  }
  return response.data ?? null
}

function setupLegacyGlobals() {
  if (typeof window === 'undefined') return
  const globalAny = window as any

  if (previousGlobals.apiGet === undefined) previousGlobals.apiGet = globalAny.apiGet
  if (previousGlobals.apiPost === undefined) previousGlobals.apiPost = globalAny.apiPost
  if (previousGlobals.DATA_SOURCE === undefined) previousGlobals.DATA_SOURCE = globalAny.DATA_SOURCE
  if (previousGlobals.API_HTTP_BASE === undefined) previousGlobals.API_HTTP_BASE = globalAny.API_HTTP_BASE

  globalAny.API_HTTP_BASE = API_BASE_URL
  globalAny.DATA_SOURCE = 'sql'
  globalAny.apiGet = legacyGet
  globalAny.apiPost = legacyPost
}

function restoreLegacyGlobals() {
  if (typeof window === 'undefined') return
  const globalAny = window as any

  if (previousGlobals.apiGet !== undefined) {
    globalAny.apiGet = previousGlobals.apiGet
  } else {
    delete globalAny.apiGet
  }
  if (previousGlobals.apiPost !== undefined) {
    globalAny.apiPost = previousGlobals.apiPost
  } else {
    delete globalAny.apiPost
  }
  if (previousGlobals.DATA_SOURCE !== undefined) {
    globalAny.DATA_SOURCE = previousGlobals.DATA_SOURCE
  } else {
    delete globalAny.DATA_SOURCE
  }
  if (previousGlobals.API_HTTP_BASE !== undefined) {
    globalAny.API_HTTP_BASE = previousGlobals.API_HTTP_BASE
  } else {
    delete globalAny.API_HTTP_BASE
  }
}

function ensureBodyState() {
  if (typeof document === 'undefined') return
  addedBodyClasses.forEach((cls) => document.body.classList.add(cls))
  isModalOpen.value = true
}

function resetBodyState() {
  if (typeof document === 'undefined') return
  if (!isModalOpen.value) return
  addedBodyClasses.forEach((cls) => document.body.classList.remove(cls))
  isModalOpen.value = false
}

function loadLegacyScript(): Promise<void> {
  if (typeof window === 'undefined') {
    return Promise.resolve()
  }

  if (typeof (window as any).openOmegaModule === 'function') {
    return Promise.resolve()
  }

  if (scriptPromise) {
    return scriptPromise
  }

  scriptPromise = new Promise((resolve, reject) => {
    const existing = document.querySelector<HTMLScriptElement>('script[data-omega-script="legacy"]')

    if (existing && existing.dataset.loaded === 'true') {
      scriptPromise = null
      resolve()
      return
    }

    const script = existing ?? document.createElement('script')
    script.src = LEGACY_SCRIPT_SRC
    script.async = true
    script.dataset.omegaScript = 'legacy'

    script.addEventListener('load', () => {
      script.dataset.loaded = 'true'
      scriptPromise = null
      resolve()
    })
    script.addEventListener('error', () => {
      scriptPromise = null
      reject(new Error('Não foi possível carregar o módulo legado do Omega.'))
    })

    if (!existing) {
      document.body.appendChild(script)
    }
  })

  return scriptPromise
}

function hookLegacyClosers() {
  if (typeof window === 'undefined') return
  const globalAny = window as any

  if (typeof globalAny.closeOmega === 'function' && !(globalAny.closeOmega as any).__omegaVueHooked) {
    originalCloseOmega = globalAny.closeOmega
    const wrapped = function (this: unknown, ...args: any[]) {
      resetBodyState()
      return originalCloseOmega?.apply(this, args)
    }
    ;(wrapped as any).__omegaVueHooked = true
    globalAny.closeOmega = wrapped
  }

  if (typeof globalAny.closeOmegaModule === 'function' && !(globalAny.closeOmegaModule as any).__omegaVueHooked) {
    originalCloseOmegaModule = globalAny.closeOmegaModule
    const wrappedModule = function (this: unknown, ...args: any[]) {
      resetBodyState()
      return originalCloseOmegaModule?.apply(this, args)
    }
    ;(wrappedModule as any).__omegaVueHooked = true
    globalAny.closeOmegaModule = wrappedModule
  }
}

function restoreLegacyClosers() {
  if (typeof window === 'undefined') return
  const globalAny = window as any

  if (originalCloseOmega) {
    globalAny.closeOmega = originalCloseOmega
    originalCloseOmega = undefined
  }

  if (originalCloseOmegaModule) {
    globalAny.closeOmegaModule = originalCloseOmegaModule
    originalCloseOmegaModule = undefined
  }
}

function openLegacyOmega(detail: any = null) {
  if (typeof window === 'undefined') return
  ensureBodyState()

  const root = document.getElementById('omega-modal')
  if (root) {
    root.hidden = false
  }

  const globalAny = window as any
  const opener = globalAny.openOmegaModule || globalAny.openOmega

  if (typeof opener === 'function') {
    opener(detail)
    return
  }

  resetBodyState()
  throw new Error('Módulo Omega não inicializado.')
}

function registerGlobalOpener() {
  if (typeof window === 'undefined') return
  const globalAny = window as any
  globalAny.__openOmegaFromVue = (detail?: any) => {
    if (!isReady.value) {
      console.warn('Omega ainda está carregando.')
      return
    }
    try {
      openLegacyOmega(detail ?? null)
    } catch (err) {
      console.error(err)
    }
  }
}

function unregisterGlobalOpener() {
  if (typeof window === 'undefined') return
  const globalAny = window as any
  if (globalAny.__openOmegaFromVue) {
    delete globalAny.__openOmegaFromVue
  }
}

onMounted(async () => {
  try {
    setupLegacyGlobals()
    await loadLegacyScript()
    hookLegacyClosers()
    registerGlobalOpener()
    isReady.value = true
  } catch (err) {
    errorMessage.value = err instanceof Error ? err.message : 'Não foi possível carregar o módulo Omega.'
    console.error(err)
  }
})

onBeforeUnmount(() => {
  unregisterGlobalOpener()
  restoreLegacyClosers()
  resetBodyState()
  restoreLegacyGlobals()
})
</script>

<template>
  <div class="omega-legacy-modal">
    <div
      v-if="isModalOpen"
      class="omega-legacy-modal__scrim"
      aria-hidden="true"
    ></div>
    <div class="omega-legacy-modal__root" v-html="omegaTemplateHtml"></div>

    <div v-if="!isReady && !errorMessage" class="omega-legacy-modal__status">
      Carregando módulo Omega...
    </div>

    <div v-if="errorMessage" class="omega-legacy-modal__status omega-legacy-modal__status--error">
      {{ errorMessage }}
    </div>
  </div>
</template>

<style scoped>
.omega-legacy-modal {
  width: 0;
  height: 0;
  overflow: hidden;
}

.omega-legacy-modal__root :deep(#omega-modal) {
  position: fixed;
  inset: 0;
  z-index: 2200;
}

.omega-legacy-modal__scrim {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  z-index: 2190;
  pointer-events: none;
}

.omega-legacy-modal__status {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: rgba(15, 23, 42, 0.9);
  color: #fff;
  padding: 10px 18px;
  border-radius: 999px;
  font-weight: 600;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.2);
  z-index: 3200;
}

.omega-legacy-modal__status--error {
  background: #dc2626;
}
</style>

