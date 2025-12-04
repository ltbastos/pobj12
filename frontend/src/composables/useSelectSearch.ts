import { onMounted, onUnmounted, nextTick } from 'vue'

type SelectSearchData = {
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

const simplificarTexto = (text: string): string => {
  if (!text) return ''
  return text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
}

const escapeHTML = (text: string): string => {
  const div = document.createElement('div')
  div.textContent = text
  return div.textContent || ''
}

export function initSelectSearch(select: HTMLSelectElement): void {
  if (!select || select.dataset.searchBound === '1' || select.dataset.search !== 'true') {
    return
  }

  const group = select.closest('.filters__group')
  if (!group) return

  const labelText = group.querySelector('label')?.textContent?.trim() || 'opção'
  
  const wrapper = document.createElement('div')
  wrapper.className = 'select-search'
  select.parentNode?.insertBefore(wrapper, select)
  wrapper.appendChild(select)

  const panel = document.createElement('div')
  panel.className = 'select-search__panel'
  panel.setAttribute('role', 'listbox')
  panel.setAttribute('aria-label', `Sugestões de ${labelText}`)
  panel.hidden = true
  
  const box = document.createElement('div')
  box.className = 'select-search__box'
  
  const input = document.createElement('input')
  input.type = 'search'
  input.className = 'input input--xs select-search__input'
  input.placeholder = `Pesquisar ${labelText.toLowerCase()}`
  input.setAttribute('aria-label', `Pesquisar ${labelText}`)
  box.appendChild(input)
  
  const list = document.createElement('div')
  list.className = 'select-search__results'
  
  panel.appendChild(box)
  panel.appendChild(list)
  wrapper.appendChild(panel)

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

  input.oninput = () => updateSelectSearchResults(select)
  input.onfocus = () => updateSelectSearchResults(select)
  input.onkeydown = (ev) => {
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
  }
  input.onblur = () => setTimeout(hidePanel, 120)

  panel.onmousedown = (ev) => ev.preventDefault()
  panel.onclick = (ev) => {
    const item = (ev.target as HTMLElement).closest('.select-search__item')
    if (!item) return
    ev.preventDefault()
    const value = item.getAttribute('data-value') || ''
    aplicarSelecaoBusca(select, value)
    hidePanel()
  }

  select.onmousedown = (ev) => {
    ev.preventDefault()
    if (panel.hidden) {
      showPanel()
    } else {
      hidePanel()
    }
  }

  select.onkeydown = (ev) => {
    if (['ArrowDown', 'ArrowUp', ' ', 'Enter'].includes(ev.key)) {
      ev.preventDefault()
      showPanel()
    }
  }

  select.onchange = () => {
    const meta = selectSearchDataMap.get(select)
    if (!meta) return
    meta.input.value = ''
    meta.hidePanel()
  }

  select.dataset.searchBound = '1'
  
  storeSelectSearchOptions(select)
}

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

function updateSelectSearchResults(select: HTMLSelectElement, opts: { limit?: number; forceAll?: boolean } = {}): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  const { input, panel, list, options } = data
  if (!options || !options.length) {
    panel.hidden = true
    if (list) {
      while (list.firstChild) {
        list.removeChild(list.firstChild)
      }
    }
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
      if (list) {
        while (list.firstChild) {
          list.removeChild(list.firstChild)
        }
      }
      return
    }
    
    if (list) {
      while (list.firstChild) {
        list.removeChild(list.firstChild)
      }
      const empty = document.createElement('div')
      empty.className = 'select-search__empty'
      empty.textContent = 'Nenhum resultado encontrado'
      list.appendChild(empty)
    }
    panel.hidden = false
    return
  }

  if (list) {
    while (list.firstChild) {
      list.removeChild(list.firstChild)
    }
    
    finalList.forEach((opt) => {
      const button = document.createElement('button')
      button.type = 'button'
      button.className = 'select-search__item'
      button.setAttribute('data-value', opt.value)
      button.textContent = opt.label
      list.appendChild(button)
    })
  }
  
  panel.hidden = false
}

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

export function syncSelectSearchOptions(select: HTMLSelectElement): void {
  const data = selectSearchDataMap.get(select)
  if (!data) return

  storeSelectSearchOptions(select)
  updateSelectSearchResults(select)
}

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

export function useSelectSearch() {
  onMounted(() => {
    initAllSelectSearch()
    
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
