<script setup lang="ts">
import Icon from './Icon.vue'

interface Props {
  title?: string
  message: string
  icon?: string
}

withDefaults(defineProps<Props>(), {
  title: 'Erro ao carregar dados',
  icon: 'alert-circle'
})
</script>

<template>
  <div class="error-state">
    <div class="error-state__content">
      <Icon :name="icon" :size="48" />
      <h3 class="error-state__title">{{ title }}</h3>
      <p class="error-state__message">{{ message }}</p>
    </div>
  </div>
</template>

<style scoped>
.error-state {
  padding: 48px 24px;
  text-align: center;
  color: var(--muted, #6b7280);
  background: var(--panel, #ffffff);
  border: 1px solid var(--stroke, #e7eaf2);
  border-radius: var(--radius, 16px);
  box-shadow: var(--shadow, 0 12px 28px rgba(17, 23, 41, 0.08));
  margin: 16px;
}

.error-state__content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  max-width: 500px;
  margin: 0 auto;
}

.error-state__content :deep(i) {
  color: var(--brand, #cc092f);
  opacity: 0.8;
}

.error-state__title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: var(--brand, #cc092f);
}

.error-state__message {
  margin: 0;
  font-size: 14px;
  color: var(--muted, #6b7280);
  line-height: 1.6;
}
</style>
