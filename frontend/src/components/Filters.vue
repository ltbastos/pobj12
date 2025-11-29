<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { getEstrutura, type EstruturaData } from '../services/estruturaService'
import { useHierarchyFilters } from '../composables/useHierarchyFilters'
import { usePeriodManager } from '../composables/usePeriodManager'
import { useAccumulatedView, syncPeriodFromAccumulatedView } from '../composables/useAccumulatedView'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import Button from './Button.vue'
import SelectSearch from './SelectSearch.vue'
import type { FilterOption } from '../types'

const advancedFiltersOpen = ref(false)
const showAdvancedButton = ref(true)
const loading = ref(true)
const estruturaData = ref<EstruturaData | null>(null)

// Usa filtros globais
const { filterState, period: globalPeriod, updateFilter, updatePeriod, clearFilters } = useGlobalFilters()

// Usa o composable de hierarquia
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
} = useHierarchyFilters(estruturaData)

// Filtros não hierárquicos
const familias = ref<FilterOption[]>([])
const indicadores = ref<FilterOption[]>([])
const allSubindicadores = ref<FilterOption[]>([]) // Todas as opções de subindicadores
const statusIndicadores = ref<FilterOption[]>([])

// Valores selecionados (não hierárquicos)
const selectedFamilia = ref('')
const selectedIndicador = ref('')
const selectedSubindicador = ref('')
const selectedStatusKpi = ref('todos')

// Subindicadores filtrados baseados no indicador selecionado
const subindicadores = computed(() => {
  if (!selectedIndicador.value) {
    return [] // Não mostra subindicadores se não há indicador selecionado
  }

  const indicadorId = String(selectedIndicador.value).trim()
  return allSubindicadores.value.filter(sub => {
    const subIndicadorId = sub.id_indicador ? String(sub.id_indicador).trim() : ''
    return subIndicadorId === indicadorId
  })
})

// Verifica se o indicador selecionado tem subindicadores
const hasSubindicadores = computed(() => {
  return subindicadores.value.length > 0
})

// Função para encontrar metadados de um item
const findItemMeta = (id: string, items: FilterOption[]): FilterOption | null => {
  if (!id) return null
  const normalizedId = String(id).trim()
  return items.find(item => String(item.id).trim() === normalizedId) || null
}

// Handlers para família, indicador e subindicador
const handleFamiliaChange = (value: string): void => {
  selectedFamilia.value = value
  // Limpa indicador e subindicador quando família muda
  if (!value) {
    selectedIndicador.value = ''
    selectedSubindicador.value = ''
  }
}

const handleIndicadorChange = (value: string): void => {
  selectedIndicador.value = value
  // Limpa subindicador quando indicador muda
  selectedSubindicador.value = ''

  if (value) {
    // Tenta preencher família automaticamente
    const indicadorMeta = findItemMeta(value, indicadores.value)
    if (indicadorMeta?.id_familia) {
      const familiaId = String(indicadorMeta.id_familia).trim()
      // Verifica se a família existe na lista
      const familiaExists = familias.value.some(fam => String(fam.id).trim() === familiaId)
      if (familiaExists) {
        selectedFamilia.value = familiaId
      }
    }
  }
}

const handleSubindicadorChange = (value: string): void => {
  selectedSubindicador.value = value
  if (value) {
    // Tenta preencher indicador e família automaticamente
    // Busca em todas as opções de subindicadores, não apenas nas filtradas
    const subindicadorMeta = findItemMeta(value, allSubindicadores.value)

    if (subindicadorMeta?.id_indicador) {
      const indicadorId = String(subindicadorMeta.id_indicador).trim()

      // Verifica se o indicador existe na lista antes de selecionar
      const indicadorExists = indicadores.value.some(ind => {
        const indId = String(ind.id).trim()
        return indId === indicadorId
      })

      if (indicadorExists) {
        selectedIndicador.value = indicadorId

        // Busca a família do indicador
        const indicadorMeta = findItemMeta(indicadorId, indicadores.value)
        if (indicadorMeta?.id_familia) {
          const familiaId = String(indicadorMeta.id_familia).trim()

          // Verifica se a família existe na lista antes de selecionar
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
    // Limpa quando desmarca
    selectedSubindicador.value = ''
  }
}

const normalizeOption = (item: any): FilterOption => {
  const id = String(item.id || item.codigo || item.id_diretoria || item.id_regional || item.id_agencia || item.funcional || item.subId || item.subindicadorId || '').trim()
  const label = String(item.label || item.nome || id).trim()

  // Preserva campos de relacionamento hierárquico
  // Para subindicador, pode vir como indicador_id, indicadorId, ou através de relacionamento
  const idFamilia = item.id_familia ?? item.idFamilia ?? item.familia_id ?? item.familiaId
  const idIndicador = item.id_indicador ?? item.idIndicador ?? item.indicador_id ?? item.indicadorId ?? item.produtoId ?? item.produto_id

  return {
    id,
    label,
    nome: label,
    id_familia: idFamilia ? String(idFamilia).trim() : undefined,
    id_indicador: idIndicador ? String(idIndicador).trim() : undefined
  }
}

const buildOptions = (data: any[]): FilterOption[] => {
  if (!Array.isArray(data)) return []
  return data.map(normalizeOption).filter(opt => opt.id)
}

const loadEstrutura = async (): Promise<void> => {
  try {
    loading.value = true
    const data = await getEstrutura()

    if (data) {
      estruturaData.value = data
      familias.value = buildOptions(data.familias || [])
      indicadores.value = buildOptions(data.indicadores || [])
      allSubindicadores.value = buildOptions(data.subindicadores || [])
      statusIndicadores.value = buildOptions(data.status_indicadores || [])
    }
  } catch (error) {
    console.error('Erro ao carregar estrutura:', error)
  } finally {
    loading.value = false
  }
}

// Inicializa o gerenciador de período
const { period, updatePeriodLabels, updatePeriod: updatePeriodLocal } = usePeriodManager()

// Inicializa visão acumulada
const { accumulatedView, handleViewChange, options: accumulatedViewOptions } = useAccumulatedView(
  period,
  updatePeriodLocal
)

onMounted(() => {
  loadEstrutura()
  // Aplica filtros iniciais (incluindo período)
  applyFilters()
})

const toggleAdvancedFilters = (): void => {
  advancedFiltersOpen.value = !advancedFiltersOpen.value
}

const handleFilter = (): void => {
  // Aplica os filtros (já são aplicados automaticamente via watch, mas força atualização)
  applyFilters()
}

// Função para aplicar todos os filtros
const applyFilters = (): void => {
  // Atualiza filtros globais
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
  
  // Atualiza período global
  updatePeriod(period.value)
}

const handleClear = (): void => {
  clearHierarchy()
  selectedFamilia.value = ''
  selectedIndicador.value = ''
  selectedSubindicador.value = ''
  selectedStatusKpi.value = 'todos'
  accumulatedView.value = 'mensal'
  // Reseta período para mensal
  period.value = syncPeriodFromAccumulatedView('mensal', period.value)
  updatePeriodLabels()
  
  // Limpa filtros globais
  clearFilters()
  updatePeriod(period.value)
}

// Sincroniza mudanças nos filtros locais com os globais automaticamente
watch([segmento, diretoria, gerencia, agencia, ggestao, gerente, selectedFamilia, selectedIndicador, selectedSubindicador, selectedStatusKpi], () => {
  // Aplica filtros automaticamente quando mudam
  applyFilters()
}, { deep: true })

// Sincroniza mudanças no período local com o global
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
          :options="[{ id: '', label: 'Todos' }, ...segmentos]"
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
          :options="[{ id: '', label: 'Todas' }, ...diretorias]"
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
          :options="[{ id: '', label: 'Todas' }, ...regionais]"
          placeholder="Todas"
          label="Regional"
          :disabled="loading"
          @update:model-value="handleGerenciaChange"
        />
      </div>
      <div class="filters__actions">
        <Button id="btn-filtrar" variant="primary" icon="ti ti-search" @click="handleFilter">
          Filtrar
        </Button>
        <Button id="btn-limpar" variant="secondary" icon="ti ti-x" @click="handleClear">
          Limpar filtros
        </Button>
      </div>
    </div>

      <div v-if="showAdvancedButton" class="filters__more filters__more--center">
      <Button
        id="btn-abrir-filtros"
        variant="info"
        :aria-expanded="advancedFiltersOpen"
        @click="toggleAdvancedFilters"
      >
        <i class="ti ti-chevron-down" :class="{ 'rotated': advancedFiltersOpen }" aria-hidden="true"></i>
        {{ advancedFiltersOpen ? 'Fechar filtros' : 'Abrir filtros' }}
      </Button>
    </div>

    <div
      id="advanced-filters"
      class="adv"
      :class="{ 'is-open': advancedFiltersOpen }"
      :aria-hidden="!advancedFiltersOpen"
      :hidden="!advancedFiltersOpen"
    >
      <div class="adv__grid">
        <div class="filters__group">
          <label>Agência</label>
          <SelectSearch
            id="f-agencia"
            :model-value="agencia"
            :options="[{ id: '', label: 'Todas' }, ...agencias]"
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
            :options="[{ id: '', label: 'Todos' }, ...gerentesGestao]"
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
            :options="[{ id: '', label: 'Todos' }, ...gerentes]"
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
            :options="[{ id: '', label: 'Selecione...' }, ...familias]"
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
            :options="[{ id: '', label: 'Selecione...' }, ...indicadores]"
            placeholder="Selecione..."
            label="Indicadores"
            :disabled="loading"
            @update:model-value="handleIndicadorChange"
          />
        </div>
        <div class="filters__group">
          <label>Subindicador</label>
          <SelectSearch
            id="f-produto"
            :model-value="selectedSubindicador"
            :options="[{ id: '', label: 'Selecione...' }, ...subindicadores]"
            placeholder="Selecione..."
            label="Subindicador"
            :disabled="loading || !selectedIndicador || !hasSubindicadores"
            @update:model-value="handleSubindicadorChange"
          />
        </div>
        <div class="filters__group">
          <label>Status dos indicadores</label>
          <select id="f-status-kpi" v-model="selectedStatusKpi" class="input" :disabled="loading">
            <option value="todos">Todos</option>
            <option value="atingidos">Atingidos</option>
            <option value="nao">Não atingidos</option>
          </select>
        </div>
        <div class="filters__group">
          <label>Visão acumulada</label>
          <select
            id="f-visao"
            :value="accumulatedView"
            @change="handleViewChange(($event.target as HTMLSelectElement).value as 'mensal' | 'semestral' | 'anual')"
            class="input"
            :disabled="loading"
          >
            <option v-for="option in accumulatedViewOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>
    </div>
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
}

.card--filters {
  position: relative;
  overflow: visible;
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
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(179, 0, 0, 0.12);
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
  transition: max-height 0.35s ease;
}

.adv.is-open {
  max-height: 600px;
  overflow: visible;
}

.adv[aria-hidden='false'] {
  max-height: 600px;
  padding-top: 8px;
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

.ti-chevron-down {
  transition: transform 0.2s ease;
}

.ti-chevron-down.rotated {
  transform: rotate(180deg);
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
</style>

