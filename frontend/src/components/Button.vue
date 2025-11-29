<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'link' | 'info'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  icon?: string
}

withDefaults(defineProps<Props>(), {
  variant: 'secondary',
  type: 'button',
  disabled: false,
  icon: undefined
})

defineSlots<{
  default(): unknown
}>()
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="['btn', `btn--${variant}`]"
    v-bind="$attrs"
  >
    <i v-if="icon" :class="icon" aria-hidden="true"></i>
    <slot />
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
  transition: all 0.2s ease;
  font-family: inherit;
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
  box-shadow: 0 6px 16px rgba(204, 9, 47, 0.35);
  transform: translateY(-1px);
}

.btn--primary:hover:not(:disabled) i {
  color: #fff;
}

.btn--secondary:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.04);
  border-color: rgba(0, 0, 0, 0.12);
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
  transform: none;
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
}

.btn--info:hover:not(:disabled) i {
  color: var(--brad-color-primary, #cc092f);
}
</style>

