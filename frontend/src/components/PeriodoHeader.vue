<template>
  <div id="lbl-atualizacao">
    <div class="period-inline">
      <span class="txt">
        De <strong>{{ startFormatted }}</strong>
        até <strong>{{ endFormatted }}</strong>
      </span>

      <button
        ref="anchor"
        class="link-action"
        :disabled="isSimuladoresPage"
        @click="openPopover"
      >
        <Icon name="chevron-down" :size="16" /> Alterar data
      </button>
    </div>

    <DatePopover
      :open="popoverOpen"
      :anchor="anchor"
      :modelValue="period"
      @update:modelValue="updatePeriod"
      @close="popoverOpen = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DatePopover from './DatePopover.vue'
import Icon from './Icon.vue'
import { usePeriodManager } from '../composables/usePeriodManager'

const {
  period,
  startFormatted,
  endFormatted,
  isSimuladoresPage,
  updatePeriod,
} = usePeriodManager()

const popoverOpen = ref(false)
const anchor = ref<HTMLElement | null>(null)

const openPopover = (e: Event) => {
  
  if (popoverOpen.value) {
    popoverOpen.value = false
    return
  }
  
  anchor.value = e.currentTarget as HTMLElement
  popoverOpen.value = true
}
</script>

<style scoped>
.period-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.period-inline .txt {
  font-size: 13px;
  color: var(--muted, #6b7280);
  font-family: var(--brad-font-family, inherit);
}

.period-inline strong {
  color: var(--text, #0f1424);
  font-weight: var(--brad-font-weight-bold, 700);
  font-family: var(--brad-font-family, inherit);
}
</style>

<style>
.link-action {
  display: inline-flex !important;
  align-items: center;
  gap: 6px;
  background: var(--panel, #fff) !important;
  border: 1px solid var(--stroke, #e7eaf2) !important;
  color: var(--brand, #cc092f) !important;
  font-size: 13px !important;
  font-weight: var(--brad-font-weight-bold, 700) !important;
  cursor: pointer;
  padding: 8px 14px !important;
  border-radius: 10px !important;
  transition: all 0.2s ease;
  box-sizing: border-box;
  font-family: var(--brad-font-family, inherit) !important;
  outline: none;
  text-decoration: none;
  margin: 0;
  line-height: 1.5;
}

.link-action:hover:not(:disabled) {
  background: var(--brand-xlight, rgba(204, 9, 47, 0.08)) !important;
  border-color: var(--brand, #cc092f) !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(204, 9, 47, 0.15);
}

.link-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

</style>

