import { onMounted, ref } from 'vue'

export function useAnimations() {
  const fadeInUp = {
    initial: { opacity: 0, y: 20 },
    enter: { opacity: 1, y: 0 },
    transition: { duration: 0.4, ease: [0.25, 0.1, 0.25, 1] }
  }

  const fadeInDown = {
    initial: { opacity: 0, y: -20 },
    enter: { opacity: 1, y: 0 },
    transition: { duration: 0.4, ease: [0.25, 0.1, 0.25, 1] }
  }

  const fadeIn = {
    initial: { opacity: 0 },
    enter: { opacity: 1 },
    transition: { duration: 0.3, ease: 'easeOut' }
  }

  const scaleIn = {
    initial: { opacity: 0, scale: 0.95 },
    enter: { opacity: 1, scale: 1 },
    transition: { duration: 0.3, ease: [0.25, 0.1, 0.25, 1] }
  }

  const slideInRight = {
    initial: { opacity: 0, x: 20 },
    enter: { opacity: 1, x: 0 },
    transition: { duration: 0.3, ease: [0.25, 0.1, 0.25, 1] }
  }

  const slideInLeft = {
    initial: { opacity: 0, x: -20 },
    enter: { opacity: 1, x: 0 },
    transition: { duration: 0.3, ease: [0.25, 0.1, 0.25, 1] }
  }

  const hoverScale = {
    hover: { scale: 1.05 },
    tap: { scale: 0.98 }
  }

  const staggerChildren = (delay: number = 0.05) => ({
    transition: {
      staggerChildren: delay
    }
  })

  return {
    fadeInUp,
    fadeInDown,
    fadeIn,
    scaleIn,
    slideInRight,
    slideInLeft,
    hoverScale,
    staggerChildren
  }
}

export function useStaggeredAnimation(delay: number = 0) {
  const isVisible = ref(false)

  onMounted(() => {
    setTimeout(() => {
      isVisible.value = true
    }, delay)
  })

  return {
    isVisible,
    animationProps: {
      initial: { opacity: 0, y: 20 },
      enter: { opacity: 1, y: 0 },
      transition: { duration: 0.4, delay, ease: [0.25, 0.1, 0.25, 1] }
    }
  }
}

