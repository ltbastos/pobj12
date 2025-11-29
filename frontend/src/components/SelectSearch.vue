<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import type { FilterOption } from '../types'

interface Props {
  modelValue: string
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
const searchTerm = ref('')
const wrapperRef = ref<HTMLElement | null>(null)
const inputRef = ref<HTMLInputElement | null>(null)

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
 * Opções filtradas baseadas no termo de busca
 */
const filteredOptions = computed(() => {
  if (!searchTerm.value) {
    return props.options.slice(0, 10) // Mostra apenas as primeiras 10 quando não há busca
  }
  
  const term = simplificarTexto(searchTerm.value)
  return props.options
    .filter(opt => simplificarTexto(opt.label).includes(term))
    .slice(0, 12) // Limita a 12 resultados
})

/**
 * Label do valor selecionado
 */
const selectedLabel = computed(() => {
  if (!props.modelValue) return props.placeholder
  const selected = props.options.find(opt => opt.id === props.modelValue)
  return selected?.label || props.placeholder
})

/**
 * Abre/fecha o dropdown
 */
const toggleDropdown = (): void => {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    searchTerm.value = ''
    setTimeout(() => {
      inputRef.value?.focus()
    }, 0)
  }
}

/**
 * Seleciona uma opção
 */
const selectOption = (option: FilterOption): void => {
  emit('update:modelValue', option.id)
  isOpen.value = false
  searchTerm.value = ''
}

/**
 * Fecha o dropdown ao clicar fora
 */
const handleClickOutside = (event: MouseEvent): void => {
  if (wrapperRef.value && !wrapperRef.value.contains(event.target as Node)) {
    isOpen.value = false
    searchTerm.value = ''
  }
}

/**
 * Trata teclas do teclado
 */
const handleKeydown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape') {
    isOpen.value = false
    searchTerm.value = ''
  } else if (event.key === 'Enter' && filteredOptions.value.length > 0) {
    event.preventDefault()
    const firstOption = filteredOptions.value[0]
    if (firstOption) {
      selectOption(firstOption)
    }
  } else if (['ArrowDown', 'ArrowUp', ' '].includes(event.key) && !isOpen.value) {
    event.preventDefault()
    isOpen.value = true
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Fecha quando desabilitado
watch(() => props.disabled, (disabled) => {
  if (disabled) {
    isOpen.value = false
  }
})
</script>

<template>
  <div ref="wrapperRef" class="select-search" :class="{ 'is-open': isOpen }">
    <select
      :id="id"
      :value="modelValue"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      @mousedown.prevent="toggleDropdown"
      @keydown="handleKeydown"
      class="input"
      :disabled="disabled"
      :data-search="true"
      style="display: none;"
    >
      <option v-if="!modelValue" value="">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.id" :value="option.id">
        {{ option.label }}
      </option>
    </select>
    
    <!-- Select visual customizado -->
    <div
      class="select-search__trigger"
      :class="{ 'is-disabled': disabled }"
      @click="toggleDropdown"
      @keydown="handleKeydown"
      tabindex="0"
      role="combobox"
      :aria-expanded="isOpen"
      :aria-haspopup="true"
    >
      <span :class="{ 'is-placeholder': !modelValue }">{{ selectedLabel }}</span>
      <i class="ti ti-chevron-down" :class="{ 'is-open': isOpen }"></i>
    </div>

    <!-- Painel de busca -->
    <div
      v-if="isOpen"
      class="select-search__panel"
      role="listbox"
      :aria-label="label || 'Opções'"
    >
      <div class="select-search__box">
        <input
          ref="inputRef"
          v-model="searchTerm"
          type="search"
          class="input input--xs select-search__input"
          :placeholder="`Pesquisar ${label?.toLowerCase() || 'opção'}`"
          :aria-label="`Pesquisar ${label || 'opção'}`"
        />
      </div>
      <div class="select-search__results">
        <button
          v-if="filteredOptions.length === 0"
          type="button"
          class="select-search__empty"
          disabled
        >
          Nenhum resultado encontrado
        </button>
        <button
          v-for="option in filteredOptions"
          :key="option.id"
          type="button"
          class="select-search__item"
          :class="{ 'is-selected': option.id === modelValue }"
          @click="selectOption(option)"
        >
          {{ option.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.select-search {
  position: relative;
  width: 100%;
}

.select-search.is-open {
  z-index: 2200;
}

.select-search__trigger {
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

.select-search__trigger:hover:not(.is-disabled) {
  border-color: #cbd5e1;
}

.select-search__trigger.is-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.select-search.is-open .select-search__trigger {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.select-search__trigger span.is-placeholder {
  color: var(--muted, #6b7280);
}

.select-search__trigger i {
  font-size: 16px;
  color: var(--muted, #6b7280);
  transition: transform 0.18s ease;
}

.select-search__trigger i.is-open {
  transform: rotate(180deg);
}

.select-search__panel {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid var(--stroke, #e7aaf2);
  border-radius: 12px;
  box-shadow: 0 12px 28px rgba(17, 23, 41, 0.12);
  z-index: 2600;
  padding: 10px 10px 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.select-search__box {
  display: flex;
}

.select-search__input {
  padding: 8px 10px;
  border-radius: 10px;
  border: 1px solid var(--stroke, #e7aaf2);
  background: #fff;
  color: var(--text, #0f1424);
  width: 100%;
  font-size: 12px;
}

.select-search__input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  border-color: #cbd5e1;
}

.select-search__results {
  max-height: 220px;
  overflow-y: auto;
  border-radius: 10px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: #fff;
  padding: 4px 0;
}

.select-search__item {
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
}

.select-search__item:hover,
.select-search__item:focus {
  background: rgba(36, 107, 253, 0.08);
  outline: none;
}

.select-search__item.is-selected {
  background: rgba(37, 99, 235, 0.12);
  font-weight: 600;
}

.select-search__empty {
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

