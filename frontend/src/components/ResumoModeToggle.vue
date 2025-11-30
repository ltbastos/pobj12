<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

const props = defineProps<{
  modelValue?: 'cards' | 'legacy'
}>()

const emit = defineEmits<{
  'update:modelValue': [value: 'cards' | 'legacy']
}>()

const mode = ref<'cards' | 'legacy'>(props.modelValue || 'cards')

watch(() => props.modelValue, (newValue) => {
  if (newValue && (newValue === 'cards' || newValue === 'legacy')) {
    mode.value = newValue
  }
})

const setMode = (newMode: 'cards' | 'legacy') => {
  mode.value = newMode
  emit('update:modelValue', newMode)
  if (typeof window !== 'undefined') {
    localStorage.setItem('resumo-mode', newMode)
  }
}

onMounted(() => {
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
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08));
  border: 1px solid var(--stroke, #e7eaf2);
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
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text, #0f1424);
  transition: all 0.2s ease;
  white-space: nowrap;
  font-family: var(--brad-font-family, inherit);
}

.seg-btn:hover {
  background: rgba(255, 255, 255, 0.5);
}

.seg-btn.is-active {
  background: var(--panel, #fff);
  color: var(--text, #0f1424);
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.18);
}

.seg-btn:focus-visible {
  outline: 2px solid var(--brand, #cc092f);
  outline-offset: 2px;
}
</style>

