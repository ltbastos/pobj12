<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { getRanking, type RankingItem, type RankingFilters } from '../services/rankingService'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { useInitCache } from '../composables/useInitCache'
import { formatINT } from '../utils/formatUtils'
import ErrorState from '../components/ErrorState.vue'
import EmptyState from '../components/EmptyState.vue'
import SelectSearch from '../components/SelectSearch.vue'
import type { FilterOption } from '../types'

const { filterState, period, filterTrigger, updateFilter } = useGlobalFilters()
const { initData, loadInit } = useInitCache()

const rankingData = ref<RankingItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const grupos = ref<FilterOption[]>([])
const selectedGrupo = ref<string>('')

// Normaliza opções de grupos
const normalizeOption = (item: any): FilterOption => {
  const id = String(item.id || '').trim()
  const nome = String(item.nome || item.label || id).trim()
  return { id, nome }
}

const buildOptions = (data: any[]): FilterOption[] => {
  if (!Array.isArray(data)) return []
  return data.map(normalizeOption).filter(opt => opt.id)
}

const sanitizeFilterValue = (value?: string): string | undefined => {
  if (!value) return undefined
  const trimmed = value.trim()
  if (!trimmed) return undefined
  const lower = trimmed.toLowerCase()
  if (lower === 'todos' || lower === 'todas' || lower === '') return undefined
  return trimmed
}

const rankingFilters = computed<RankingFilters>(() => {
  const filters: RankingFilters = {}
  
  const segmento = sanitizeFilterValue(filterState.value.segmento)
  const diretoria = sanitizeFilterValue(filterState.value.diretoria)
  const regional = sanitizeFilterValue(filterState.value.gerencia)
  const agencia = sanitizeFilterValue(filterState.value.agencia)
  const gerenteGestao = sanitizeFilterValue(filterState.value.ggestao)
  const gerente = sanitizeFilterValue(filterState.value.gerente)
  const grupo = sanitizeFilterValue(filterState.value.grupo)
  
  if (segmento) filters.segmento = segmento
  if (diretoria) filters.diretoria = diretoria
  if (regional) filters.regional = regional
  if (agencia) filters.agencia = agencia
  if (gerenteGestao) filters.gerenteGestao = gerenteGestao
  if (gerente) filters.gerente = gerente
  if (grupo) filters.grupo = grupo
  
  if (period.value?.start) {
    filters.dataInicio = period.value.start
  }
  if (period.value?.end) {
    filters.dataFim = period.value.end
  }
  
  return filters
})

const selectedLevel = computed<string>(() => {
  
  if (filterState.value.gerente && filterState.value.gerente.toLowerCase() !== 'todos') {
    return 'gerente'
  }
  if (filterState.value.ggestao && filterState.value.ggestao.toLowerCase() !== 'todos') {
    return 'gerenteGestao'
  }
  if (filterState.value.agencia && filterState.value.agencia.toLowerCase() !== 'todas') {
    return 'agencia'
  }
  if (filterState.value.gerencia && filterState.value.gerencia.toLowerCase() !== 'todas') {
    return 'gerencia'
  }
  if (filterState.value.diretoria && filterState.value.diretoria.toLowerCase() !== 'todas') {
    return 'diretoria'
  }
  if (filterState.value.segmento && filterState.value.segmento.toLowerCase() !== 'todos') {
    return 'segmento'
  }
  
  return 'gerenteGestao'
})

const levelLabel = computed<string>(() => {
  const labels: Record<string, string> = {
    'segmento': 'Segmento',
    'diretoria': 'Diretoria',
    'gerencia': 'Regional',
    'agencia': 'Agência',
    'gerenteGestao': 'Gerente de gestão',
    'gerente': 'Gerente'
  }
  return labels[selectedLevel.value] || 'Gerente de gestão'
})

const loadRanking = async () => {
  loading.value = true
  error.value = null

  try {
    const data = await getRanking(rankingFilters.value, selectedLevel.value)
    if (data) {
      rankingData.value = data
    } else {
      error.value = 'Não foi possível carregar os dados de ranking'
      rankingData.value = []
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Erro ao carregar ranking'
    rankingData.value = []
  } finally {
    loading.value = false
  }
}

const loadGrupos = async (): Promise<void> => {
  try {
    const data = await loadInit()
    if (data && data.grupos) {
      grupos.value = buildOptions(data.grupos)
    }
  } catch (err) {
    console.error('Erro ao carregar grupos:', err)
  }
}

const handleGrupoChange = (value: string): void => {
  selectedGrupo.value = value
  updateFilter('grupo', value === '' || value.toLowerCase() === 'todos' ? undefined : value)
}

onMounted(() => {
  loadGrupos()
  loadRanking()
  
  // Sincroniza o grupo selecionado com o filterState
  if (filterState.value.grupo) {
    selectedGrupo.value = filterState.value.grupo
  }
})

watch([filterState, filterTrigger], () => {
  // Limpa os dados antes de carregar novos
  const hasFilters = Object.keys(rankingFilters.value).length > 0
  
  if (!hasFilters) {
    rankingData.value = []
    error.value = null
  }
  
  loadRanking()
}, { deep: true })

const groupedRanking = computed(() => {
  return rankingData.value
})

const formatPoints = (value: number | null | undefined): string => {
  if (value == null || isNaN(value)) return '—'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}
</script>

<template>
  <div class="ranking-wrapper">
    <div class="ranking-view">
        <template v-if="loading">
          <div class="ranking-content">
            <div class="card card--ranking">
              <header class="card__header rk-head">
                <div class="skeleton skeleton--title" style="height: 24px; width: 150px; margin-bottom: 8px;"></div>
                <div class="skeleton skeleton--subtitle" style="height: 16px; width: 300px;"></div>
              </header>
              <div class="rk-summary">
                <div class="rk-badges">
                  <div class="skeleton skeleton--badge" style="height: 32px; width: 120px; border-radius: 6px;"></div>
                  <div class="skeleton skeleton--badge" style="height: 32px; width: 140px; border-radius: 6px;"></div>
                  <div class="skeleton skeleton--badge" style="height: 32px; width: 180px; border-radius: 6px;"></div>
                </div>
              </div>
              <div class="ranking-table-wrapper">
                <table class="rk-table">
                  <thead>
                    <tr>
                      <th class="pos-col">#</th>
                      <th class="unit-col">Unidade</th>
                      <th class="points-col">Pontos</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="i in 10" :key="i" class="rk-row" :class="{ 'rk-row--odd': i % 2 === 1 }">
                      <td class="pos-col"><div class="skeleton skeleton--text" style="height: 14px; width: 24px; margin: 0 auto;"></div></td>
                      <td class="unit-col"><div class="skeleton skeleton--text" style="height: 14px; width: 80%;"></div></td>
                      <td class="points-col"><div class="skeleton skeleton--text" style="height: 14px; width: 70px; margin-left: auto;"></div></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
        </template>

        
        <template v-else>
          <ErrorState v-if="error" :message="error" />

          <EmptyState
            v-else-if="rankingData.length === 0"
            title="Nenhum ranking disponível"
            :message="filterState.ggestao
              ? 'Sem dados de ranking disponíveis para o gerente de gestão selecionado.'
              : 'Selecione um gerente de gestão nos filtros para visualizar o ranking.'"
          />

        <div v-else class="ranking-content">
          <div class="card card--ranking">
            <header class="card__header rk-head">
              <div class="title-subtitle">
                <h3>Rankings</h3>
                <p class="muted">Compare diferentes visões respeitando os filtros aplicados.</p>
              </div>
            </header>

            <div class="rk-summary" id="rk-summary">
              <div class="rk-badges">
                <span class="rk-badge">
                  <strong>Nível de agrupamento:</strong> {{ levelLabel }}
                </span>
                <span class="rk-badge">
                  <strong>Quantidade de participantes:</strong> {{ formatINT(groupedRanking.length) }}
                </span>
                <span class="rk-badge rk-badge--filter">
                  <label for="f-grupo-ranking" style="margin-right: 8px; font-weight: 600;">Grupo:</label>
                  <SelectSearch
                    id="f-grupo-ranking"
                    :model-value="selectedGrupo"
                    :options="[{ id: '', nome: 'Todos' }, ...grupos]"
                    placeholder="Todos"
                    label="Grupo"
                    :disabled="loading"
                    @update:model-value="handleGrupoChange"
                  />
                </span>
              </div>
            </div>

            <div class="ranking-table-wrapper" id="rk-table">
              <table class="rk-table">
                <thead>
                  <tr>
                    <th class="pos-col">#</th>
                    <th class="unit-col">Unidade</th>
                    <th class="points-col">Pontos</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in groupedRanking"
                    :key="`${item.unidade}-${index}`"
                    class="rk-row"
                    :class="{ 'rk-row--top': index === 0 }"
                  >
                    <td class="pos-col">{{ formatINT(item.position ?? index + 1) }}</td>
                    <td class="unit-col rk-name">
                      {{ item.displayLabel ?? item.label ?? '—' }}
                    </td>
                    <td class="points-col">{{ formatPoints(item.pontos) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        </template>
      </div>
    </div>
</template>

<style scoped>
.ranking-wrapper {
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
  --ring: 0 0 0 3px rgba(204, 9, 47, 0.12);
  --text-muted: var(--brad-color-gray-dark, #999);

  min-height: 100vh;
  width: 100%;
  padding: 20px 0;
  color: var(--text);
  font-family: var(--brad-font-family);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  box-sizing: border-box;
}


.ranking-view {
  width: 100%;
  margin-top: 24px;
}

.loading-state {
  padding: 48px 24px;
  text-align: center;
  color: var(--muted);
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin: 16px;
}

.ranking-content {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.card {
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 16px;
  margin-bottom: 12px;
  padding-top: 12px;
  box-sizing: border-box;
  transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
}

.card--ranking {
  padding: 0;
}

.card__header {
  padding: 16px;
  border-bottom: 1px solid var(--stroke);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
}

.rk-head {
  flex-wrap: wrap;
}

.title-subtitle h3 {
  margin: 0 0 4px 0;
  font-size: 20px;
  font-weight: var(--brad-font-weight-bold, 700);
  color: var(--text);
  line-height: 1.2;
}

.title-with-icon {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 4px;
}

.title-with-icon h3 {
  margin: 0;
}

.title-subtitle .muted {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
  line-height: 1.4;
}

.rk-head__controls {
  display: flex;
  gap: 16px;
  align-items: flex-end;
}

.rk-control {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rk-control label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--muted);
  font-weight: var(--brad-font-weight-semibold, 600);
}

.input {
  padding: 8px 12px;
  border: 1px solid var(--stroke);
  border-radius: 8px;
  background: var(--panel);
  color: var(--text);
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
}

.input--sm {
  padding: 6px 10px;
  font-size: 13px;
}

.rk-summary {
  padding: 16px;
  border-bottom: 1px solid var(--stroke);
}

.rk-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.rk-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: var(--brad-color-primary-xlight, rgba(252, 231, 236, 0.8));
  border-radius: 8px;
  font-size: 13px;
  color: var(--brad-color-primary, #cc092f);
  box-sizing: border-box;
}

.rk-badge--primary {
  background: var(--brad-color-primary, #cc092f);
  color: var(--brad-color-on-bg-primary, #fff);
}

.rk-badge--level {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: var(--brad-color-primary-xlight, rgba(252, 231, 236, 0.8));
  border-radius: 8px;
  font-size: 13px;
  color: var(--brad-color-primary, #cc092f);
  box-sizing: border-box;
}

.rk-badge--level strong {
  margin-right: 0;
  font-weight: var(--brad-font-weight-semibold, 600);
  white-space: nowrap;
}

.rk-badge--level :deep(.select) {
  min-width: 180px;
}

.rk-badge--level :deep(.select__trigger) {
  padding: 6px 10px;
  font-size: 13px;
  border: 1px solid var(--brad-color-primary, #cc092f);
  background: var(--panel, #fff);
  color: var(--text, #000);
}

.rk-badge strong {
  margin-right: 6px;
  font-weight: var(--brad-font-weight-semibold, 600);
}

.rk-badge--filter {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
}

.rk-badge--filter :deep(.select) {
  min-width: 150px;
}

.rk-badge--filter :deep(.select__trigger) {
  padding: 6px 10px;
  font-size: 13px;
  border: 1px solid var(--brad-color-primary, #cc092f);
  background: var(--panel, #fff);
  color: var(--text, #000);
}

.ranking-table-wrapper {
  overflow-x: auto;
  width: 100%;
  max-width: 100%;
  padding: 16px;
  box-sizing: border-box;
}

.rk-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border: 1px solid var(--stroke, #e5e7eb);
  border-radius: 14px;
  overflow: hidden;
  box-sizing: border-box;
}

.rk-table thead {
  background: #fbfcff;
  position: sticky;
  top: 0;
  z-index: 1;
}

.rk-table th {
  padding: 10px 8px;
  text-align: left;
  font-weight: 800;
  color: #475569;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.rk-table th.pos-col {
  text-align: center;
}

.rk-table th.points-col {
  text-align: right;
  padding-right: 24px;
}

.rk-table td {
  padding: 10px 8px;
  border-bottom: 1px solid #f1f5f9;
  color: #111827;
  vertical-align: middle;
  font-size: 13px;
}

.rk-table tbody tr {
  transition: background-color 0.2s ease;
}

.rk-table tbody tr:nth-child(odd) {
  background: rgba(248, 250, 252, 0.65);
}

.rk-table tbody tr:hover {
  background: #fcfdff;
}

.rk-table tbody tr:last-child td {
  border-bottom: none;
}

.rk-table tbody tr.rk-row--top {
  background: rgba(252, 231, 236, 0.4);
}

.rk-table tbody tr.rk-row--top td {
  color: var(--brad-color-primary, #cc092f);
  font-weight: 700;
}

.pos-col {
  width: 80px;
  min-width: 80px;
  max-width: 80px;
  text-align: center;
  font-weight: 700;
  color: #111827;
  font-size: 13px;
  position: relative;
  font-variant-numeric: tabular-nums;
}

.pos-medal {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.pos-medal--gold :deep(svg) {
  color: #ffd700;
}

.pos-medal--silver :deep(svg) {
  color: #c0c0c0;
}

.pos-medal--bronze :deep(svg) {
  color: #cd7f32;
}

.unit-col {
  width: auto;
  min-width: 300px;
  font-weight: 700;
  color: #0f172a;
  font-size: 13px;
  word-wrap: break-word;
  overflow-wrap: break-word;
  text-align: left;
}

.rk-name {
  color: #0f172a;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.date-col {
  color: var(--text-muted);
  font-size: 13px;
}

.rank-col {
  text-align: center;
  font-weight: var(--brad-font-weight-semibold, 600);
  color: var(--brad-color-primary, #cc092f);
}

.grupo-col {
  width: 150px;
  min-width: 150px;
  max-width: 150px;
  text-align: left;
  font-weight: var(--brad-font-weight-medium, 500);
  color: var(--text);
  font-size: 14px;
}

.points-col {
  width: 140px;
  min-width: 140px;
  max-width: 140px;
  text-align: right;
  font-weight: 800;
  color: #111827;
  font-variant-numeric: tabular-nums;
  font-size: 13px;
  padding-right: 24px;
  white-space: nowrap;
}


@media (max-width: 768px) {
  .rk-badges {
    flex-direction: column;
  }

  .ranking-table-wrapper {
    padding: 12px 16px;
  }

  .rk-table {
    font-size: 14px;
    min-width: 500px;
  }

  .rk-table th {
    padding: 12px 16px;
    font-size: 11px;
  }

  .rk-table td {
    padding: 14px 16px;
    font-size: 14px;
  }

  .pos-col {
    width: 60px;
    min-width: 60px;
    max-width: 60px;
    font-size: 14px;
  }

  .unit-col {
    min-width: 200px;
    font-size: 14px;
  }

  .grupo-col {
    width: 120px;
    min-width: 120px;
    max-width: 120px;
    font-size: 13px;
  }

  .points-col {
    width: 120px;
    min-width: 120px;
    max-width: 120px;
    font-size: 14px;
    padding-right: 16px;
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

.skeleton--text {
  border-radius: 4px;
}

.rk-row--odd {
  background: rgba(248, 250, 252, 0.65);
}
</style>

