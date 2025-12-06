<script setup lang="ts">
import { ref } from 'vue'
import ResumoModeToggle from '../components/ResumoModeToggle.vue'
import ResumoKPI from '../components/ResumoKPI.vue'
import ProdutosCards from '../components/ProdutosCards.vue'
import ResumoLegacy from '../components/ResumoLegacy.vue'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { useResumoData } from '../composables/useResumoData'

const resumoMode = ref<'cards' | 'legacy'>('cards')
const { filterState, period } = useGlobalFilters()
const { loading } = useResumoData(filterState, period)
</script>

<template>
  <section id="view-cards" class="view-section">
    <template v-if="loading">
      <div class="resumo-skeleton">
        <ResumoModeToggle v-model="resumoMode" />
        
        <div id="resumo-summary" class="resumo-summary">
          <div class="kpi-summary">
            <div class="skeleton skeleton--kpi-card" style="height: 140px; border-radius: 12px;"></div>
            <div class="skeleton skeleton--kpi-card" style="height: 140px; border-radius: 12px;"></div>
            <div class="skeleton skeleton--kpi-card" style="height: 140px; border-radius: 12px;"></div>
          </div>
        </div>

        <div
          id="resumo-cards"
          class="resumo-mode__view"
          :class="{ hidden: resumoMode !== 'cards' }"
        >
          <div class="produtos-skeleton">
            <div class="skeleton skeleton--section-title" style="height: 28px; width: 200px; margin-bottom: 16px; border-radius: 6px;"></div>
            <div class="cards-grid">
              <div class="skeleton skeleton--card" style="height: 180px; border-radius: 12px;"></div>
              <div class="skeleton skeleton--card" style="height: 180px; border-radius: 12px;"></div>
              <div class="skeleton skeleton--card" style="height: 180px; border-radius: 12px;"></div>
              <div class="skeleton skeleton--card" style="height: 180px; border-radius: 12px;"></div>
            </div>
          </div>
        </div>

        <div
          id="resumo-legacy"
          class="resumo-mode__view"
          :class="{ hidden: resumoMode !== 'legacy' }"
        >
          <div class="skeleton skeleton--legacy" style="height: 400px; border-radius: 12px;"></div>
        </div>
      </div>
    </template>

    <template v-else>
      <ResumoModeToggle v-model="resumoMode" />

      <div id="resumo-summary" class="resumo-summary">
        <ResumoKPI />
      </div>

      <div
        id="resumo-cards"
        class="resumo-mode__view"
        :class="{ hidden: resumoMode !== 'cards' }"
      >
        <ProdutosCards />
      </div>

      <div
        id="resumo-legacy"
        class="resumo-mode__view"
        :class="{ hidden: resumoMode !== 'legacy' }"
        aria-live="polite"
      >
        <ResumoLegacy />
      </div>
    </template>
  </section>
</template>

<style scoped>
.view-section {
  margin-top: 24px;
  width: 100%;
}

.resumo-summary {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 18px;
}

.kpi-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.resumo-mode__view {
  width: 100%;
}

.resumo-mode__view.hidden {
  display: none;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}

@media (max-width: 768px) {
  .cards-grid {
    grid-template-columns: 1fr;
  }

  .resumo-summary {
    margin: 0 0 18px;
  }

  .kpi-summary {
    flex-wrap: nowrap;
    overflow-x: auto;
  }
}

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s ease-in-out infinite;
  border-radius: 8px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.resumo-skeleton .kpi-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin: 8px 0 14px;
  align-items: flex-start;
}

.resumo-skeleton .kpi-summary .skeleton--kpi-card {
  flex: 1 1 360px;
  min-width: 280px;
}

.resumo-skeleton .produtos-skeleton .cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
}
</style>

