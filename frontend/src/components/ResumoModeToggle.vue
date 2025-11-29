<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

const props = defineProps<{
  modelValue?: 'cards' | 'legacy'
}>()

const emit = defineEmits<{
  'update:modelValue': [value: 'cards' | 'legacy']
}>()

const mode = ref<'cards' | 'legacy'>(props.modelValue || 'cards')

// Sincroniza quando o prop mudar externamente
watch(() => props.modelValue, (newValue) => {
  if (newValue && (newValue === 'cards' || newValue === 'legacy')) {
    mode.value = newValue
  }
})

const setMode = (newMode: 'cards' | 'legacy') => {
  mode.value = newMode
  emit('update:modelValue', newMode)
  // Persiste no localStorage
  if (typeof window !== 'undefined') {
    localStorage.setItem('resumo-mode', newMode)
  }
}

onMounted(() => {
  // Carrega do localStorage se disponível e não houver prop
  if (typeof window !== 'undefined' && !props.modelValue) {
    const saved = localStorage.getItem('resumo-mode') as 'cards' | 'legacy' | null
    if (saved && (saved === 'cards' || saved === 'legacy')) {
      mode.value = saved
      emit('update:modelValue', saved)
    }
  } else if (props.modelValue) {
    mode.value = props.modelValue
  }
})
</script>

<template>
  <div class="resumo-mode">
    <div class="resumo-mode__toggle" role="group" aria-label="Alterar visão do resumo">
      <div class="segmented">
        <button
          type="button"
          class="seg-btn"
          :class="{ 'is-active': mode === 'cards' }"
          :aria-pressed="mode === 'cards'"
          @click="setMode('cards')"
        >
          Visão por cards
        </button>
        <button
          type="button"
          class="seg-btn"
          :class="{ 'is-active': mode === 'legacy' }"
          :aria-pressed="mode === 'legacy'"
          @click="setMode('legacy')"
        >
          Visão clássica
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.resumo-mode {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}

.resumo-mode__toggle .segmented {
  background: #eef2ff;
  border: 1px solid #d7def3;
  display: inline-flex;
  border-radius: 10px;
  padding: 3px;
  gap: 0;
}

.seg-btn {
  border: 0;
  background: transparent;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.seg-btn:hover {
  background: rgba(255, 255, 255, 0.5);
}

.seg-btn.is-active {
  background: #fff;
  color: #111827;
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.18);
}

.seg-btn:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>

