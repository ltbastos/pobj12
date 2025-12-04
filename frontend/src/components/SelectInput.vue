<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import Icon from './Icon.vue'
import type { FilterOption } from '../types'

type Props = {
  modelValue: string | null
  options: FilterOption[]
  placeholder?: string
  label?: string
  disabled?: boolean
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Selecione...',
  label: '',
  disabled: false,
  id: ''
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const isOpen = ref(false)
const wrapperRef = ref<HTMLElement | null>(null)

const selectedLabel = computed(() => {
  if (!props.modelValue) return props.placeholder
  const selected = props.options.find(opt => opt.id === props.modelValue)
  return selected?.nome || props.placeholder
})

const toggleDropdown = (): void => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}

const selectOption = (option: FilterOption): void => {
  emit('update:modelValue', option.id)
  isOpen.value = false
}

const handleKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape') {
    isOpen.value = false
  } else if (event.key === 'Enter' && props.options.length > 0 && isOpen.value) {
    event.preventDefault()
    const firstOption = props.options[0]
    if (firstOption) {
      selectOption(firstOption)
    }
  } else if (['ArrowDown', 'ArrowUp', ' '].includes(event.key) && !isOpen.value) {
    event.preventDefault()
    isOpen.value = true
  } else if (event.key === 'ArrowDown' && isOpen.value) {
    event.preventDefault()
    focusNextOption()
  } else if (event.key === 'ArrowUp' && isOpen.value) {
    event.preventDefault()
    focusPreviousOption()
  }
}

const focusNextOption = (): void => {
  const items = wrapperRef.value?.querySelectorAll('.select__item') as NodeListOf<HTMLElement>
  if (!items || items.length === 0) return
  
  const currentIndex = Array.from(items).findIndex(item => item === document.activeElement)
  const nextIndex = currentIndex < items.length - 1 ? currentIndex + 1 : 0
  items[nextIndex]?.focus()
}

const focusPreviousOption = (): void => {
  const items = wrapperRef.value?.querySelectorAll('.select__item') as NodeListOf<HTMLElement>
  if (!items || items.length === 0) return
  
  const currentIndex = Array.from(items).findIndex(item => item === document.activeElement)
  const prevIndex = currentIndex > 0 ? currentIndex - 1 : items.length - 1
  items[prevIndex]?.focus()
}

const useClickOutside = (callback: () => void) => {
  const handleClick = (event: MouseEvent) => {
    if (wrapperRef.value && !wrapperRef.value.contains(event.target as Node)) {
      callback()
    }
  }
  
  onMounted(() => {
    document.addEventListener('click', handleClick)
  })
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClick)
  })
}

useClickOutside(() => {
  isOpen.value = false
})

watch(() => props.disabled, (disabled) => {
  if (disabled) {
    isOpen.value = false
  }
})
</script>

<template>
  <div ref="wrapperRef" class="select" :class="{ 'is-open': isOpen }">
    <select
      :id="id"
      :value="modelValue"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      @mousedown.prevent="toggleDropdown"
      @keydown="handleKeydown"
      class="input"
      :disabled="disabled"
      style="display: none;"
    >
      <option v-if="!modelValue" value="">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.id" :value="option.id">
        {{ option.nome }}
      </option>
    </select>
    
    <div
      class="select__trigger"
      :class="{ 'is-disabled': disabled }"
      @click="toggleDropdown"
      @keydown="handleKeydown"
      tabindex="0"
      role="combobox"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      :aria-controls="`${id}-listbox`"
      :aria-label="label || 'Selecione uma opção'"
    >
      <span :class="{ 'is-placeholder': !modelValue }" :aria-label="modelValue ? selectedLabel : placeholder">{{ selectedLabel }}</span>
      <Icon name="chevron-down" :size="16" :class="{ 'is-open': isOpen }" />
    </div>

    <div
      v-if="isOpen"
      :id="`${id}-listbox`"
      class="select__panel"
      role="listbox"
      :aria-label="label || 'Opções'"
      @keydown.escape="isOpen = false"
    >
      <div class="select__results" role="group" :aria-label="`${options.length} opções disponíveis`">
        <div
          v-if="options.length === 0"
          class="select__empty"
          role="status"
          aria-live="polite"
        >
          Nenhuma opção disponível
        </div>
        <button
          v-for="(option, index) in options"
          :key="option.id"
          type="button"
          class="select__item"
          :class="{ 'is-selected': option.id === modelValue }"
          role="option"
          :aria-selected="option.id === modelValue"
          :tabindex="index === 0 ? 0 : -1"
          @click="selectOption(option)"
          @keydown.enter.prevent="selectOption(option)"
          @keydown.space.prevent="selectOption(option)"
        >
          {{ option.nome }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.select {
  position: relative;
  width: 100%;
}

.select.is-open {
  z-index: 2200;
}

.select__trigger {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 10px;
  background: #fff;
  color: var(--text, #0f1424);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
  font-size: 13px;
}

.select__trigger:hover:not(.is-disabled) {
  border-color: #cbd5e1;
}

.select__trigger.is-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.select.is-open .select__trigger {
  border-color: var(--brand, #cc092f);
  box-shadow: 0 0 0 3px var(--brand-xlight, rgba(204, 9, 47, 0.12));
}

.select__trigger span.is-placeholder {
  color: var(--muted, #6b7280);
}

.select__trigger i {
  font-size: 16px;
  color: var(--muted, #6b7280);
  transition: transform 0.18s ease;
}

.select__trigger i.is-open {
  transform: rotate(180deg);
}

.select__panel {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: var(--panel, #fff);
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: 12px;
  box-shadow: 0 12px 28px rgba(17, 23, 41, 0.12);
  z-index: 2600;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 0;
  font-family: var(--brad-font-family, inherit);
}

.select__results {
  max-height: 220px;
  overflow-y: auto;
  border-radius: 10px;
  border: 1px solid var(--stroke, #e7eaf2);
  background: var(--panel, #fff);
  padding: 4px 0;
}

.select__item {
  width: 100%;
  display: block;
  padding: 9px 12px;
  text-align: left;
  background: transparent;
  border: none;
  font-size: 13px;
  cursor: pointer;
  color: var(--text, #0f1424);
  transition: background-color 0.15s ease;
  font-family: var(--brad-font-family, inherit);
}

.select__item:hover,
.select__item:focus {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  outline: none;
}

.select__item.is-selected {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.12));
  font-weight: var(--brad-font-weight-semibold, 600);
  color: var(--brand, #cc092f);
}

.select__empty {
  padding: 9px 12px;
  font-size: 13px;
  color: var(--muted, #6b7280);
  text-align: center;
  width: 100%;
  background: transparent;
  border: none;
  cursor: default;
}
</style>

