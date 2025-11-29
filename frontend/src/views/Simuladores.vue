<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { formatINT, formatPoints, formatByMetric, formatBRLReadable } from '../utils/formatUtils'
import Filters from '../components/Filters.vue'
import TabsNavigation from '../components/TabsNavigation.vue'

const { filterState } = useGlobalFilters()

// Estado do simulador
const selectedIndicatorId = ref<string>('')
const delta = ref<number>(0)

// Dados mockados - catálogo de indicadores
const mockCatalog = [
  {
    id: 'cartao_credito_emissao',
    label: 'Cartão de Crédito - Emissão',
    sectionId: 'captacao',
    sectionLabel: 'Captação',
    metric: 'qtd',
    meta: 150,
    realizado: 120,
    variavelMeta: 50000,
    variavelReal: 40000,
    pontosMeta: 15,
    pontosBrutos: 12,
    pontos: 12,
    ultimaAtualizacao: '20/09/2025 08:30'
  },
  {
    id: 'conta_corrente_abertura',
    label: 'Conta Corrente - Abertura',
    sectionId: 'captacao',
    sectionLabel: 'Captação',
    metric: 'qtd',
    meta: 200,
    realizado: 180,
    variavelMeta: 30000,
    variavelReal: 27000,
    pontosMeta: 10,
    pontosBrutos: 9,
    pontos: 9,
    ultimaAtualizacao: '20/09/2025 08:30'
  },
  {
    id: 'emprestimo_consignado',
    label: 'Empréstimo Consignado',
    sectionId: 'credito',
    sectionLabel: 'Crédito',
    metric: 'valor',
    meta: 500000,
    realizado: 420000,
    variavelMeta: 80000,
    variavelReal: 67200,
    pontosMeta: 20,
    pontosBrutos: 16.8,
    pontos: 16.8,
    ultimaAtualizacao: '20/09/2025 08:30'
  },
  {
    id: 'investimento_poupanca',
    label: 'Investimento - Poupança',
    sectionId: 'investimento',
    sectionLabel: 'Investimento',
    metric: 'valor',
    meta: 1000000,
    realizado: 850000,
    variavelMeta: 60000,
    variavelReal: 51000,
    pontosMeta: 12,
    pontosBrutos: 10.2,
    pontos: 10.2,
    ultimaAtualizacao: '20/09/2025 08:30'
  },
  {
    id: 'seguro_vida',
    label: 'Seguro de Vida',
    sectionId: 'seguros',
    sectionLabel: 'Seguros',
    metric: 'qtd',
    meta: 80,
    realizado: 65,
    variavelMeta: 40000,
    variavelReal: 32500,
    pontosMeta: 8,
    pontosBrutos: 6.5,
    pontos: 6.5,
    ultimaAtualizacao: '20/09/2025 08:30'
  }
]

// Indicador selecionado
const selectedProduct = computed(() => {
  return mockCatalog.find(item => item.id === selectedIndicatorId.value) || null
})

// Agrupa indicadores por seção
const groupedCatalog = computed(() => {
  const groups = new Map<string, typeof mockCatalog>()
  mockCatalog.forEach(item => {
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

// Função auxiliar para converter para número
const toNumber = (value: any): number => {
  if (value === null || value === undefined || value === '') return 0
  const n = typeof value === 'string' ? parseFloat(value) : value
  return Number.isFinite(n) ? n : 0
}

// Calcula os resultados da simulação
const simulationResults = computed(() => {
  if (!selectedProduct.value) return null

  const product = selectedProduct.value
  const metric = product.metric || 'valor'
  const meta = Math.max(0, toNumber(product.meta))
  const realizado = Math.max(0, toNumber(product.realizado))
  const variavelMeta = Math.max(0, toNumber(product.variavelMeta))
  const variavelReal = Math.max(0, toNumber(product.variavelReal))
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

// Sugestões de atalhos
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

// Formata delta de moeda
const formatCurrencyDelta = (value: number): string => {
  const formatted = formatBRLReadable(value)
  return value > 0 ? `+${formatted}` : formatted
}

// Formata delta de pontos
const formatPointsDelta = (value: number): string => {
  const formatted = formatPoints(value, { withUnit: true })
  return value > 0 ? `+${formatted}` : formatted
}

// Aplica atalho
const applyShortcut = (value: number) => {
  const product = selectedProduct.value
  if (!product) return
  
  const step = product.metric === 'valor' ? 1000 : 1
  const nextValue = Math.max(0, Math.round(value / step) * step)
  delta.value = nextValue
}

// Inicializa com o primeiro indicador
onMounted(() => {
  if (mockCatalog.length > 0 && !selectedIndicatorId.value && mockCatalog[0]) {
    selectedIndicatorId.value = mockCatalog[0].id
  }
})

// Reseta delta quando muda o indicador
watch(selectedIndicatorId, () => {
  delta.value = 0
})
</script>

<template>
  <div class="simuladores-wrapper">
    <div class="container">
      <Filters />
      <TabsNavigation />

      <div class="simuladores-view">
        <section class="card card--simuladores">
          <header class="card__header">
            <div class="title-subtitle">
              <h3>Simulador "E se?"</h3>
              <p class="muted">Simule o impacto de incrementos em indicadores específicos.</p>
            </div>
          </header>

          <div id="sim-whatif" class="sim-whatif">
            <!-- Formulário de seleção -->
            <form id="sim-whatif-form" class="sim-whatif__form" @submit.prevent>
              <div class="sim-whatif__field">
                <label for="sim-whatif-indicador" class="muted">INDICADOR</label>
                <select 
                  id="sim-whatif-indicador" 
                  v-model="selectedIndicatorId"
                  class="input"
                >
                  <option value="" disabled>Selecione um indicador</option>
                  <optgroup 
                    v-for="group in groupedCatalog" 
                    :key="group.sectionId"
                    :label="group.sectionLabel"
                  >
                    <option 
                      v-for="item in group.items" 
                      :key="item.id"
                      :value="item.id"
                    >
                      {{ item.label }}
                    </option>
                  </optgroup>
                </select>
              </div>

              <div class="sim-whatif__field">
                <label for="sim-whatif-extra" class="muted">INCREMENTO</label>
                <div class="sim-whatif__input-group">
                  <span id="sim-whatif-prefix" class="sim-whatif__prefix">
                    {{ selectedProduct?.metric === 'valor' ? 'R$' : '+' }}
                  </span>
                  <input 
                    id="sim-whatif-extra"
                    type="number"
                    v-model.number="delta"
                    :min="0"
                    :step="selectedProduct?.metric === 'valor' ? 1000 : 1"
                    class="input"
                    placeholder="0"
                  />
                </div>
                <p id="sim-whatif-unit" class="sim-whatif__hint">
                  <template v-if="selectedProduct">
                    Meta {{ formatByMetric(selectedProduct.metric, selectedProduct.meta) }} · 
                    Realizado {{ formatByMetric(selectedProduct.metric, selectedProduct.realizado) }}
                    <template v-if="selectedProduct.meta > selectedProduct.realizado">
                      · Falta {{ formatByMetric(selectedProduct.metric, selectedProduct.meta - selectedProduct.realizado) }}
                    </template>
                  </template>
                  <template v-else>
                    Selecione um indicador elegível.
                  </template>
                </p>
              </div>
            </form>

            <!-- Atalhos rápidos -->
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

            <!-- Resultados -->
            <div class="sim-whatif__results">
              <div class="sim-whatif__header">
                <h4 id="sim-whatif-title">
                  <template v-if="selectedProduct">
                    Impacto em {{ selectedProduct.label }}
                  </template>
                  <template v-else-if="mockCatalog.length === 0">
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
                  <template v-else-if="mockCatalog.length === 0">
                    Aplique outros filtros ou volte mais tarde para usar o simulador.
                  </template>
                  <template v-else>
                    Os valores consideram os filtros aplicados na visão principal.
                  </template>
                </p>
              </div>

              <div v-if="!selectedProduct" class="sim-whatif__empty">
                <template v-if="mockCatalog.length === 0">
                  Nenhum indicador com meta em valor ou quantidade está disponível com os filtros atuais.
                </template>
                <template v-else>
                  Selecione um indicador com meta em valor ou quantidade para liberar a simulação.
                </template>
              </div>

              <div v-else id="sim-whatif-cards" class="sim-whatif-cards">
                <!-- Card Remuneração Variável -->
                <article class="sim-whatif-card sim-whatif-card--var">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <i class="ti ti-coin"></i>
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

                <!-- Card Pontuação -->
                <article class="sim-whatif-card sim-whatif-card--points">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <i class="ti ti-medal"></i>
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

                <!-- Card Atingimento -->
                <article class="sim-whatif-card sim-whatif-card--hit">
                  <header class="sim-whatif-card__head">
                    <span class="sim-whatif-card__icon">
                      <i class="ti ti-target"></i>
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

              <!-- Rodapé com informações -->
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
                  Projeção linear considerando os filtros atuais. Resultados reais podem variar conforme regras específicas do indicador.
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.simuladores-wrapper {
  --brand: #b30000;
  --brand-dark: #8f0000;
  --info: #246BFD;
  --bg: #f6f7fc;
  --panel: #ffffff;
  --stroke: #e7eaf2;
  --text: #0f1424;
  --muted: #6b7280;
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  background-color: var(--bg);
  background-image:
    url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%20viewBox%3D%270%200%20320%20320%27%3E%3Ctext%20x%3D%2750%25%27%20y%3D%2750%25%27%20fill%3D%27rgba%2815%2C20%2C36%2C0.08%29%27%20font-size%3D%2720%27%20font-family%3D%27Plus%20Jakarta%20Sans%2C%20sans-serif%27%20text-anchor%3D%27middle%27%20dominant-baseline%3D%27middle%27%20transform%3D%27rotate%28-30%20160%20160%29%27%3EX%20Burguer%20%E2%80%A2%20Funcional%201234567%3C/text%3E%3C/svg%3E"),
    radial-gradient(1200px 720px at 95% -30%, #dfe8ff 0%, transparent 60%),
    radial-gradient(1100px 720px at -25% -10%, #ffe6ea 0%, transparent 55%);
  background-repeat: repeat, no-repeat, no-repeat;
  background-size: 320px 320px, auto, auto;
  background-position: center center, 95% -30%, -25% -10%;
  color: var(--text);
  font-family: "Plus Jakarta Sans", Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  box-sizing: border-box;
}

.container {
  max-width: min(1600px, 96vw);
  margin: 18px auto;
  padding: 0 16px;
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
  font-weight: 700;
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
}

.sim-whatif__prefix {
  padding: 10px 8px 10px 12px;
  background: var(--bg);
  border: 1px solid var(--stroke);
  border-right: none;
  border-radius: 8px 0 0 8px;
  color: var(--muted);
  font-size: 14px;
  font-weight: 600;
}

.sim-whatif__input-group .input {
  border-radius: 0 8px 8px 0;
  border-left: none;
}

.sim-whatif__hint {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.4;
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

@media (max-width: 768px) {
  .sim-whatif__form {
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

