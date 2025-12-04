<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { getSimuladorProducts, type SimuladorProduct } from '../services/simuladorService'
import { formatPoints, formatByMetric, formatBRLReadable } from '../utils/formatUtils'
import SelectInput from '../components/SelectInput.vue'
import Icon from '../components/Icon.vue'
import type { FilterOption } from '../types'

const selectedIndicatorId = ref<string>('')
const delta = ref<number>(0)
const loading = ref(false)
const error = ref<string | null>(null)

const catalog = ref<SimuladorProduct[]>([])

const loadData = async () => {
  loading.value = true
  error.value = null
  
  try {
    const data = await getSimuladorProducts()
    if (data) {
      catalog.value = data
      if (catalog.value.length > 0 && !selectedIndicatorId.value) {
        selectedIndicatorId.value = catalog.value[0]?.id || ''
      }
    } else {
      error.value = 'Erro ao carregar produtos'
      catalog.value = []
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Erro desconhecido'
    console.error('Erro ao carregar produtos:', err)
    catalog.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})

const selectedProduct = computed(() => {
  return catalog.value.find(item => item.id === selectedIndicatorId.value) || null
})

const _groupedCatalog = computed(() => {
  const groups = new Map<string, SimuladorProduct[]>()
  catalog.value.forEach(item => {
    const key = item.sectionId || item.sectionLabel || '__'
    if (!groups.has(key)) {
      groups.set(key, [])
    }
    groups.get(key)!.push(item)
  })
  
  return Array.from(groups.entries()).map(([sectionId, items]) => ({
    sectionId,
    sectionLabel: items[0]?.sectionLabel || sectionId,
    items: items.sort((a, b) => a.label.localeCompare(b.label, 'pt-BR'))
  }))
})

const selectOptions = computed<FilterOption[]>(() => {
  return catalog.value
    .map(item => ({
      id: item.id,
      nome: `${item.sectionLabel} - ${item.label}`
    }))
    .sort((a, b) => a.nome.localeCompare(b.nome, 'pt-BR'))
})

const handleIndicatorChange = (value: string): void => {
  selectedIndicatorId.value = value
}

const toNumber = (value: any): number => {
  if (value === null || value === undefined || value === '') return 0
  const n = typeof value === 'string' ? parseFloat(value) : value
  return Number.isFinite(n) ? n : 0
}

const simulationResults = computed(() => {
  if (!selectedProduct.value) return null

  const product = selectedProduct.value
  const metric = product.metric || 'valor'
  const meta = Math.max(0, toNumber(product.meta))
  const realizado = Math.max(0, toNumber(product.realizado))
  const variavelMeta = Math.max(0, toNumber(product.variavelMeta || 0))
  const variavelReal = Math.max(0, toNumber(product.variavelReal || 0))
  const pontosMeta = Math.max(0, toNumber(product.pontosMeta))
  const pontosBrutos = Math.max(0, toNumber(product.pontosBrutos))
  const pontosAtual = Math.max(0, Math.min(pontosMeta || pontosBrutos, toNumber(product.pontos)))
  const deltaValue = Math.max(0, toNumber(delta.value))
  
  const novoReal = realizado + deltaValue
  const ratioVar = meta > 0 ? ((variavelMeta || variavelReal) / meta) : 0
  const ratioPontos = meta > 0 ? (pontosMeta / meta) : 0
  const variavelProjBruta = variavelReal + (deltaValue * ratioVar)
  const variavelProj = variavelMeta > 0 ? Math.min(variavelMeta, variavelProjBruta) : variavelProjBruta
  const variavelDelta = Math.max(0, variavelProj - variavelReal)
  const pontosProjBrutos = pontosMeta > 0 ? ratioPontos * novoReal : pontosAtual + (deltaValue * ratioPontos)
  const pontosProj = pontosMeta > 0 ? Math.max(0, Math.min(pontosMeta, pontosProjBrutos)) : Math.max(0, pontosProjBrutos)
  const pontosDelta = Math.max(0, pontosProj - pontosAtual)
  const atingAtual = meta > 0 ? (realizado / meta) * 100 : 0
  const atingProj = meta > 0 ? (novoReal / meta) * 100 : 0
  const atingDelta = Math.max(0, atingProj - atingAtual)

  return {
    metric,
    meta,
    realizado,
    novoReal,
    deltaValue,
    variavelAtual: variavelReal,
    variavelProj,
    variavelDelta,
    pontosAtual,
    pontosProj,
    pontosDelta,
    atingAtual,
    atingProj,
    atingDelta,
    variavelMeta,
    pontosMeta
  }
})

const shortcuts = computed(() => {
  if (!selectedProduct.value) return []
  const product = selectedProduct.value
  const meta = Math.max(0, toNumber(product.meta))
  if (!(meta > 0)) return []

  const gap = Math.max(0, meta - toNumber(product.realizado))
  return [
    { label: '+5% da meta', value: meta * 0.05 },
    { label: '+10% da meta', value: meta * 0.10 },
    { label: gap > 0 ? 'Fechar a meta' : '+25% da meta', value: gap > 0 ? gap : meta * 0.25 }
  ]
})

const formatCurrencyDelta = (value: number): string => {
  const formatted = formatBRLReadable(value)
  return value > 0 ? `+${formatted}` : formatted
}

const formatPointsDelta = (value: number): string => {
  const formatted = formatPoints(value, { withUnit: true })
  return value > 0 ? `+${formatted}` : formatted
}

const applyShortcut = (value: number) => {
  const product = selectedProduct.value
  if (!product) return
  
  const step = product.metric === 'valor' ? 1000 : 1
  const nextValue = Math.max(0, Math.round(value / step) * step)
  delta.value = nextValue
}

const handleDeltaBlur = (): void => {
  if (delta.value > 0 && selectedProduct.value) {
    const step = selectedProduct.value.metric === 'valor' ? 1000 : 1
    delta.value = Math.round(delta.value / step) * step
  }
}

const handleDeltaFocus = (event: FocusEvent): void => {
  const target = event.target as HTMLInputElement
  target.select()
}

watch(selectedIndicatorId, () => {
  delta.value = 0
})

const deltaDisplay = computed({
  get: () => {
    if (!delta.value || delta.value === 0) return ''
    return new Intl.NumberFormat('pt-BR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(delta.value)
  },
  set: (value: string) => {
    let cleaned = value.replace(/[^\d,.]/g, '')
    
    if (cleaned.includes(',')) {
      cleaned = cleaned.replace(/\./g, '').replace(',', '')
    } else if (cleaned.includes('.')) {
      const parts = cleaned.split('.')
      if (parts.length === 2 && parts[1]?.length && parts[1]?.length <= 2) {
        cleaned = cleaned.replace('.', '')
      } else {
        cleaned = cleaned.replace(/\./g, '')
      }
    }
    
    const num = cleaned ? parseFloat(cleaned) : 0
    delta.value = isNaN(num) ? 0 : Math.max(0, Math.round(num))
  }
})

const formatDeltaDisplay = (value: number): string => {
  if (!value || value === 0) return '0'
  if (selectedProduct.value?.metric === 'valor') {
    return formatBRLReadable(value)
  } else {
    return new Intl.NumberFormat('pt-BR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value) + ' un'
  }
}
</script>

<template>
  <div class="simuladores-wrapper">
    <div class="simuladores-view">
        <section class="card card--simuladores">
          <header class="card__header">
            <div class="title-subtitle">
              <h3>Simulador "E se?"</h3>
              <p class="muted">Simule o impacto de incrementos em indicadores específicos.</p>
            </div>
          </header>

          <div id="sim-whatif" class="sim-whatif">
            <!-- Skeleton Loading -->
            <template v-if="loading">
              <div class="sim-whatif__skeleton">
                <div class="sim-whatif__form">
                  <div class="sim-whatif__field">
                    <div class="skeleton skeleton--label"></div>
                    <div class="skeleton skeleton--input"></div>
                  </div>
                  <div class="sim-whatif__field">
                    <div class="skeleton skeleton--label"></div>
                    <div class="skeleton skeleton--input"></div>
                    <div class="skeleton skeleton--hint"></div>
                  </div>
                </div>
                <div class="skeleton skeleton--shortcuts"></div>
                <div class="sim-whatif__results">
                  <div class="skeleton skeleton--title"></div>
                  <div class="skeleton skeleton--subtitle"></div>
                  <div class="sim-whatif-cards">
                    <div class="skeleton skeleton--card"></div>
                    <div class="skeleton skeleton--card"></div>
                    <div class="skeleton skeleton--card"></div>
                  </div>
                </div>
              </div>
            </template>

            <template v-else>
              <form id="sim-whatif-form" class="sim-whatif__form" @submit.prevent>
                <div class="sim-whatif__field">
                  <label for="sim-whatif-indicador" class="muted">INDICADOR</label>
                  <SelectInput
                    id="sim-whatif-indicador"
                    :model-value="selectedIndicatorId"
                    :options="selectOptions"
                    placeholder="Selecione um indicador"
                    label="Indicador"
                    :disabled="catalog.length === 0 || loading"
                    @update:model-value="handleIndicatorChange"
                  />
                </div>

                <div class="sim-whatif__field">
                  <label for="sim-whatif-extra" class="muted">INCREMENTO</label>
                  <div class="sim-whatif__input-group" :class="{ 'has-value': delta > 0 }">
                    <span id="sim-whatif-prefix" class="sim-whatif__prefix">
                      {{ selectedProduct?.metric === 'valor' ? 'R$' : '+' }}
                    </span>
                    <input 
                      id="sim-whatif-extra"
                      type="text"
                      v-model="deltaDisplay"
                      inputmode="numeric"
                      class="input sim-whatif__input"
                      :placeholder="selectedProduct?.metric === 'valor' ? '0,00' : '0'"
                      :disabled="!selectedProduct"
                      @blur="handleDeltaBlur"
                      @focus="handleDeltaFocus"
                    />
                  </div>
                  <div class="sim-whatif__hint-group">
                    <p id="sim-whatif-unit" class="sim-whatif__hint">
                      <template v-if="selectedProduct">
                        <span class="sim-whatif__hint-item">
                          <strong>Meta:</strong> {{ formatByMetric(selectedProduct.metric, selectedProduct.meta) }}
                        </span>
                        <span class="sim-whatif__hint-item">
                          <strong>Realizado:</strong> {{ formatByMetric(selectedProduct.metric, selectedProduct.realizado) }}
                        </span>
                        <template v-if="selectedProduct.meta > selectedProduct.realizado">
                          <span class="sim-whatif__hint-item sim-whatif__hint-item--gap">
                            <strong>Falta:</strong> {{ formatByMetric(selectedProduct.metric, selectedProduct.meta - selectedProduct.realizado) }}
                          </span>
                        </template>
                      </template>
                      <template v-else>
                        Selecione um indicador elegível.
                      </template>
                    </p>
                    <p v-if="delta > 0 && selectedProduct" class="sim-whatif__hint sim-whatif__hint--delta">
                      Incremento: <strong>{{ formatDeltaDisplay(delta) }}</strong>
                    </p>
                  </div>
                </div>
              </form>

            <div 
              id="sim-whatif-shortcuts" 
              class="sim-quick"
              :class="{ 'is-empty': !selectedProduct || shortcuts.length === 0 }"
            >
              <span v-if="selectedProduct && shortcuts.length > 0" class="sim-quick__label">Atalhos</span>
              <div v-if="selectedProduct && shortcuts.length > 0" class="sim-quick__chips">
                <button 
                  v-for="(shortcut, index) in shortcuts" 
                  :key="index"
                  type="button" 
                  class="sim-chip"
                  :title="formatByMetric(selectedProduct.metric, shortcut.value)"
                  @click="applyShortcut(shortcut.value)"
                >
                  {{ shortcut.label }}
                </button>
              </div>
            </div>

            <div class="sim-whatif__results">
              <div class="sim-whatif__header">
                <h4 id="sim-whatif-title">
                  <template v-if="selectedProduct">
                    Impacto em {{ selectedProduct.label }}
                  </template>
                  <template v-else-if="catalog.length === 0">
                    Sem indicadores elegíveis
                  </template>
                  <template v-else>
                    Escolha um indicador para iniciar
                  </template>
                </h4>
                <p id="sim-whatif-subtitle" class="muted">
                  <template v-if="selectedProduct">
                    {{ selectedProduct.sectionLabel }} · Meta de {{ formatByMetric(selectedProduct.metric, selectedProduct.meta) }}
                  </template>
                  <template v-else-if="catalog.length === 0">
                    Nenhum indicador disponível no momento.
                  </template>
                  <template v-else>
                    Selecione um indicador e informe um incremento para ver o impacto.
                  </template>
                </p>
              </div>

              <div v-if="!selectedProduct" class="sim-whatif__empty">
                <template v-if="loading">
                  Carregando indicadores...
                </template>
                <template v-else-if="error">
                  {{ error }}
                </template>
                <template v-else-if="catalog.length === 0">
                  Nenhum indicador com meta em valor ou quantidade está disponível.
                </template>
                <template v-else>
                  Selecione um indicador com meta em valor ou quantidade para liberar a simulação.
                </template>
              </div>

              <div v-else id="sim-whatif-cards" class="sim-whatif-cards">
                <article class="sim-whatif-card sim-whatif-card--var">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <Icon name="coin" :size="24" />
                    </span>
                    <h5>Remuneração variável</h5>
                  </header>
                  <dl class="sim-whatif-card__stats">
                    <div>
                      <dt>Atual</dt>
                      <dd>{{ formatBRLReadable(simulationResults?.variavelAtual || 0) }}</dd>
                    </div>
                    <div>
                      <dt>Projeção</dt>
                      <dd>{{ formatBRLReadable(simulationResults?.variavelProj || 0) }}</dd>
                    </div>
                    <div>
                      <dt>Incremento</dt>
                      <dd class="sim-whatif-card__highlight">
                        {{ formatCurrencyDelta(simulationResults?.variavelDelta || 0) }}
                      </dd>
                    </div>
                  </dl>
                  <p class="sim-whatif-card__meta">
                    Meta de remuneração: {{ formatBRLReadable(simulationResults?.variavelMeta || simulationResults?.variavelProj || 0) }}
                  </p>
                </article>

                <article class="sim-whatif-card sim-whatif-card--points">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <Icon name="medal" :size="24" />
                    </span>
                    <h5>Pontuação</h5>
                  </header>
                  <dl class="sim-whatif-card__stats">
                    <div>
                      <dt>Atual</dt>
                      <dd>{{ formatPoints(simulationResults?.pontosAtual || 0, { withUnit: true }) }}</dd>
                    </div>
                    <div>
                      <dt>Projeção</dt>
                      <dd>{{ formatPoints(simulationResults?.pontosProj || 0, { withUnit: true }) }}</dd>
                    </div>
                    <div>
                      <dt>Incremento</dt>
                      <dd class="sim-whatif-card__highlight">
                        {{ formatPointsDelta(simulationResults?.pontosDelta || 0) }}
                      </dd>
                    </div>
                  </dl>
                  <p class="sim-whatif-card__meta">
                    Peso máximo: {{ formatPoints(simulationResults?.pontosMeta || simulationResults?.pontosProj || 0, { withUnit: true }) }}
                  </p>
                </article>

                <article class="sim-whatif-card sim-whatif-card--hit">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <Icon name="target" :size="24" />
                    </span>
                    <h5>Atingimento</h5>
                  </header>
                  <dl class="sim-whatif-card__stats">
                    <div>
                      <dt>Atual</dt>
                      <dd>{{ (simulationResults?.atingAtual || 0).toFixed(1) }}%</dd>
                    </div>
                    <div>
                      <dt>Projeção</dt>
                      <dd>{{ (simulationResults?.atingProj || 0).toFixed(1) }}%</dd>
                    </div>
                    <div>
                      <dt>Variação</dt>
                      <dd class="sim-whatif-card__highlight">
                        {{ simulationResults?.atingDelta && simulationResults.atingDelta > 0 ? '+' : '' }}{{ (simulationResults?.atingDelta || 0).toFixed(1) }} p.p.
                      </dd>
                    </div>
                  </dl>
                  <p class="sim-whatif-card__meta">
                    Realizado projetado: {{ formatByMetric(simulationResults?.metric || 'valor', simulationResults?.novoReal || 0) }}
                  </p>
                </article>
              </div>

              <div v-if="selectedProduct && simulationResults" id="sim-whatif-foot" class="sim-whatif__foot">
                <p>
                  Incremento informado: {{ formatByMetric(simulationResults.metric, simulationResults.deltaValue) }} · 
                  Realizado atual: {{ formatByMetric(simulationResults.metric, simulationResults.realizado) }} · 
                  Meta vigente: {{ formatByMetric(simulationResults.metric, simulationResults.meta) }}
                  <template v-if="simulationResults.pontosMeta">
                    · Peso do indicador: {{ formatPoints(simulationResults.pontosMeta, { withUnit: true }) }}
                  </template>
                  <template v-if="selectedProduct.ultimaAtualizacao">
                    · Última atualização: {{ selectedProduct.ultimaAtualizacao }}
                  </template>
                </p>
                <p class="sim-whatif__foot-note">
                  Projeção linear baseada nos dados atuais. Resultados reais podem variar conforme regras específicas do indicador.
                </p>
              </div>
            </div>
            </template>
          </div>
        </section>
      </div>
    </div>
</template>

<style scoped>
.simuladores-wrapper {
  --brand: var(--brad-color-primary, #cc092f);
  --brand-dark: var(--brad-color-primary-dark, #9d0b21);
  --info: var(--brad-color-accent, #517bc5);
  --bg: var(--brad-color-neutral-0, #fff);
  --panel: var(--brad-color-neutral-0, #fff);
  --stroke: var(--brad-color-gray-light, #ebebeb);
  --text: var(--brad-color-neutral-100, #000);
  --muted: var(--brad-color-gray-dark, #999);
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  color: var(--text);
  font-family: var(--brad-font-family);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  box-sizing: border-box;
}

.simuladores-view {
  width: 100%;
  margin-top: 24px;
}

.card {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 16px;
  box-sizing: border-box;
}

.card--simuladores {
  padding: 0;
}

.card__header {
  padding: 16px;
  border-bottom: 1px solid var(--stroke);
}

.title-subtitle h3 {
  margin: 0 0 4px 0;
  font-size: 20px;
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text);
  line-height: 1.2;
}

.title-subtitle .muted {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
  line-height: 1.4;
}

.sim-whatif {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.sim-whatif__form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.sim-whatif__field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sim-whatif__field label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--muted);
  font-weight: 600;
}

.input {
  padding: 10px 12px;
  border: 1px solid var(--stroke);
  border-radius: 8px;
  background: var(--panel);
  color: var(--text);
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
  width: 100%;
}

.sim-whatif__input-group {
  display: flex;
  align-items: center;
  gap: 0;
  position: relative;
  transition: all 0.2s ease;
}

.sim-whatif__input-group.has-value {
  box-shadow: 0 0 0 3px rgba(179, 0, 0, 0.08);
}

.sim-whatif__prefix {
  padding: 10px 10px 10px 14px;
  background: var(--bg);
  border: 1px solid var(--stroke);
  border-right: none;
  border-radius: 10px 0 0 10px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
  flex-shrink: 0;
}

.sim-whatif__input-group.has-value .sim-whatif__prefix {
  background: var(--brand-xlight, rgba(179, 0, 0, 0.05));
  color: var(--brand, #b30000);
  border-color: var(--brand, #b30000);
}

.sim-whatif__input {
  flex: 1;
  border-radius: 0 10px 10px 0;
  border-left: none;
  text-align: right;
  font-weight: 600;
  font-size: 16px;
  padding: 10px 14px;
  font-variant-numeric: tabular-nums;
  min-width: 0;
}

.sim-whatif__input-group.has-value .sim-whatif__input {
  border-color: var(--brand, #b30000);
  color: var(--text, #0f1424);
}

.sim-whatif__input:focus {
  outline: none;
  border-color: var(--brand, #b30000);
  box-shadow: 0 0 0 3px rgba(179, 0, 0, 0.12);
}


.sim-whatif__hint-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 8px;
}

.sim-whatif__hint {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.5;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
}

.sim-whatif__hint-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.sim-whatif__hint-item strong {
  font-weight: 600;
  color: var(--text, #0f1424);
}

.sim-whatif__hint-item--gap {
  color: var(--brand, #b30000);
}

.sim-whatif__hint-item--gap strong {
  color: var(--brand, #b30000);
}

.sim-whatif__hint--delta {
  margin-top: 4px;
  padding-top: 8px;
  border-top: 1px solid var(--stroke, #e7eaf2);
  color: var(--text, #0f1424);
  font-weight: 500;
}

.sim-whatif__hint--delta strong {
  color: var(--brand, #b30000);
  font-size: 13px;
}

.sim-quick {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--bg);
  border-radius: 10px;
  border: 1px solid var(--stroke);
}

.sim-quick.is-empty {
  display: none;
}

.sim-quick__label {
  font-size: 12px;
  font-weight: 600;
  color: var(--muted);
  white-space: nowrap;
}

.sim-quick__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.sim-chip {
  appearance: none;
  border: 1px solid #d1d5db;
  border-radius: 20px;
  padding: 6px 12px;
  background: #fff;
  color: #1f2937;
  font-weight: 600;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.15s ease;
}

.sim-chip:hover {
  box-shadow: 0 8px 18px rgba(17, 23, 41, 0.1);
  transform: translateY(-1px);
  border-color: var(--brand);
  color: var(--brand);
}

.sim-whatif__results {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.sim-whatif__header h4 {
  margin: 0 0 4px 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
}

.sim-whatif__header .muted {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
}

.sim-whatif__empty {
  padding: 32px;
  text-align: center;
  color: var(--muted);
  background: var(--bg);
  border-radius: 10px;
  border: 1px dashed var(--stroke);
}

.sim-whatif-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
}

.sim-whatif-card {
  background: #fdfdff;
  border: 2px solid #dbe2ff;
  border-radius: 18px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.sim-whatif-card--var {
  border-color: #dbe2ff;
}

.sim-whatif-card--points {
  border-color: #ffe6ea;
}

.sim-whatif-card--hit {
  border-color: #e0f2fe;
}

.sim-whatif-card__head {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sim-whatif-card__icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(36, 107, 253, 0.1);
  color: #246BFD;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.sim-whatif-card--var .sim-whatif-card__icon {
  background: rgba(36, 107, 253, 0.1);
  color: #246BFD;
}

.sim-whatif-card--points .sim-whatif-card__icon {
  background: rgba(179, 0, 0, 0.1);
  color: #b30000;
}

.sim-whatif-card--hit .sim-whatif-card__icon {
  background: rgba(14, 165, 233, 0.1);
  color: #0ea5e9;
}

.sim-whatif-card__icon i {
  font-size: 20px;
}

.sim-whatif-card__head h5 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.sim-whatif-card__stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin: 0;
  padding: 0;
}

.sim-whatif-card__stats > div {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sim-whatif-card__stats dt {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--muted);
  font-weight: 700;
  margin: 0;
}

.sim-whatif-card__stats dd {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  font-variant-numeric: tabular-nums;
}

.sim-whatif-card__highlight {
  color: var(--brand) !important;
}

.sim-whatif-card__meta {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.4;
}

.sim-whatif__foot {
  padding-top: 20px;
  border-top: 1px solid var(--stroke);
}

.sim-whatif__foot p {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: var(--muted);
  line-height: 1.5;
}

.sim-whatif__foot-note {
  font-size: 12px !important;
  color: var(--muted) !important;
  font-style: italic;
}

/* Skeleton Loading */
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

.skeleton--label {
  height: 12px;
  width: 80px;
  margin-bottom: 8px;
}

.skeleton--input {
  height: 42px;
  width: 100%;
}

.skeleton--hint {
  height: 16px;
  width: 60%;
  margin-top: 8px;
}

.skeleton--shortcuts {
  height: 48px;
  width: 100%;
  border-radius: 10px;
}

.skeleton--title {
  height: 24px;
  width: 200px;
  margin-bottom: 8px;
}

.skeleton--subtitle {
  height: 18px;
  width: 300px;
  margin-bottom: 20px;
}

.skeleton--card {
  height: 200px;
  width: 100%;
  border-radius: 18px;
}

.sim-whatif__skeleton {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.sim-whatif__skeleton .sim-whatif__form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.sim-whatif__skeleton .sim-whatif-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
}

@media (max-width: 768px) {
  .sim-whatif__form {
    grid-template-columns: 1fr;
  }

  .sim-whatif__skeleton .sim-whatif__form {
    grid-template-columns: 1fr;
  }

  .sim-whatif-cards {
    grid-template-columns: 1fr;
  }

  .sim-whatif-card__stats {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}
</style>
