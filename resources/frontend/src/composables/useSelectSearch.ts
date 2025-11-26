/**
 * Composable para implementar busca em selects
 * Transforma selects normais em selects com busca quando têm data-search="true"
 */

import { onMounted, onUnmounted, nextTick } from 'vue'

interface SelectSearchData {
  select: HTMLSelectElement
  input: HTMLInputElement
  panel: HTMLElement
  list: HTMLElement
  wrapper: HTMLElement
  options: Array<{ value: string; label: string }>
  hidePanel: () => void
  showPanel: () => void
}

const selectSearchDataMap = new WeakMap<HTMLSelectElement, SelectSearchData>()

/**
 * Simplifica texto para busca (remove acentos, converte para minúsculas)
 */
const simplificarTexto = (text: string): string => {
  if (!text) return ''
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
}

/**
 * Escapa HTML para prevenir XSS
 */
const escapeHTML = (text: string): string => {
  const div = document.createElement('div')
  div.textContent = text
  return div.innerHTML
}

/**
 * Inicializa busca em um select
 */
export function initSelectSearch(select: HTMLSelectElement): void {
  if (!select || select.dataset.searchBound === '1' || select.dataset.search !== 'true') {
    return
  }

  const group = select.closest('.filters__group')
  if (!group) return

  const labelText = group.querySelector('label')?.textContent?.trim() || 'opção'
  
  // Cria wrapper
  const wrapper = document.createElement('div')
  wrapper.className = 'select-search'
  select.parentNode?.insertBefore(wrapper, select)
  wrapper.appendChild(select)

  // Cria painel de busca
  const panel = document.createElement('div')
  panel.className = 'select-search__panel'
  panel.setAttribute('role', 'listbox')
  panel.setAttribute('aria-label', `Sugestões de ${labelText}`)
  panel.hidden = true
  panel.innerHTML = `
    <div class="select-search__box">
      <input type="search" class="input input--xs select-search__input" placeholder="Pesquisar ${labelText.toLowerCase()}" aria-label="Pesquisar ${labelText}">
    </div>
    <div class="select-search__results"></div>
  `
  wrapper.appendChild(panel)

  const input = panel.querySelector('input') as HTMLInputElement
  const list = panel.querySelector('.select-search__results') as HTMLElement

  const hidePanel = (): void => {
    panel.hidden = true
    wrapper.classList.remove('is-open')
  }

  const showPanel = (): void => {
    panel.hidden = false
    wrapper.classList.add('is-open')
    updateSelectSearchResults(select, { limit: 12, forceAll: true })
    window.requestAnimationFrame(() => input.focus())
  }

  const data: SelectSearchData = {
    select,
    input,
    panel,
    list,
    wrapper,
    options: [],
    hidePanel,
    showPanel
  }

  selectSearchDataMap.set(select, data)

  // Event listeners
  input.addEventListener('input', () => updateSelectSearchResults(select))
  input.addEventListener('focus', () => updateSelectSearchResults(select))
  input.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape') {
      input.value = ''
      hidePanel()
    }
    if (ev.key === 'Enter') {
      const first = list.querySelector('.select-search__item') as HTMLElement | null
      if (first) {
        ev.preventDefault()
        first.click()
      }
    }
  })
  input.addEventListener('blur', () => setTimeout(hidePanel, 120))

  panel.addEventListener('mousedown', (ev) => ev.preventDefault())
  panel.addEventListener('click', (ev) => {
    const item = (ev.target as HTMLElement).closest('.select-search__item')
    if (!item) return
    ev.preventDefault()
    const value = item.getAttribute('data-value') || ''
    aplicarSelecaoBusca(select, value)
    hidePanel()
  })

  select.addEventListener('mousedown', (ev) => {
    ev.preventDefault()
    if (panel.hidden) {
      showPanel()
    } else {
      hidePanel()
    }
  })

  select.addEventListener('keydown', (ev) => {
    if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(ev.key)) {
      ev.preventDefault()
      showPanel()
    }
  })

  select.addEventListener('change', () => {
    const meta = selectSearchDataMap.get(select)
    if (!meta) return
    meta.input.value = ''
    meta.hidePanel()
  })

  select.dataset.searchBound = '1'
  
  // Armazena opções iniciais
  storeSelectSearchOptions(select)
}

/**
 * Armazena opções do select para busca
 */
function storeSelectSearchOptions(select: HTMLSelectElement): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  const options: Array<{ value: string; label: string }> = []
  Array.from(select.options).forEach((option) => {
    if (option.value) {
      options.push({
        value: option.value,
        label: option.textContent || option.value
      })
    }
  })

  data.options = options
}

/**
 * Atualiza resultados da busca
 */
function updateSelectSearchResults(select: HTMLSelectElement, opts: { limit?: number; forceAll?: boolean } = {}): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  const { input, panel, list, options } = data
  if (!options || !options.length) {
    panel.hidden = true
    if (list) list.innerHTML = ''
    return
  }

  const term = simplificarTexto(input.value)
  const matches = options.filter((opt) => {
    if (!term) return true
    return simplificarTexto(opt.label).includes(term)
  })

  const limit = Number.isFinite(opts.limit) ? opts.limit : 10
  const finalList = matches.slice(0, limit)

  if (!finalList.length) {
    if (!term) {
      panel.hidden = true
      if (list) list.innerHTML = ''
      return
    }
    if (list) list.innerHTML = '<div class="select-search__empty">Nenhum resultado encontrado</div>'
    panel.hidden = false
    return
  }

  const rows = finalList
    .map(
      (opt) =>
        `<button type="button" class="select-search__item" data-value="${escapeHTML(opt.value)}">${escapeHTML(opt.label)}</button>`
    )
    .join('\n')

  if (list) list.innerHTML = rows
  panel.hidden = false
}

/**
 * Aplica seleção da busca
 */
function aplicarSelecaoBusca(select: HTMLSelectElement, rawValue: string): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  select.value = rawValue
  data.input.value = ''
  if (typeof data.hidePanel === 'function') {
    data.hidePanel()
  }
  select.dispatchEvent(new Event('change', { bubbles: true }))
}

/**
 * Sincroniza opções do select quando mudam
 */
export function syncSelectSearchOptions(select: HTMLSelectElement): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  storeSelectSearchOptions(select)
  updateSelectSearchResults(select)
}

/**
 * Inicializa todos os selects com busca
 */
export function initAllSelectSearch(): void {
  nextTick(() => {
    const selects = document.querySelectorAll<HTMLSelectElement>('select[data-search="true"]')
    selects.forEach((select) => {
      if (select.dataset.searchBound !== '1') {
        initSelectSearch(select)
      }
    })
  })
}

/**
 * Composable para usar busca em selects
 */
export function useSelectSearch() {
  onMounted(() => {
    initAllSelectSearch()
    
    // Observa mudanças no DOM para inicializar novos selects
    const observer = new MutationObserver(() => {
      initAllSelectSearch()
    })

    observer.observe(document.body, {
      childList: true,
      subtree: true
    })

    onUnmounted(() => {
      observer.disconnect()
    })
  })

  return {
    initSelectSearch,
    syncSelectSearchOptions,
    initAllSelectSearch
  }
}

