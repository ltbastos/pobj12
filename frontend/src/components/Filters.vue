<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Icon from './Icon.vue'
import { useInitCache } from '../composables/useInitCache'
import { useHierarchyFilters } from '../composables/useHierarchyFilters'
import { usePeriodManager } from '../composables/usePeriodManager'
import { useAccumulatedView, syncPeriodFromAccumulatedView } from '../composables/useAccumulatedView'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import Button from './Button.vue'
import SelectSearch from './SelectSearch.vue'
import SelectInput from './SelectInput.vue'
import type { FilterOption } from '../types'
 
const route = useRoute()
const isSimuladoresPage = computed(() => route.name === 'Simuladores')

const advancedFiltersOpen = ref(false)
const showAdvancedButton = ref(true)
const { initData, isLoading: initLoading, loadInit } = useInitCache()
const loading = initLoading

const { updateFilter, updatePeriod, clearFilters, triggerFilter } = useGlobalFilters()

const {
  segmento,
  diretoria,
  gerencia,
  agencia,
  ggestao,
  gerente,
  segmentos,
  diretorias,
  regionais,
  agencias,
  gerentesGestao,
  gerentes,
  handleSegmentoChange,
  handleDiretoriaChange,
  handleGerenciaChange,
  handleAgenciaChange,
  handleGerenteGestaoChange,
  handleGerenteChange,
  clearAll: clearHierarchy
} = useHierarchyFilters(initData)

const familias = ref<FilterOption[]>([])
const indicadores = ref<FilterOption[]>([])
const allSubindicadores = ref<FilterOption[]>([])
const statusIndicadores = ref<FilterOption[]>([])

const selectedFamilia = ref('')
const selectedIndicador = ref('')
const selectedSubindicador = ref('')
const selectedStatusKpi = ref('todos')

const indicadoresFiltrados = computed(() => {
  if (!selectedFamilia.value) {
    return indicadores.value
  }

  const familiaId = String(selectedFamilia.value).trim()
  return indicadores.value.filter(ind => {
    const indFamiliaId = ind.id_familia ? String(ind.id_familia).trim() : ''
    return indFamiliaId === familiaId
  })
})

const subindicadores = computed(() => {
  if (!selectedIndicador.value) {
    return []
  }

  const indicadorId = String(selectedIndicador.value).trim()
  return allSubindicadores.value.filter(sub => {
    const subIndicadorId = sub.id_indicador ? String(sub.id_indicador).trim() : ''
    return subIndicadorId === indicadorId
  })
})

const hasSubindicadores = computed(() => {
  return subindicadores.value.length > 0
})

const findItemMeta = (id: string, items: FilterOption[]): FilterOption | null => {
  if (!id) return null
  const normalizedId = String(id).trim()
  return items.find(item => String(item.id).trim() === normalizedId) || null
}

const handleFamiliaChange = (value: string): void => {
  selectedFamilia.value = value
  if (!value) {
    selectedIndicador.value = ''
    selectedSubindicador.value = ''
  } else {
    
    if (selectedIndicador.value) {
      const indicadorMeta = findItemMeta(selectedIndicador.value, indicadores.value)
      const familiaId = String(value).trim()
      const indicadorFamiliaId = indicadorMeta?.id_familia ? String(indicadorMeta.id_familia).trim() : ''
      if (indicadorFamiliaId !== familiaId) {
        selectedIndicador.value = ''
        selectedSubindicador.value = ''
      }
    }
  }
}

const handleIndicadorChange = (value: string): void => {
  selectedIndicador.value = value
  selectedSubindicador.value = ''

  if (value) {
    const indicadorMeta = findItemMeta(value, indicadores.value)
    if (indicadorMeta?.id_familia) {
      const familiaId = String(indicadorMeta.id_familia).trim()
      const familiaExists = familias.value.some(fam => String(fam.id).trim() === familiaId)
      if (familiaExists) {
        selectedFamilia.value = familiaId
      }
    }
  } else {
    selectedIndicador.value = ''
    selectedSubindicador.value = ''
  }
}

const handleSubindicadorChange = (value: string): void => {
  selectedSubindicador.value = value
  if (value) {
    const subindicadorMeta = findItemMeta(value, allSubindicadores.value)

    if (subindicadorMeta?.id_indicador) {
      const indicadorId = String(subindicadorMeta.id_indicador).trim()

      const indicadorExists = indicadores.value.some(ind => {
        const indId = String(ind.id).trim()
        return indId === indicadorId
      })

      if (indicadorExists) {
        selectedIndicador.value = indicadorId

        const indicadorMeta = findItemMeta(indicadorId, indicadores.value)
        if (indicadorMeta?.id_familia) {
          const familiaId = String(indicadorMeta.id_familia).trim()

          const familiaExists = familias.value.some(fam => {
            const famId = String(fam.id).trim()
            return famId === familiaId
          })

          if (familiaExists) {
            selectedFamilia.value = familiaId
          }
        }
      }
    }
  } else {
    selectedSubindicador.value = ''
  }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const normalizeOption = (item: any): FilterOption => {
  const id = String(item.id || item.codigo || item.id_diretoria || item.id_regional || item.id_agencia || item.funcional || item.subId || item.subindicadorId || '').trim()
  const label = String(item.label || item.nome || id).trim()

  const idFamilia = item.id_familia ?? item.idFamilia ?? item.familia_id ?? item.familiaId
  const idIndicador = item.id_indicador ?? item.idIndicador ?? item.indicador_id ?? item.indicadorId ?? item.produtoId ?? item.produto_id

  return {
    id,
    nome: label,
    id_familia: idFamilia ? String(idFamilia).trim() : undefined,
    id_indicador: idIndicador ? String(idIndicador).trim() : undefined
  }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const buildOptions = (data: any[]): FilterOption[] => {
  if (!Array.isArray(data)) return []
  return data.map(normalizeOption).filter(opt => opt.id)
}

const loadEstrutura = async (): Promise<void> => {
  try {
    const data = await loadInit()

    if (data) {
      familias.value = buildOptions(data.familias || [])
      indicadores.value = buildOptions(data.indicadores || [])
      allSubindicadores.value = buildOptions(data.subindicadores || [])
      statusIndicadores.value = buildOptions(data.status_indicadores || [])
    }
  } catch (error) {
    console.error('Erro ao carregar estrutura:', error)
  }
}

const { period, updatePeriod: updatePeriodLocal } = usePeriodManager()

const { accumulatedView, handleViewChange, options: accumulatedViewOptions } = useAccumulatedView(
  period,
  updatePeriodLocal
)

const statusKpiOptions = computed<FilterOption[]>(() => [
  { id: 'todos', nome: 'Todos' },
  { id: 'atingidos', nome: 'Atingidos' },
  { id: 'nao', nome: 'Não atingidos' }
])

const visaoAcumuladaOptions = computed<FilterOption[]>(() => {
  return accumulatedViewOptions.map(opt => ({
    id: opt.value,
    nome: opt.label
  }))
})

const handleStatusKpiChange = (value: string): void => {
  selectedStatusKpi.value = value
}

const handleVisaoAcumuladaChange = (value: string): void => {
  handleViewChange(value as 'mensal' | 'semestral' | 'anual')
}

onMounted(() => {
  loadEstrutura()
  applyFilters()
  triggerFilter()
})

const toggleAdvancedFilters = (): void => {
  advancedFiltersOpen.value = !advancedFiltersOpen.value
}

const handleFilter = (): void => {
  applyFilters()
  triggerFilter()
}

const applyFilters = (): void => {
  updateFilter('segmento', segmento.value)
  updateFilter('diretoria', diretoria.value)
  updateFilter('gerencia', gerencia.value)
  updateFilter('agencia', agencia.value)
  updateFilter('ggestao', ggestao.value)
  updateFilter('gerente', gerente.value)
  updateFilter('familia', selectedFamilia.value)
  updateFilter('indicador', selectedIndicador.value)
  updateFilter('subindicador', selectedSubindicador.value)
  updateFilter('status', selectedStatusKpi.value)
  
  updatePeriod(period.value)
}

const handleClear = (): void => {
  clearHierarchy()
  selectedFamilia.value = ''
  selectedIndicador.value = ''
  selectedSubindicador.value = ''
  selectedStatusKpi.value = 'todos'
  accumulatedView.value = 'mensal'
  period.value = syncPeriodFromAccumulatedView('mensal', period.value)
  
  clearFilters()
  updatePeriod(period.value)
}

watch(() => period.value, (newPeriod) => {
  updatePeriod(newPeriod)
}, { deep: true })
</script>

<template>
  <section class="card card--filters">
    <header class="card__header">
      <div class="filters-hero">
        <div class="title-subtitle">
          <h2>POBJ Produções</h2>
          <p class="muted">Acompanhe dados atualizados de performance.</p>
        </div>
      </div>
    </header>

    <div class="filters">
      <div class="filters__group">
        <label>Segmento</label>
        <SelectSearch
          id="f-segmento"
          :model-value="segmento"
          :options="[{ id: '', nome: 'Todos' }, ...(Array.isArray(segmentos) ? segmentos : [])]"
          placeholder="Todos"
          label="Segmento"
          :disabled="loading"
          @update:model-value="handleSegmentoChange"
        />
      </div>
      <div class="filters__group">
        <label>Diretoria</label>
        <SelectSearch
          id="f-diretoria"
          :model-value="diretoria"
          :options="[{ id: '', nome: 'Todas' }, ...(Array.isArray(diretorias) ? diretorias : [])]"
          placeholder="Todas"
          label="Diretoria"
          :disabled="loading"
          @update:model-value="handleDiretoriaChange"
        />
      </div>
      <div class="filters__group">
        <label>Regional</label>
        <SelectSearch
          id="f-gerencia"
          :model-value="gerencia"
          :options="[{ id: '', nome: 'Todas' }, ...(Array.isArray(regionais) ? regionais : [])]"
          placeholder="Todas"
          label="Regional"
          :disabled="loading"
          @update:model-value="handleGerenciaChange"
        />
      </div>
      <div class="filters__actions">
        <Button id="btn-filtrar" variant="primary" :disabled="isSimuladoresPage" @click="handleFilter">
          <Icon name="search" :size="16" color="white" />
          Filtrar
        </Button>
        <Button id="btn-limpar" variant="secondary" @click="handleClear">
          <Icon name="x" :size="16" />
          Limpar filtros
        </Button>
      </div>
    </div>

      <div v-if="showAdvancedButton" class="filters__more filters__more--center">
      <Button
        id="btn-abrir-filtros"
        variant="info"
        :aria-expanded="advancedFiltersOpen"
        aria-controls="advanced-filters"
        @click="toggleAdvancedFilters"
        @keydown.enter.prevent="toggleAdvancedFilters"
        @keydown.space.prevent="toggleAdvancedFilters"
      >
        <Icon name="chevron-down" :size="16" :class="{ 'rotated': advancedFiltersOpen }" />
        <span>{{ advancedFiltersOpen ? 'Fechar filtros avançados' : 'Abrir filtros avançados' }}</span>
      </Button>
    </div>

    <section
      id="advanced-filters"
      class="adv"
      :class="{ 'is-open': advancedFiltersOpen }"
      :aria-hidden="!advancedFiltersOpen"
      :aria-labelledby="'btn-abrir-filtros'"
      role="region"
    >
      <div class="adv__grid">
        <div class="filters__group">
          <label>Agência</label>
          <SelectSearch
            id="f-agencia"
            :model-value="agencia"
            :options="[{ id: '', nome: 'Todas' }, ...(Array.isArray(agencias) ? agencias : [])]"
            placeholder="Todas"
            label="Agência"
            :disabled="loading"
            @update:model-value="handleAgenciaChange"
          />
        </div>
        <div class="filters__group">
          <label>Gerente de gestão</label>
          <SelectSearch
            id="f-gerente-gestao"
            :model-value="ggestao"
            :options="[{ id: '', nome: 'Todos' }, ...(Array.isArray(gerentesGestao) ? gerentesGestao : [])]"
            placeholder="Todos"
            label="Gerente de gestão"
            :disabled="loading"
            @update:model-value="handleGerenteGestaoChange"
          />
        </div>
        <div class="filters__group">
          <label>Gerente</label>
          <SelectSearch
            id="f-gerente"
            :model-value="gerente"
            :options="[{ id: '', nome: 'Todos' }, ...(Array.isArray(gerentes) ? gerentes : [])]"
            placeholder="Todos"
            label="Gerente"
            :disabled="loading"
            @update:model-value="handleGerenteChange"
          />
        </div>
        <div class="filters__group">
          <label>Família</label>
          <SelectSearch
            id="f-secao"
            :model-value="selectedFamilia"
            :options="[{ id: '', nome: 'Selecione...' }, ...familias]"
            placeholder="Selecione..."
            label="Família"
            :disabled="loading"
            @update:model-value="handleFamiliaChange"
          />
        </div>
        <div class="filters__group">
          <label>Indicadores</label>
          <SelectSearch
            id="f-familia"
            :model-value="selectedIndicador"
            :options="[{ id: '', nome: 'Selecione...' }, ...indicadoresFiltrados]"
            placeholder="Selecione..."
            label="Indicadores"
            :disabled="loading || !selectedFamilia"
            @update:model-value="handleIndicadorChange"
          />
        </div>
        <div class="filters__group">
          <label>Subindicador</label>
          <SelectSearch
            id="f-produto"
            :model-value="selectedSubindicador"
            :options="[{ id: '', nome: 'Selecione...' }, ...subindicadores]"
            placeholder="Selecione..."
            label="Subindicador"
            :disabled="loading || !selectedIndicador || !hasSubindicadores"
            @update:model-value="handleSubindicadorChange"
          />
        </div>
        <div class="filters__group">
          <label>Status dos indicadores</label>
          <SelectInput
            id="f-status-kpi"
            :model-value="selectedStatusKpi"
            :options="statusKpiOptions"
            placeholder="Todos"
            label="Status dos indicadores"
            :disabled="loading"
            @update:model-value="handleStatusKpiChange"
          />
        </div>
        <div class="filters__group">
          <label>Visão acumulada</label>
          <SelectInput
            id="f-visao"
            :model-value="accumulatedView"
            :options="visaoAcumuladaOptions"
            placeholder="Mensal"
            label="Visão acumulada"
            :disabled="loading"
            @update:model-value="handleVisaoAcumuladaChange"
          />
        </div>
      </div>
    </section>
  </section>
</template>

<style scoped>
.card {
  --bg: #f6f7fc;
  --panel: #ffffff;
  --stroke: #e7eaf2;
  --text: #0f1424;
  --muted: #6b7280;
  --radius: 16px;
  --shadow: 0 12px 28px rgba(17, 23, 41, 0.08);
  --brand: #b30000;
  --brand-dark: #8f0000;

  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 16px;
  margin-bottom: 16px;
  animation: fadeInDown 0.4s cubic-bezier(0.25, 0.1, 0.25, 1) backwards;
  transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card--filters {
  position: relative;
  overflow: visible;
  z-index: 10;
}

.card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}

.filters-hero {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
  width: 100%;
}

.title-subtitle h2 {
  font-size: 24px;
  font-weight: 800;
  color: var(--text);
  margin: 0 0 4px;
}

.title-subtitle .muted {
  font-size: 14px;
  color: var(--muted);
  margin: 0;
}

.filters {
  display: grid;
  grid-template-columns: repeat(3, minmax(240px, 1fr)) auto;
  gap: 12px;
  align-items: end;
  margin-top: 8px;
  margin-bottom: 12px;
}

.filters__group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filters__group label {
  font-size: 12px;
  color: var(--muted);
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
}

.input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--stroke);
  border-radius: 10px;
  background: var(--panel);
  color: var(--text);
  font-size: 14px;
  font-family: inherit;
  transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
  transform: scale(1);
  position: relative;
}

.input:hover:not(:disabled):not(:focus) {
  border-color: rgba(179, 0, 0, 0.3);
  box-shadow: 0 0 0 1px rgba(179, 0, 0, 0.1);
}

.input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(179, 0, 0, 0.12);
  transform: scale(1.005);
}

.input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #f9fafb;
}

.input::placeholder {
  color: #9ca3af;
  transition: opacity 0.2s ease;
}

.input:focus::placeholder {
  opacity: 0.5;
}

.filters__actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  align-items: end;
}

.filters__more {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 6px;
}

.filters__more--center {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 6px;
}

.adv {
  max-height: 0;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
  opacity: 0;
  transform: translateY(-10px);
}

.adv.is-open {
  max-height: 600px;
  overflow: visible;
  opacity: 1;
  transform: translateY(0);
}

.adv[aria-hidden='false'] {
  max-height: 600px;
  padding-top: 8px;
  opacity: 1;
  transform: translateY(0);
}

.adv__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(240px, 1fr));
  gap: 12px;
  align-items: end;
  padding-top: 8px;
}

.adv[aria-hidden='true'] {
  display: none;
}

.adv[aria-hidden='false'] {
  display: block;
}

.rotated {
  transform: rotate(180deg);
  transition: transform 0.2s ease;
}

@media (max-width: 1024px) {
  .filters {
    grid-template-columns: repeat(2, 1fr);
  }

  .adv__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .filters {
    grid-template-columns: 1fr;
  }

  .adv__grid {
    grid-template-columns: 1fr;
  }

  .filters__actions {
    flex-direction: column;
    align-items: stretch;
  }

  .card--filters .title-subtitle {
    display: none;
  }
}

#btn-filtrar {
  color: white !important;
}

#btn-filtrar :deep(span) {
  color: white !important;
}

#btn-filtrar :deep(svg) {
  color: white !important;
  stroke: white !important;
}
</style>

<style>
#btn-filtrar {
  color: white !important;
}

#btn-filtrar span {
  color: white !important;
}

#btn-filtrar svg {
  color: white !important;
  stroke: white !important;
}
</style>

