import { ref, type Ref } from 'vue'

export function useFeedback() {
  const isLoading = ref(false)
  const isSuccess = ref(false)
  const isError = ref(false)
  const message = ref<string | null>(null)

  const setLoading = (loading: boolean) => {
    isLoading.value = loading
    if (loading) {
      isSuccess.value = false
      isError.value = false
    }
  }

  const setSuccess = (msg?: string) => {
    isSuccess.value = true
    isError.value = false
    isLoading.value = false
    if (msg) message.value = msg
    
    setTimeout(() => {
      isSuccess.value = false
      message.value = null
    }, 3000)
  }

  const setError = (msg?: string) => {
    isError.value = true
    isSuccess.value = false
    isLoading.value = false
    if (msg) message.value = msg
    
    setTimeout(() => {
      isError.value = false
      message.value = null
    }, 5000)
  }

  const reset = () => {
    isLoading.value = false
    isSuccess.value = false
    isError.value = false
    message.value = null
  }

  return {
    isLoading,
    isSuccess,
    isError,
    message,
    setLoading,
    setSuccess,
    setError,
    reset
  }
}

export function useRipple(element: Ref<HTMLElement | null>) {
  const createRipple = (event: MouseEvent) => {
    const button = element.value
    if (!button) return

    const circle = document.createElement('span')
    const diameter = Math.max(button.clientWidth, button.clientHeight)
    const radius = diameter / 2

    const rect = button.getBoundingClientRect()
    circle.style.width = circle.style.height = `${diameter}px`
    circle.style.left = `${event.clientX - rect.left - radius}px`
    circle.style.top = `${event.clientY - rect.top - radius}px`
    circle.classList.add('ripple')

    const ripple = button.getElementsByClassName('ripple')[0]
    if (ripple) {
      ripple.remove()
    }

    button.appendChild(circle)

    setTimeout(() => {
      circle.remove()
    }, 600)
  }

  return { createRipple }
}

export function usePulse() {
  const pulse = (element: HTMLElement) => {
    element.classList.add('pulse-animation')
    setTimeout(() => {
      element.classList.remove('pulse-animation')
    }, 600)
  }

  return { pulse }
}

export function useShake() {
  const shake = (element: HTMLElement) => {
    element.classList.add('shake-animation')
    setTimeout(() => {
      element.classList.remove('shake-animation')
    }, 600)
  }

  return { shake }
}

