export function animateElement(
  element: HTMLElement,
  keyframes: Keyframe[] | PropertyIndexedKeyframes,
  options?: KeyframeAnimationOptions
): Animation {
  return element.animate(keyframes, {
    duration: 300,
    easing: 'cubic-bezier(0.25, 0.1, 0.25, 1)',
    fill: 'forwards',
    ...options
  })
}

export function fadeIn(element: HTMLElement, duration: number = 300): Animation {
  return animateElement(
    element,
    [
      { opacity: 0 },
      { opacity: 1 }
    ],
    { duration }
  )
}

export function fadeOut(element: HTMLElement, duration: number = 300): Animation {
  return animateElement(
    element,
    [
      { opacity: 1 },
      { opacity: 0 }
    ],
    { duration }
  )
}

export function slideInRight(element: HTMLElement, duration: number = 300): Animation {
  return animateElement(
    element,
    [
      { opacity: 0, transform: 'translateX(20px)' },
      { opacity: 1, transform: 'translateX(0)' }
    ],
    { duration }
  )
}

export function slideInLeft(element: HTMLElement, duration: number = 300): Animation {
  return animateElement(
    element,
    [
      { opacity: 0, transform: 'translateX(-20px)' },
      { opacity: 1, transform: 'translateX(0)' }
    ],
    { duration }
  )
}

export function scaleIn(element: HTMLElement, duration: number = 300): Animation {
  return animateElement(
    element,
    [
      { opacity: 0, transform: 'scale(0.95)' },
      { opacity: 1, transform: 'scale(1)' }
    ],
    { duration }
  )
}

export function bounce(element: HTMLElement, duration: number = 600): Animation {
  return animateElement(
    element,
    [
      { transform: 'scale(1)' },
      { transform: 'scale(1.1)', offset: 0.5 },
      { transform: 'scale(1)' }
    ],
    { duration, easing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)' }
  )
}

export function shimmer(element: HTMLElement, duration: number = 2000): Animation {
  return animateElement(
    element,
    [
      { backgroundPosition: '-1000px 0' },
      { backgroundPosition: '1000px 0' }
    ],
    { duration, iterations: Infinity, easing: 'linear' }
  )
}

export function staggerChildren(
  parent: HTMLElement,
  selector: string,
  animationFn: (el: HTMLElement) => Animation,
  delay: number = 50
): Animation[] {
  const children = Array.from(parent.querySelectorAll(selector)) as HTMLElement[]
  return children.map((child, index) => {
    const anim = animationFn(child)
    anim.startTime = (anim.startTime as number) + (index * delay)
    return anim
  })
}
