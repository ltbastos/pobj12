import { ref, computed, onBeforeUnmount } from 'vue'

let fullscreenKeydownHandler: ((e: KeyboardEvent) => void) | null = null

export function useOmegaFullscreen() {
  const isFullscreen = ref(false)
  function setOmegaFullscreen(on?: boolean) {
    const root = document.getElementById('omega-modal')
    if (!root) {
      console.warn('⚠️ Modal não encontrado para fullscreen')
      return
    }

    const btn = root.querySelector('[data-omega-fullscreen-toggle]') as HTMLElement
    if (!btn) {
      console.warn('⚠️ Botão de fullscreen não encontrado')
      // Tenta encontrar pelo ID também
      const btnById = root.querySelector('#omega-fullscreen') as HTMLElement
      if (btnById) {
      }
    }

    const next = (typeof on === 'boolean') ? on : !root.classList.contains('omega-modal--fullscreen')

    root.classList.toggle('omega-modal--fullscreen', next)
    isFullscreen.value = next

    if (btn) {
      btn.setAttribute('aria-pressed', next ? 'true' : 'false')
      btn.setAttribute('aria-label', next ? 'Sair de tela cheia' : 'Entrar em tela cheia')
      const icon = btn.querySelector('i')
      if (icon) icon.className = next ? 'ti ti-arrows-minimize' : 'ti ti-arrows-maximize'
    }

  }

  function bindOmegaFullscreenControls(root: HTMLElement) {
    // Remove listener anterior se existir
    if (fullscreenKeydownHandler) {
      document.removeEventListener('keydown', fullscreenKeydownHandler)
      fullscreenKeydownHandler = null
    }

    // Tenta encontrar o botão pelo atributo data ou pelo ID
    const fsBtn = root.querySelector('[data-omega-fullscreen-toggle]') as HTMLElement ||
                  root.querySelector('#omega-fullscreen') as HTMLElement

    if (fsBtn) {
      fsBtn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        setOmegaFullscreen()
      })
    } else {
      console.warn('⚠️ Botão de fullscreen não encontrado!')
    }

    const header = root.querySelector('.omega-header')
    if (header) {
      header.addEventListener('dblclick', () => {
        setOmegaFullscreen()
      })
    }

    fullscreenKeydownHandler = (ev: KeyboardEvent) => {
      // Só reage se o popup estiver visível
      if (!root || root.hidden) return
      if ((ev.key || '').toLowerCase() === 'f') {
        setOmegaFullscreen()
        ev.preventDefault()
      }
      // ESC sai do fullscreen antes de fechar o modal
      if (ev.key === 'Escape' && root.classList.contains('omega-modal--fullscreen')) {
        setOmegaFullscreen(false)
        ev.stopPropagation()
      }
    }

    document.addEventListener('keydown', fullscreenKeydownHandler, { passive: false })
  }

  function cleanup() {
    if (fullscreenKeydownHandler) {
      document.removeEventListener('keydown', fullscreenKeydownHandler)
      fullscreenKeydownHandler = null
    }
  }

  onBeforeUnmount(() => {
    cleanup()
  })

  return {
    isFullscreen: computed(() => isFullscreen.value),
    setOmegaFullscreen,
    bindOmegaFullscreenControls,
    cleanup
  }
}

