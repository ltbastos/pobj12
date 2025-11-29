<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { getRanking, type RankingItem, type RankingFilters } from '../services/rankingService'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { formatINT } from '../utils/formatUtils'
import Filters from '../components/Filters.vue'
import TabsNavigation from '../components/TabsNavigation.vue'

const { filterState, period } = useGlobalFilters()

const rankingData = ref<RankingItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const rankingType = ref<'pobj' | 'produto' | 'historico'>('pobj')

const rankingFilters = computed<RankingFilters>(() => ({
  gerenteGestao: filterState.value.ggestao || undefined
}))

const loadRanking = async () => {
  loading.value = true
  error.value = null

  try {
    const data = await getRanking(rankingFilters.value)
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

// Carrega dados quando o componente é montado
onMounted(() => {
  loadRanking()
})

// Recarrega quando os filtros mudam
watch([filterState], () => {
  loadRanking()
}, { deep: true })

// Mapeamento de campos de chave e label por nível (como no app.js)
// Nota: o app.js usa 'gerenciaRegional' como chave, mas nosso DTO usa 'gerencia_id'
const RANKING_KEY_FIELDS: Record<string, string> = {
  segmento: 'segmento_id',
  diretoria: 'diretoria_id',
  gerencia: 'gerencia_id', // No app.js é 'gerenciaRegional', mas nosso DTO usa 'gerencia_id'
  agencia: 'agencia_id',
  gerenteGestao: 'gerente_gestao_id',
  gerente: 'gerente_id'
}

const RANKING_LABEL_FIELDS: Record<string, string> = {
  segmento: 'segmento',
  diretoria: 'diretoria_nome',
  gerencia: 'gerencia_nome',
  agencia: 'agencia_nome',
  gerenteGestao: 'gerente_gestao_nome',
  gerente: 'gerente_nome'
}

// Verifica se um valor é seleção padrão (Todos, Todas, etc)
const isDefaultSelection = (val: string | null | undefined): boolean => {
  if (!val) return true
  const normalized = val.toLowerCase().trim()
  return normalized === 'todos' || normalized === 'todas' || normalized === ''
}

// Obtém a seleção para um nível específico
const getRankingSelectionForLevel = (level: string): string | null => {
  const fs = filterState.value
  switch (level) {
    case 'segmento':
      return fs.segmento || null
    case 'diretoria':
      return fs.diretoria || null
    case 'gerencia':
      return fs.gerencia || null
    case 'agencia':
      return fs.agencia || null
    case 'gerenteGestao':
      return fs.ggestao || null
    case 'gerente':
      return fs.gerente || null
    default:
      return null
  }
}

// Determina o nível baseado nos filtros (ordem de mais específico para menos específico)
const rankingLevel = computed(() => {
  const fs = filterState.value
  // Verifica se os valores não são padrão (Todos, Todas, etc)
  const isSelected = (val: string | null | undefined) => {
    return !isDefaultSelection(val)
  }

  if (isSelected(fs.gerente)) return 'gerente'
  if (isSelected(fs.ggestao)) return 'gerenteGestao'
  if (isSelected(fs.agencia)) return 'agencia'
  if (isSelected(fs.gerencia)) return 'gerencia'
  if (isSelected(fs.diretoria)) return 'diretoria'
  if (isSelected(fs.segmento)) return 'segmento'
  // Default: gerenteGestao
  return 'gerenteGestao'
})

const levelNames: Record<string, string> = {
  segmento: 'Segmento',
  diretoria: 'Diretoria',
  gerencia: 'Regional',
  agencia: 'Agência',
  gerente: 'Gerente',
  gerenteGestao: 'Gerente de gestão'
}

// Função auxiliar para simplificar texto (remover acentos, espaços, etc)
const simplificarTexto = (text: string): string => {
  return text.toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '')
    .trim()
}

// Função auxiliar para verificar se um item corresponde à seleção
const matchesSelection = (filterValue: string, ...candidates: (string | null | undefined)[]): boolean => {
  if (isDefaultSelection(filterValue)) return true
  const normalizedFilter = filterValue.toLowerCase().trim()
  return candidates.some(candidate => {
    if (!candidate) return false
    return candidate.toString().toLowerCase().trim() === normalizedFilter ||
           simplificarTexto(candidate.toString()) === simplificarTexto(filterValue)
  })
}

// Agrupa dados por unidade para calcular pontos (similar ao aggRanking do app.js)
const groupedRanking = computed(() => {
  const level = rankingLevel.value
  const keyField = RANKING_KEY_FIELDS[level] || 'gerente_gestao_id'
  const labelField = RANKING_LABEL_FIELDS[level] || 'gerente_gestao_nome'

  // Verifica se há uma seleção no nível atual
  const selectionForLevel = getRankingSelectionForLevel(level)
  const hasSelectionForLevel = !!selectionForLevel && !isDefaultSelection(selectionForLevel)

  // Se há seleção no nível atual, precisa mostrar o nível abaixo
  // Por exemplo: se agência está selecionada, mostra gerentes de gestão dessa agência
  let targetLevel = level
  let targetKeyField = keyField
  let targetLabelField = labelField

  if (hasSelectionForLevel) {
    // Determina o nível abaixo baseado na hierarquia
    const levelHierarchy: Record<string, string> = {
      segmento: 'diretoria',
      diretoria: 'gerencia',
      gerencia: 'agencia',
      agencia: 'gerenteGestao',
      gerenteGestao: 'gerente',
      gerente: 'gerente' // Gerente é o último nível
    }

    const nextLevel = levelHierarchy[level]
    if (nextLevel && nextLevel !== level) {
      targetLevel = nextLevel
      targetKeyField = RANKING_KEY_FIELDS[targetLevel] || targetKeyField
      targetLabelField = RANKING_LABEL_FIELDS[targetLevel] || targetLabelField
    }
  }

  // Filtra os dados se houver seleção no nível atual
  let filteredData = rankingData.value
  if (hasSelectionForLevel && selectionForLevel) {
    filteredData = rankingData.value.filter(item => {
      // Verifica se o item corresponde à seleção do nível atual
      const itemKey = (item as any)[keyField]
      const itemLabel = (item as any)[labelField]
      return matchesSelection(selectionForLevel, itemKey, itemLabel)
    })
  }

  const groups = new Map<string, {
    unidade: string
    label: string
    real_mens: number
    real_acum: number
    meta_mens: number
    meta_acum: number
    count: number
  }>()

  filteredData.forEach(item => {
    // Obtém a chave e label baseado no nível alvo (pode ser o nível abaixo se houver seleção)
    const key = (item as any)[targetKeyField] || 'unknown'
    const label = (item as any)[targetLabelField] || key || '—'

    if (!groups.has(key)) {
      groups.set(key, {
        unidade: key,
        label,
        real_mens: 0,
        real_acum: 0,
        meta_mens: 0,
        meta_acum: 0,
        count: 0
      })
    }

    const group = groups.get(key)!
    const realizado = item.realizado_mensal || 0
    const meta = item.meta_mensal || 0

    group.real_mens += realizado
    group.real_acum += realizado
    group.meta_mens += meta
    group.meta_acum += meta
    group.count += 1
  })

  return Array.from(groups.values())
    .map(g => {
      // Se não há meta, usa o realizado diretamente normalizado
      // Normaliza para valores próximos ao print (divide por 100000 para obter valores como 8.2, 2.8)
      // Ou usa percentual se houver meta
      let p_mens = 0
      let p_acum = 0

      if (g.meta_mens > 0) {
        // Se tem meta, calcula percentual de atingimento
        const ating_mens = g.real_mens / g.meta_mens
        p_mens = ating_mens * 100
      } else {
        // Se não tem meta, usa o realizado normalizado (divide por 100000)
        p_mens = g.real_mens / 100000
      }

      if (g.meta_acum > 0) {
        const ating_acum = g.real_acum / g.meta_acum
        p_acum = ating_acum * 100
      } else {
        p_acum = g.real_acum / 100000
      }

      return {
        ...g,
        p_mens,
        p_acum
      }
    })
    .sort((a, b) => b.p_acum - a.p_acum)
})

const formatPoints = (value: number | null | undefined): string => {
  if (value == null || isNaN(value)) return '—'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1
  }).format(value)
}

// Determina se deve mascarar o nome (mostrar *****)
// Baseado na lógica do app.js: sempre mascara exceto o primeiro, o item do usuário, ou o que corresponde à seleção
const shouldMaskName = (item: any, index: number): boolean => {
  const level = rankingLevel.value
  const selectionForLevel = getRankingSelectionForLevel(level)
  const hasSelectionForLevel = !!selectionForLevel && !isDefaultSelection(selectionForLevel)

  // Não mascara o primeiro lugar
  if (index === 0) return false

  // Se há seleção no nível atual, verifica se o item corresponde à seleção
  if (hasSelectionForLevel && selectionForLevel) {
    // Não mascara se corresponde à seleção (pode ser por unidade ou label)
    const matches = matchesSelection(selectionForLevel, item.unidade, item.label)
    if (matches) return false
  }

  // Mascara todos os outros
  return true
}
</script>

<template>
  <div class="ranking-wrapper">
    <div class="container">
      <Filters />
      <TabsNavigation />

      <div class="ranking-view">
        <div v-if="loading" class="loading-state">
          <p>Carregando ranking...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <p>{{ error }}</p>
        </div>

        <div v-else-if="rankingData.length === 0" class="empty-state">
          <p v-if="filterState.ggestao">
            Sem dados de ranking disponíveis para o gerente de gestão selecionado.
          </p>
          <p v-else>
            Selecione um gerente de gestão nos filtros para visualizar o ranking.
          </p>
        </div>

        <div v-else class="ranking-content">
          <div class="card card--ranking">
            <header class="card__header rk-head">
              <div class="title-subtitle">
                <h3>Rankings</h3>
                <p class="muted">Compare diferentes visões respeitando os filtros aplicados.</p>
              </div>
              <div class="rk-head__controls">
                <div class="rk-control">
                  <label for="rk-type" class="muted">TIPO DE RANKING</label>
                  <select id="rk-type" v-model="rankingType" class="input input--sm">
                    <option value="pobj">Ranking POBJ</option>
                    <option value="produto">Ranking por produto</option>
                    <option value="historico">Histórico anual</option>
                  </select>
                </div>
              </div>
            </header>

            <div class="rk-summary" id="rk-summary">
              <div class="rk-badges">
                <span class="rk-badge rk-badge--primary">
                  <strong>Nível:</strong> {{ levelNames[rankingLevel] }}
                </span>
                <span class="rk-badge">
                  <strong>Número do grupo:</strong> —
                </span>
                <span class="rk-badge">
                  <strong>Quantidade de participantes:</strong> {{ formatINT(groupedRanking.length) }}
                </span>
              </div>
            </div>

            <div class="ranking-table-wrapper" id="rk-table">
              <table class="rk-table">
                <thead>
                  <tr>
                    <th class="pos-col">#</th>
                    <th class="unit-col">Unidade</th>
                    <th class="points-col">Pontos (mensal)</th>
                    <th class="points-col">Pontos (acumulado)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in groupedRanking"
                    :key="`${item.unidade}-${index}`"
                    class="rk-row"
                    :class="{ 'rk-row--top': index === 0 }"
                  >
                    <td class="pos-col">{{ formatINT(index + 1) }}</td>
                    <td class="unit-col rk-name">
                      {{ shouldMaskName(item, index) ? '*****' : item.label }}
                    </td>
                    <td class="points-col">{{ formatPoints(item.p_mens) }}</td>
                    <td class="points-col">{{ formatPoints(item.p_acum) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ranking-wrapper {
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
  --ring: 0 0 0 3px rgba(36, 107, 253, 0.12);
  --text-muted: #64748b;

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

.ranking-view {
  width: 100%;
  margin-top: 24px;
}

.loading-state,
.error-state,
.empty-state {
  padding: 48px 24px;
  text-align: center;
  color: var(--muted);
  background: var(--panel);
  border: 1px solid var(--stroke);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.error-state {
  color: var(--brand);
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
  font-weight: 600;
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
  padding: 8px 14px;
  background: rgba(36, 107, 253, 0.12);
  border-radius: 8px;
  font-size: 13px;
  color: var(--info);
  box-sizing: border-box;
}

.rk-badge--primary {
  background: var(--info);
  color: #ffffff;
}

.rk-badge strong {
  margin-right: 6px;
  font-weight: 600;
}

.ranking-table-wrapper {
  overflow-x: auto;
  padding: 16px;
  box-sizing: border-box;
}

.rk-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.rk-table thead {
  background: var(--bg);
  border-bottom: 2px solid var(--stroke);
}

.rk-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: var(--text);
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.rk-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--stroke);
  color: var(--text);
}

.rk-table tbody tr:hover {
  background: var(--bg);
}

.rk-table tbody tr:last-child td {
  border-bottom: none;
}

.rk-table tbody tr.rk-row--top {
  background: rgba(179, 0, 0, 0.05);
  font-weight: 600;
}

.rk-table tbody tr.rk-row--top td {
  color: var(--brand);
}

.pos-col {
  width: 60px;
  text-align: center;
  font-weight: 600;
  color: var(--text);
}

.unit-col {
  min-width: 200px;
  font-weight: 500;
  color: var(--text);
}

.rk-name {
  color: var(--text);
}

.date-col {
  color: var(--text-muted);
  font-size: 13px;
}

.rank-col {
  text-align: center;
  font-weight: 600;
  color: var(--brand);
}

.points-col {
  text-align: right;
  font-weight: 500;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

@media (max-width: 768px) {
  .rk-badges {
    flex-direction: column;
  }

  .rk-table {
    font-size: 13px;
  }

  .rk-table th,
  .rk-table td {
    padding: 10px 12px;
  }

  .unit-col {
    min-width: 150px;
  }
}
</style>

