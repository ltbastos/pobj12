<script setup lang="ts">
import { ref } from 'vue'

interface Props {
  variant?: 'primary' | 'secondary' | 'link' | 'info'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  icon?: string
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'secondary',
  type: 'button',
  disabled: false,
  icon: undefined,
  loading: false
})

defineSlots<{
  default(): unknown
}>()

const buttonRef = ref<HTMLElement | null>(null)

const handleClick = (event: MouseEvent) => {
  if (props.disabled || props.loading) return
  
  const button = event.currentTarget as HTMLElement
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
</script>

<template>
  <button
    ref="buttonRef"
    :type="type"
    :disabled="disabled || loading"
    :class="['btn', `btn--${variant}`, { 'btn--loading': loading }]"
    :aria-busy="loading"
    :aria-disabled="disabled || loading"
    v-bind="$attrs"
    @click="handleClick"
    @keydown.enter.prevent="!disabled && !loading && handleClick($event as any)"
    @keydown.space.prevent="!disabled && !loading && handleClick($event as any)"
  >
    <span v-if="loading" class="btn__spinner" aria-hidden="true"></span>
    <i v-if="icon && !loading" :class="icon" aria-hidden="true"></i>
    <span v-if="!loading"><slot /></span>
    <span v-else class="btn__loading-text" aria-live="polite" aria-atomic="true"><slot /></span>
  </button>
</template>

<style scoped>
.btn {
  --brand: #b30000;
  --brand-dark: #8f0000;
  --stroke: #e7eaf2;
  --text: #0f1424;

  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid var(--stroke);
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
  font-family: inherit;
  transform: scale(1);
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn:active::before {
  width: 300px;
  height: 300px;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn--primary {
  background: linear-gradient(90deg, #cc092f 40%, #b81570 90%);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.25);
}

.btn--primary i {
  color: #fff;
  stroke-width: 1.5;
}

.btn--primary:hover:not(:disabled) {
  background: linear-gradient(90deg, #b81570 40%, #cc092f 90%);
  box-shadow: 0 6px 20px rgba(204, 9, 47, 0.4);
  transform: translateY(-2px) scale(1.02);
}

.btn--primary:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
  box-shadow: 0 2px 8px rgba(204, 9, 47, 0.3);
}

.btn--primary:hover:not(:disabled) i {
  color: #fff;
}

.btn--secondary:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.04);
  border-color: rgba(0, 0, 0, 0.12);
  transform: translateY(-1px);
}

.btn--secondary:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

.btn--link {
  background: transparent;
  border: none;
  color: var(--brand);
  padding: 8px 12px;
  box-shadow: none;
}

.btn--link:hover:not(:disabled) {
  background: rgba(204, 9, 47, 0.08);
  transform: translateX(2px);
}

.btn--link:active:not(:disabled) {
  transform: translateX(0);
}

.btn--info {
  background: transparent;
  border: 1.5px solid var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
  font-weight: var(--brad-font-weight-semibold, 600);
}

.btn--info i {
  color: var(--brad-color-primary, #cc092f);
  stroke-width: 1.5;
  transition: transform 0.2s ease;
}

.btn--info:hover:not(:disabled) {
  background: rgba(204, 9, 47, 0.08);
  border-color: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-primary, #cc092f);
  transform: translateY(-1px);
}

.btn--info:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

.btn--info:hover:not(:disabled) i {
  color: var(--brad-color-primary, #cc092f);
}

.btn--loading {
  position: relative;
  pointer-events: none;
}

.btn__spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: currentColor;
  animation: spin 0.6s linear infinite;
  margin-right: 8px;
}

.btn--secondary .btn__spinner {
  border-color: rgba(0, 0, 0, 0.2);
  border-top-color: currentColor;
}

.btn__loading-text {
  opacity: 0.7;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

