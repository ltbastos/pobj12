<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useGlobalFilters } from '../composables/useGlobalFilters'
import { formatINT, formatPoints } from '../utils/formatUtils'
import Filters from '../components/Filters.vue'
import TabsNavigation from '../components/TabsNavigation.vue'

const { filterState } = useGlobalFilters()

// Estado da campanha
const loading = ref(false)
const error = ref<string | null>(null)
const campanhas = ref<any[]>([])
const sprintSelecionada = ref<string | null>(null)
const sprintAtual = ref<any>(null)

// Dados da campanha
const headlineStats = ref<any[]>([])
const kpis = ref<any[]>([])
const rankingData = ref<any[]>([])
const teamSimulator = ref<any>(null)
const individualSimulator = ref<any>(null)

// Estado dos simuladores
const teamValues = ref<Record<string, Record<string, number>>>({})
const individualValues = ref<Record<string, Record<string, Record<string, number>>>>({})
const teamPresets = ref<Record<string, string>>({})
const individualPresets = ref<Record<string, string>>({})
const individualProfileSelected = ref<string | null>(null)

// Resultados calculados
const teamResult = ref<any>(null)
const individualResult = ref<any>(null)

// Dados mockados
const mockCampanhas = [
  {
    id: 'sprint-1',
    label: 'Campanha Q1 2024',
    cycle: 'Q1 2024',
    note: 'Campanha de incentivo para o primeiro trimestre.',
    period: {
      start: '2024-01-01',
      end: '2024-03-31'
    },
    headStats: [
      { label: 'Participantes', value: '1.234', sub: 'equipes ativas' },
      { label: 'Meta Total', value: 'R$ 2,5M', sub: 'em vendas' },
      { label: 'Realizado', value: 'R$ 1,8M', sub: '72% da meta' }
    ],
    summary: [
      { id: 'equipes', label: 'Equipes elegíveis', value: 45, total: 60 },
      { id: 'media', label: 'Média de pontos', value: 125.5, unit: 'pts' },
      { id: 'recorde', label: 'Recorde', value: 198.3, unit: 'pts', complement: 'Equipe Alpha' }
    ],
    team: {
      minThreshold: 90,
      superThreshold: 120,
      cap: 150,
      eligibilityMinimum: 100,
      defaultPreset: 'meta',
      indicators: [
        { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 40, hint: 'Operações direcionadas, BB Giro e BNDES.', default: 100 },
        { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 30, hint: 'Centralização de caixa e TPV eletrônico.', default: 100 },
        { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 30, hint: 'Abertura de contas e ativação digital.', default: 100 }
      ],
      presets: [
        { id: 'minimo', label: 'Mínimo obrigatório (90%)', values: { linhas: 90, cash: 90, conquista: 90 } },
        { id: 'meta', label: 'Meta do sprint (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
        { id: 'stretch', label: 'Meta esticada (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
      ]
    },
    individual: {
      profiles: [
        {
          id: 'negocios',
          label: 'Negócios',
          description: 'Carteiras MPE com foco em relacionamento consultivo.',
          minThreshold: 90,
          superThreshold: 120,
          cap: 150,
          eligibilityMinimum: 100,
          defaultPreset: 'meta',
          indicators: [
            { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 40, default: 100 },
            { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 30, default: 100 },
            { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 30, default: 100 }
          ],
          presets: [
            { id: 'minimo', label: '90% em todos', values: { linhas: 90, cash: 90, conquista: 90 } },
            { id: 'meta', label: 'Meta (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
            { id: 'destaque', label: 'Stretch (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
          ]
        },
        {
          id: 'empresas',
          label: 'Empresas',
          description: 'Grandes empresas e governo com foco em volume.',
          minThreshold: 90,
          superThreshold: 120,
          cap: 150,
          eligibilityMinimum: 100,
          defaultPreset: 'meta',
          indicators: [
            { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 45, default: 100 },
            { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 35, default: 100 },
            { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 20, default: 100 }
          ],
          presets: [
            { id: 'minimo', label: '90% em todos', values: { linhas: 90, cash: 90, conquista: 90 } },
            { id: 'meta', label: 'Meta (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
            { id: 'stretch', label: 'Stretch (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
          ]
        }
      ]
    }
  },
  {
    id: 'sprint-2',
    label: 'Campanha Q2 2024',
    cycle: 'Q2 2024',
    note: 'Campanha de incentivo para o segundo trimestre.',
    period: {
      start: '2024-04-01',
      end: '2024-06-30'
    },
    headStats: [
      { label: 'Participantes', value: '1.456', sub: 'equipes ativas' },
      { label: 'Meta Total', value: 'R$ 3,0M', sub: 'em vendas' },
      { label: 'Realizado', value: 'R$ 2,1M', sub: '70% da meta' }
    ],
    summary: [
      { id: 'equipes', label: 'Equipes elegíveis', value: 52, total: 65 },
      { id: 'media', label: 'Média de pontos', value: 132.8, unit: 'pts' },
      { id: 'recorde', label: 'Recorde', value: 205.7, unit: 'pts', complement: 'Equipe Beta' }
    ],
    team: {
      minThreshold: 90,
      superThreshold: 120,
      cap: 150,
      eligibilityMinimum: 100,
      defaultPreset: 'meta',
      indicators: [
        { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 40, hint: 'Operações direcionadas, BB Giro e BNDES.', default: 100 },
        { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 30, hint: 'Centralização de caixa e TPV eletrônico.', default: 100 },
        { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 30, hint: 'Abertura de contas e ativação digital.', default: 100 }
      ],
      presets: [
        { id: 'minimo', label: 'Mínimo obrigatório (90%)', values: { linhas: 90, cash: 90, conquista: 90 } },
        { id: 'meta', label: 'Meta do sprint (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
        { id: 'stretch', label: 'Meta esticada (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
      ]
    },
    individual: {
      profiles: [
        {
          id: 'negocios',
          label: 'Negócios',
          description: 'Carteiras MPE com foco em relacionamento consultivo.',
          minThreshold: 90,
          superThreshold: 120,
          cap: 150,
          eligibilityMinimum: 100,
          defaultPreset: 'meta',
          indicators: [
            { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 40, default: 100 },
            { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 30, default: 100 },
            { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 30, default: 100 }
          ],
          presets: [
            { id: 'minimo', label: '90% em todos', values: { linhas: 90, cash: 90, conquista: 90 } },
            { id: 'meta', label: 'Meta (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
            { id: 'destaque', label: 'Stretch (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
          ]
        },
        {
          id: 'empresas',
          label: 'Empresas',
          description: 'Grandes empresas e governo com foco em volume.',
          minThreshold: 90,
          superThreshold: 120,
          cap: 150,
          eligibilityMinimum: 100,
          defaultPreset: 'meta',
          indicators: [
            { id: 'linhas', label: 'Linhas governamentais', short: 'Linhas', weight: 45, default: 100 },
            { id: 'cash', label: 'Cash (TPV)', short: 'Cash', weight: 35, default: 100 },
            { id: 'conquista', label: 'Conquista cliente PJ', short: 'Conquista', weight: 20, default: 100 }
          ],
          presets: [
            { id: 'minimo', label: '90% em todos', values: { linhas: 90, cash: 90, conquista: 90 } },
            { id: 'meta', label: 'Meta (100%)', values: { linhas: 100, cash: 100, conquista: 100 } },
            { id: 'stretch', label: 'Stretch (120%)', values: { linhas: 120, cash: 120, conquista: 120 } }
          ]
        }
      ]
    }
  }
]

const mockRankingData = [
  { rank: 1, unidade: 'Agência Centro', label: 'Agência Centro', linhas: 125, cash: 118, conquista: 132, totalPoints: 128.5, finalStatus: 'Elegível' },
  { rank: 2, unidade: 'Agência Norte', label: 'Agência Norte', linhas: 120, cash: 115, conquista: 128, totalPoints: 120.5, finalStatus: 'Elegível' },
  { rank: 3, unidade: 'Agência Sul', label: 'Agência Sul', linhas: 115, cash: 110, conquista: 125, totalPoints: 115.5, finalStatus: 'Elegível' },
  { rank: 4, unidade: 'Agência Leste', label: 'Agência Leste', linhas: 110, cash: 105, conquista: 120, totalPoints: 111.5, finalStatus: 'Elegível' },
  { rank: 5, unidade: 'Agência Oeste', label: 'Agência Oeste', linhas: 105, cash: 100, conquista: 115, totalPoints: 106.5, finalStatus: 'Elegível' },
  { rank: 6, unidade: 'Agência Alpha', label: 'Agência Alpha', linhas: 100, cash: 95, conquista: 110, totalPoints: 101.5, finalStatus: 'Elegível' },
  { rank: 7, unidade: 'Agência Beta', label: 'Agência Beta', linhas: 95, cash: 90, conquista: 105, totalPoints: 96.5, finalStatus: 'Elegível' },
  { rank: 8, unidade: 'Agência Gamma', label: 'Agência Gamma', linhas: 90, cash: 88, conquista: 100, totalPoints: 92.5, finalStatus: 'Elegível' },
  { rank: 9, unidade: 'Agência Delta', label: 'Agência Delta', linhas: 88, cash: 85, conquista: 98, totalPoints: 90.5, finalStatus: 'Elegível' },
  { rank: 10, unidade: 'Agência Epsilon', label: 'Agência Epsilon', linhas: 85, cash: 82, conquista: 95, totalPoints: 87.5, finalStatus: 'Ajustar' }
]

// Carrega as campanhas disponíveis (mock)
const loadCampanhas = () => {
  loading.value = true
  error.value = null

  setTimeout(() => {
    campanhas.value = mockCampanhas
    if (campanhas.value.length > 0 && !sprintSelecionada.value) {
      sprintSelecionada.value = campanhas.value[0].id
    }
    loading.value = false
  }, 300)
}

// Carrega os dados da sprint selecionada (mock)
const loadSprintData = () => {
  if (!sprintSelecionada.value) return

  loading.value = true
  error.value = null

  setTimeout(() => {
    const sprint = campanhas.value.find(s => s.id === sprintSelecionada.value)
    if (!sprint) {
      error.value = 'Sprint não encontrada'
      loading.value = false
      return
    }

    sprintAtual.value = sprint
    headlineStats.value = sprint.headStats || []
    kpis.value = sprint.summary || []
    rankingData.value = mockRankingData
    teamSimulator.value = sprint.team || null
    individualSimulator.value = sprint.individual || null

    // Inicializa valores dos simuladores
    if (sprint.team) {
      const teamVals = ensureTeamValues(sprint)
      teamResult.value = computeCampaignScore(sprint.team, teamVals)
    }

    if (sprint.individual?.profiles?.length) {
      const firstProfile = sprint.individual.profiles[0]
      individualProfileSelected.value = firstProfile.id
      const individualVals = ensureIndividualValues(sprint, firstProfile)
      individualResult.value = computeCampaignScore(firstProfile, individualVals)
    }

    loading.value = false
  }, 200)
}

// Perfil individual selecionado
const currentIndividualProfile = computed(() => {
  if (!sprintAtual.value?.individual?.profiles || !individualProfileSelected.value) return null
  return sprintAtual.value.individual.profiles.find((p: any) => p.id === individualProfileSelected.value) || null
})

// Valores atuais do simulador de equipe
const currentTeamValues = computed(() => {
  if (!sprintAtual.value?.team) return {}
  return ensureTeamValues(sprintAtual.value)
})

// Valores atuais do simulador individual
const currentIndividualValues = computed(() => {
  if (!sprintAtual.value || !currentIndividualProfile.value) return {}
  return ensureIndividualValues(sprintAtual.value, currentIndividualProfile.value)
})

// Preset ativo do simulador de equipe
const activeTeamPreset = computed(() => {
  if (!sprintAtual.value) return 'custom'
  return teamPresets.value[sprintAtual.value.id] || 'custom'
})

// Preset ativo do simulador individual
const activeIndividualPreset = computed(() => {
  if (!sprintAtual.value || !currentIndividualProfile.value) return 'custom'
  const key = `${sprintAtual.value.id}:${currentIndividualProfile.value.id}`
  return individualPresets.value[key] || 'custom'
})

// Formata validade da campanha
const formatValidity = (period: any): string => {
  if (!period) return 'Vigência não informada'
  
  const start = period.start ? new Date(period.start).toLocaleDateString('pt-BR') : null
  const end = period.end ? new Date(period.end).toLocaleDateString('pt-BR') : null
  
  if (start && end) return `Vigência da campanha: de ${start} até ${end}`
  if (start) return `Vigência da campanha a partir de ${start}`
  if (end) return `Vigência da campanha até ${end}`
  return 'Vigência não informada'
}

const validityText = computed(() => {
  return formatValidity(sprintAtual.value?.period)
})

const cycleLabel = computed(() => {
  return sprintAtual.value?.label || sprintAtual.value?.cycle || ''
})

const noteText = computed(() => {
  const base = sprintAtual.value?.note || ''
  const visibleUnits = rankingData.value.length
  const pluralLabel = 'unidades'
  const suffix = visibleUnits
    ? ` Exibindo ${formatINT(visibleUnits)} ${pluralLabel} filtradas.`
    : ' Nenhuma unidade encontrada para o filtro atual.'
  return `${base}${suffix}`.trim()
})

// Formata percentual
const formatPercent = (value: number | null | undefined): string => {
  if (value == null || isNaN(value)) return '—'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1
  }).format(value) + '%'
}

// Formata valor único
const formatOne = (value: number | null | undefined): string => {
  if (value == null || isNaN(value)) return '—'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1
  }).format(value)
}

// Função auxiliar para converter para número
const toNumber = (value: any): number => {
  if (value === null || value === undefined || value === '') return 0
  const n = typeof value === 'string' ? parseFloat(value) : value
  return Number.isFinite(n) ? n : 0
}

// Calcula o score da campanha (baseado no app.js)
const computeCampaignScore = (config: any, values: Record<string, number>) => {
  const indicators = config?.indicators || []
  const min = toNumber(config?.minThreshold ?? 90)
  const stretch = toNumber(config?.superThreshold ?? (min + 20))
  const cap = toNumber(config?.cap ?? 150)
  const minTotal = toNumber(config?.eligibilityMinimum ?? 100) || 100

  const rows = indicators.map((ind: any) => {
    const raw = Math.max(0, toNumber(values?.[ind.id] ?? ind.default ?? 0))
    const capped = Math.min(cap, raw)
    const points = (toNumber(ind.weight) * capped) / 100
    let statusText = 'Crítico'
    let statusClass = 'status-pill--alert'
    if (raw >= stretch) {
      statusText = 'Parabéns'
      statusClass = 'status-pill--great'
    } else if (raw >= min) {
      statusText = 'Elegível'
      statusClass = 'status-pill--ok'
    } else if (raw >= Math.max(0, min - 10)) {
      statusText = 'Ajustar'
      statusClass = 'status-pill--warn'
    }
    return { ...ind, pct: raw, capped, points, statusText, statusClass }
  })

  const totalPoints = rows.reduce((acc: number, row: any) => acc + row.points, 0)
  const hasAllMin = rows.every((row: any) => row.pct >= min)
  const hasAllStretch = rows.every((row: any) => row.pct >= stretch)
  const eligible = hasAllMin && totalPoints >= minTotal

  let finalStatus = 'Não elegível'
  let finalClass = 'status-tag--alert'
  if (hasAllStretch && totalPoints >= minTotal) {
    finalStatus = 'Parabéns'
    finalClass = 'status-tag--great'
  } else if (eligible) {
    finalStatus = 'Elegível'
    finalClass = 'status-tag--ok'
  } else if (hasAllMin) {
    finalStatus = 'Ajustar foco'
    finalClass = 'status-tag--warn'
  }

  const shortfall = Math.max(0, minTotal - totalPoints)
  const progressPct = minTotal ? Math.max(0, Math.min(1, totalPoints / minTotal)) : 0
  const progressLabel = shortfall > 0
    ? `${formatINT(Math.round(shortfall))} pts para elegibilidade`
    : (hasAllStretch ? 'Acima do stretch' : 'Meta mínima atingida')

  return { rows, totalPoints, finalStatus, finalClass, progressPct, progressLabel, shortfall, hasAllMin, hasAllStretch }
}

// Garante valores iniciais para o simulador de equipe
const ensureTeamValues = (sprint: any): Record<string, number> => {
  if (!sprint?.team) return {}
  if (!teamValues.value[sprint.id]) {
    const base: Record<string, number> = {}
    ;(sprint.team.indicators || []).forEach((ind: any) => {
      base[ind.id] = toNumber(ind.default ?? 100)
    })
    teamValues.value[sprint.id] = base
    teamPresets.value[sprint.id] = sprint.team.defaultPreset || (sprint.team.presets?.[0]?.id || 'custom')
  }
  return teamValues.value[sprint.id] || {}
}

// Garante valores iniciais para o simulador individual
const ensureIndividualValues = (sprint: any, profile: any): Record<string, number> => {
  if (!sprint || !profile) return {}
  const key = `${sprint.id}:${profile.id}`
  
  // Garante que existe o objeto para o sprint
  if (!individualValues.value[sprint.id]) {
    individualValues.value[sprint.id] = {}
  }
  
  // Garante que existe o objeto para o perfil
  const sprintValues = individualValues.value[sprint.id]
  if (!sprintValues) {
    return {}
  }
  
  if (!sprintValues[profile.id]) {
    const base: Record<string, number> = {}
    ;(profile.indicators || []).forEach((ind: any) => {
      base[ind.id] = toNumber(ind.default ?? 100)
    })
    sprintValues[profile.id] = base
    individualPresets.value[key] = profile.defaultPreset || (profile.presets?.[0]?.id || 'custom')
  }
  
  return sprintValues[profile.id] || {}
}

// Detecta qual preset corresponde aos valores atuais
const detectPresetMatch = (values: Record<string, number>, presets: any[]): string | null => {
  if (!values || !Array.isArray(presets)) return null
  return presets.find(preset => {
    const pairs = Object.entries(preset.values || {})
    return pairs.every(([key, val]) => Math.round(toNumber(val)) === Math.round(toNumber(values[key])))
  })?.id || null
}

// Atualiza valores do simulador de equipe
const updateTeamValues = (sprint: any, indicatorId: string, value: number) => {
  const values = ensureTeamValues(sprint)
  values[indicatorId] = value
  teamResult.value = computeCampaignScore(sprint.team, values)
  const presetMatch = detectPresetMatch(values, sprint.team.presets || [])
  teamPresets.value[sprint.id] = presetMatch || 'custom'
}

// Aplica preset no simulador de equipe
const applyTeamPreset = (sprint: any, presetId: string) => {
  const preset = sprint.team.presets?.find((p: any) => p.id === presetId)
  if (!preset) return
  const values = ensureTeamValues(sprint)
  Object.entries(preset.values || {}).forEach(([key, val]) => {
    values[key] = toNumber(val)
  })
  teamPresets.value[sprint.id] = presetId
  teamResult.value = computeCampaignScore(sprint.team, values)
}

// Atualiza valores do simulador individual
const updateIndividualValues = (sprint: any, profile: any, indicatorId: string, value: number) => {
  const values = ensureIndividualValues(sprint, profile)
  values[indicatorId] = value
  individualResult.value = computeCampaignScore(profile, values)
  const key = `${sprint.id}:${profile.id}`
  const presetMatch = detectPresetMatch(values, profile.presets || [])
  individualPresets.value[key] = presetMatch || 'custom'
}

// Aplica preset no simulador individual
const applyIndividualPreset = (sprint: any, profile: any, presetId: string) => {
  const preset = profile.presets?.find((p: any) => p.id === presetId)
  if (!preset) return
  const values = ensureIndividualValues(sprint, profile)
  Object.entries(preset.values || {}).forEach(([key, val]) => {
    values[key] = toNumber(val)
  })
  const key = `${sprint.id}:${profile.id}`
  individualPresets.value[key] = presetId
  individualResult.value = computeCampaignScore(profile, values)
}

// Seleciona perfil individual
const selectIndividualProfile = (sprint: any, profileId: string) => {
  individualProfileSelected.value = profileId
  const profile = sprint.individual?.profiles?.find((p: any) => p.id === profileId)
  if (profile) {
    const values = ensureIndividualValues(sprint, profile)
    individualResult.value = computeCampaignScore(profile, values)
  }
}

// Carrega dados quando o componente é montado
onMounted(() => {
  loadCampanhas()
})

// Recarrega quando a sprint muda
watch(sprintSelecionada, () => {
  loadSprintData()
})

// Recarrega quando os filtros mudam (mock - apenas para demonstração)
watch([filterState], () => {
  // Em produção, aqui recarregaria os dados com os filtros aplicados
  // Por enquanto, apenas mantém os dados mockados
}, { deep: true })
</script>

<template>
  <div class="campanhas-wrapper">
    <div class="container">
      <Filters />
      <TabsNavigation />

      <div class="campanhas-view">
        <div v-if="loading && !sprintAtual" class="loading-state">
          <p>Carregando campanhas...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <p>{{ error }}</p>
        </div>

        <div v-else-if="campanhas.length === 0" class="empty-state">
          <p>Nenhuma campanha disponível.</p>
        </div>

        <div v-else class="campanhas-content">
          <section class="card card--campanhas">
            <!-- Header com seletor de sprint -->
            <header class="card__header camp-header">
              <div class="title-subtitle">
                <h3>Campanhas</h3>
                <p class="muted">Acompanhe o desempenho das campanhas e simule cenários.</p>
              </div>
              <div class="camp-header__controls">
                <label for="campanha-sprint" class="muted">CAMPANHA</label>
                <select 
                  id="campanha-sprint" 
                  v-model="sprintSelecionada" 
                  class="input input--sm"
                >
                  <option 
                    v-for="sprint in campanhas" 
                    :key="sprint.id" 
                    :value="sprint.id"
                  >
                    {{ sprint.label }}
                  </option>
                </select>
              </div>
            </header>

            <!-- Hero section com informações da campanha -->
            <div v-if="sprintAtual" class="camp-hero">
              <div class="camp-hero__info">
                <p id="camp-cycle">{{ cycleLabel }}</p>
                <div class="camp-period camp-period--validity">
                  <i class="ti ti-calendar"></i>
                  <span id="camp-validity" class="camp-validity">{{ validityText }}</span>
                </div>
                <p id="camp-note" class="muted">{{ noteText }}</p>
              </div>
              <div class="camp-hero__stats" id="camp-headline">
                <div 
                  v-for="(stat, index) in headlineStats" 
                  :key="index"
                  class="camp-hero__stat"
                >
                  <span>{{ stat.label }}</span>
                  <strong>{{ stat.value }}</strong>
                  <small v-if="stat.sub">{{ stat.sub }}</small>
                </div>
              </div>
            </div>

            <!-- KPIs -->
            <div v-if="sprintAtual && kpis.length > 0" class="camp-kpi-grid" id="camp-kpis">
              <div 
                v-for="(kpi, index) in kpis" 
                :key="index"
                class="camp-kpi"
              >
                <span>{{ kpi.label }}</span>
                <template v-if="kpi.total != null">
                  <strong>{{ formatINT(kpi.value) }} / {{ formatINT(kpi.total) }}</strong>
                  <small>de {{ formatINT(kpi.total) }} monitoradas</small>
                </template>
                <template v-else-if="kpi.unit === 'pts'">
                  <strong>{{ formatPoints(kpi.value, { withUnit: true }) }}</strong>
                  <small v-if="kpi.complement">{{ kpi.complement }}</small>
                </template>
                <template v-else-if="kpi.text">
                  <strong>{{ kpi.text }}</strong>
                </template>
                <template v-else>
                  <strong>{{ formatOne(kpi.value) }}</strong>
                  <small v-if="kpi.complement">{{ kpi.complement }}</small>
                </template>
              </div>
            </div>

            <!-- Simuladores -->
            <div v-if="sprintAtual && (teamSimulator || individualSimulator)" class="card card--camp-sims">
              <div class="sim-grid">
                <!-- Simulador de Equipe -->
                <div v-if="teamSimulator" id="sim-equipe" class="sim-card">
                  <div class="sim-card__head">
                    <div class="sim-card__title">
                      <h5>Simulador de equipe</h5>
                      <button 
                        type="button" 
                        class="sim-help" 
                        aria-label="Como funciona o simulador de equipe"
                        title="Ajuste os percentuais de cada indicador entre 0% e 150%. A equipe se torna elegível com todos os indicadores a partir de 90% e somando pelo menos 100 pontos."
                      >
                        <i class="ti ti-info-circle"></i>
                      </button>
                    </div>
                    <p>Defina o atingimento de cada indicador para estimar a pontuação e a elegibilidade da equipe.</p>
                    <p class="sim-hint">Elegível com todos os indicadores ≥ 90% e pelo menos 100 pontos.</p>
                  </div>
                  <div class="sim-presets" id="team-presets">
                    <button 
                      v-for="preset in (teamSimulator.presets || [])" 
                      :key="preset.id"
                      type="button" 
                      class="sim-chip"
                      :class="{ 'is-active': activeTeamPreset === preset.id }"
                      @click="applyTeamPreset(sprintAtual, preset.id)"
                    >
                      {{ preset.label }}
                    </button>
                  </div>
                  <table class="sim-table">
                    <thead>
                      <tr>
                        <th>Indicador</th>
                        <th>Peso</th>
                        <th>Atingimento</th>
                        <th>Pontos</th>
                        <th>Resultado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="indicator in (teamSimulator.indicators || [])" :key="indicator.id">
                        <td class="sim-indicator">
                          <strong>{{ indicator.label }}</strong>
                          <div v-if="indicator.hint" class="muted" style="font-size: 12px;">{{ indicator.hint }}</div>
                        </td>
                        <td class="sim-weight">{{ formatOne(indicator.weight) }}%</td>
                        <td>
                          <div class="sim-slider">
                            <input 
                              type="range" 
                              :min="0" 
                              :max="160" 
                              :value="currentTeamValues[indicator.id] || indicator.default || 100"
                              @input="updateTeamValues(sprintAtual, indicator.id, Number(($event.target as HTMLInputElement).value))"
                            />
                            <span class="sim-slider-value">{{ formatPercent(currentTeamValues[indicator.id] || indicator.default || 100) }}</span>
                          </div>
                        </td>
                        <td class="sim-points">
                          {{ teamResult ? formatPoints(teamResult.rows.find((r: any) => r.id === indicator.id)?.points || 0, { withUnit: true }) : '—' }}
                        </td>
                        <td>
                          <span 
                            v-if="teamResult"
                            class="status-pill"
                            :class="teamResult.rows.find((r: any) => r.id === indicator.id)?.statusClass || 'status-pill--ok'"
                          >
                            {{ teamResult.rows.find((r: any) => r.id === indicator.id)?.statusText || 'Elegível' }}
                          </span>
                          <span v-else class="status-pill status-pill--ok">Elegível</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="sim-summary">
                    <div class="sim-total">
                      <span>Pontuação total</span>
                      <strong>{{ teamResult ? formatPoints(teamResult.totalPoints, { withUnit: true }) : '—' }}</strong>
                    </div>
                    <div class="sim-progress">
                      <div class="sim-progress__track">
                        <div 
                          class="sim-progress__fill" 
                          :style="{ '--target': teamResult ? `${Math.round(teamResult.progressPct * 100)}%` : '0%' }"
                        ></div>
                      </div>
                      <small>{{ teamResult ? teamResult.progressLabel : '0% do mínimo necessário' }}</small>
                    </div>
                    <div class="sim-outcome">
                      <span 
                        v-if="teamResult"
                        class="status-tag"
                        :class="teamResult.finalClass"
                      >
                        {{ teamResult.finalStatus }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Simulador Individual -->
                <div v-if="individualSimulator" id="sim-individual" class="sim-card">
                  <div class="sim-card__head">
                    <div class="sim-card__title">
                      <h5>Simulador individual</h5>
                      <button 
                        type="button" 
                        class="sim-help" 
                        aria-label="Como funciona o simulador individual"
                        title="Escolha um perfil, use os presets ou ajuste manualmente. Para elegibilidade é necessário atingir 90% em cada indicador e acumular 100 pontos ou mais."
                      >
                        <i class="ti ti-info-circle"></i>
                      </button>
                    </div>
                    <p>Simule o desempenho de um gerente considerando os mesmos pesos da campanha.</p>
                    <p class="sim-hint">Elegível com todos os indicadores ≥ 90% e pelo menos 100 pontos.</p>
                  </div>
                  <div class="segmented seg-mini" role="tablist" id="individual-profiles">
                    <button 
                      v-for="profile in (individualSimulator.profiles || [])" 
                      :key="profile.id"
                      type="button" 
                      class="seg-btn"
                      :class="{ 'is-active': individualProfileSelected === profile.id }"
                      @click="selectIndividualProfile(sprintAtual, profile.id)"
                    >
                      {{ profile.label }}
                    </button>
                  </div>
                  <div class="sim-presets" id="individual-presets">
                    <button 
                      v-for="preset in (currentIndividualProfile?.presets || [])" 
                      :key="preset.id"
                      type="button" 
                      class="sim-chip"
                      :class="{ 'is-active': activeIndividualPreset === preset.id }"
                      @click="applyIndividualPreset(sprintAtual, currentIndividualProfile, preset.id)"
                    >
                      {{ preset.label }}
                    </button>
                  </div>
                  <p v-if="currentIndividualProfile" id="individual-description" class="sim-hint">
                    {{ currentIndividualProfile.description || '' }}
                  </p>
                  <table class="sim-table">
                    <thead>
                      <tr>
                        <th>Indicador</th>
                        <th>Peso</th>
                        <th>Atingimento</th>
                        <th>Pontos</th>
                        <th>Resultado</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="indicator in (currentIndividualProfile?.indicators || [])" :key="indicator.id">
                        <td class="sim-indicator">
                          <strong>{{ indicator.label }}</strong>
                        </td>
                        <td class="sim-weight">{{ formatOne(indicator.weight) }}%</td>
                        <td>
                          <div class="sim-slider">
                            <input 
                              type="range" 
                              :min="0" 
                              :max="160" 
                              :value="currentIndividualValues[indicator.id] || indicator.default || 100"
                              @input="updateIndividualValues(sprintAtual, currentIndividualProfile, indicator.id, Number(($event.target as HTMLInputElement).value))"
                            />
                            <span class="sim-slider-value">{{ formatPercent(currentIndividualValues[indicator.id] || indicator.default || 100) }}</span>
                          </div>
                        </td>
                        <td class="sim-points">
                          {{ individualResult ? formatPoints(individualResult.rows.find((r: any) => r.id === indicator.id)?.points || 0, { withUnit: true }) : '—' }}
                        </td>
                        <td>
                          <span 
                            v-if="individualResult"
                            class="status-pill"
                            :class="individualResult.rows.find((r: any) => r.id === indicator.id)?.statusClass || 'status-pill--ok'"
                          >
                            {{ individualResult.rows.find((r: any) => r.id === indicator.id)?.statusText || 'Elegível' }}
                          </span>
                          <span v-else class="status-pill status-pill--ok">Elegível</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="sim-summary">
                    <div class="sim-total">
                      <span>Pontuação total</span>
                      <strong>{{ individualResult ? formatPoints(individualResult.totalPoints, { withUnit: true }) : '—' }}</strong>
                    </div>
                    <div class="sim-progress">
                      <div class="sim-progress__track">
                        <div 
                          class="sim-progress__fill" 
                          :style="{ '--target': individualResult ? `${Math.round(individualResult.progressPct * 100)}%` : '0%' }"
                        ></div>
                      </div>
                      <small>{{ individualResult ? individualResult.progressLabel : '0% do mínimo necessário' }}</small>
                    </div>
                    <div class="sim-outcome">
                      <span 
                        v-if="individualResult"
                        class="status-tag"
                        :class="individualResult.finalClass"
                      >
                        {{ individualResult.finalStatus }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ranking da Campanha -->
            <div v-if="sprintAtual && rankingData.length > 0" class="card card--camp-ranking">
              <div class="card__header">
                <div class="title-subtitle">
                  <h3>Ranking da Campanha</h3>
                  <p id="camp-ranking-desc" class="muted">
                    Acompanhe a performance das Unidades e a elegibilidade frente aos critérios mínimos.
                  </p>
                </div>
              </div>
              <div id="camp-ranking" class="camp-ranking-wrapper">
                <table class="camp-ranking-table">
                  <thead>
                    <tr>
                      <th class="pos-col">#</th>
                      <th class="regional-col">Unidade</th>
                      <th>Linhas governamentais</th>
                      <th>Cash (TPV)</th>
                      <th>Abertura de contas</th>
                      <th>Pontuação final</th>
                      <th>Atividade comercial</th>
                      <th>Elegibilidade</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, index) in rankingData" :key="index">
                      <td class="pos-col">{{ row.rank || (index + 1) }}</td>
                      <td class="regional-col">{{ row.unidade || row.label || '—' }}</td>
                      <td>
                        <div class="indicator-bar">
                          <div class="indicator-bar__track">
                            <span style="--target: 0%"></span>
                          </div>
                          <div class="indicator-bar__value">—</div>
                        </div>
                      </td>
                      <td>
                        <div class="indicator-bar">
                          <div class="indicator-bar__track">
                            <span style="--target: 0%"></span>
                          </div>
                          <div class="indicator-bar__value">—</div>
                        </div>
                      </td>
                      <td>
                        <div class="indicator-bar">
                          <div class="indicator-bar__track">
                            <span style="--target: 0%"></span>
                          </div>
                          <div class="indicator-bar__value">—</div>
                        </div>
                      </td>
                      <td class="sim-points">{{ formatPoints(row.totalPoints || 0, { withUnit: true }) }}</td>
                      <td>—</td>
                      <td>
                        <span class="elegibility-badge elegibility-badge--ok">Elegível</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.campanhas-wrapper {
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

.campanhas-view {
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

.campanhas-content {
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

.card--campanhas {
  display: flex;
  flex-direction: column;
  gap: 18px;
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

.camp-header {
  align-items: flex-start;
  gap: 18px;
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

.camp-header__controls {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  min-width: 200px;
}

.camp-header__controls label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--muted);
  font-weight: 600;
}

.camp-header__controls select {
  min-width: 200px;
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

/* Hero Section */
.camp-hero {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  align-items: stretch;
  padding: 18px;
  border: 1px dashed rgba(179, 0, 0, 0.18);
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(179, 0, 0, 0.08), rgba(179, 0, 0, 0.02));
}

.camp-hero__info {
  flex: 1 1 240px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 0;
}

.camp-hero__info p {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  color: #1f2937;
  font-weight: 600;
}

.camp-validity {
  font-size: 13px;
  color: var(--muted);
  font-weight: 500;
}

.camp-period {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #111827;
}

.camp-period i {
  color: var(--brand);
  font-size: 16px;
}

.camp-period--validity {
  font-weight: 500;
  color: var(--muted);
}

.camp-period--validity .camp-validity {
  font-weight: 500;
}

.camp-hero__stats {
  flex: 1 1 200px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
}

.camp-hero__stat {
  background: #fff;
  border: 1px solid rgba(179, 0, 0, 0.12);
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  box-shadow: 0 10px 24px rgba(179, 0, 0, 0.08);
}

.camp-hero__stat span {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted);
  font-weight: 700;
}

.camp-hero__stat strong {
  font-size: 18px;
  color: #0f172a;
}

.camp-hero__stat small {
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}

/* KPIs */
.camp-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
  padding: 16px;
}

.camp-kpi {
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.camp-kpi span {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #6b7280;
  font-weight: 700;
}

.camp-kpi strong {
  font-size: 18px;
  color: #0f172a;
}

.camp-kpi small {
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}

/* Simuladores */
.card--camp-sims {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

.sim-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
}

.sim-card {
  background: #fdfdff;
  border: 2px solid #dbe2ff;
  border-radius: 18px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.sim-card__head {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sim-card__title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.sim-card__head h5 {
  margin: 0;
  font-size: 19px;
  color: #0f172a;
}

.sim-card__head p {
  margin: 0;
  color: #4b5563;
  font-size: 13px;
  line-height: 1.5;
}

.sim-hint {
  margin: 0;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.sim-help {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  border: 1px solid rgba(36, 107, 253, 0.25);
  background: rgba(36, 107, 253, 0.1);
  color: #246BFD;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.sim-help:hover,
.sim-help:focus-visible {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(36, 107, 253, 0.18);
  outline: none;
}

.sim-help i {
  font-size: 18px;
}

.sim-presets {
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
}

.sim-chip.is-active {
  border-color: #b30000;
  color: #b30000;
  background: rgba(179, 0, 0, 0.08);
}

.sim-table {
  width: 100%;
  border-collapse: collapse;
}

.sim-table th {
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: #6b7280;
  padding-bottom: 8px;
}

.sim-table td {
  padding: 10px 0;
  border-bottom: 1px solid #edf1f9;
  font-size: 13px;
  color: #111827;
  vertical-align: middle;
}

.sim-table tr:last-child td {
  border-bottom: none;
}

.sim-table td:last-child {
  text-align: right;
}

.sim-indicator {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sim-indicator strong {
  font-weight: 700;
  color: #0f172a;
}

.sim-weight {
  font-weight: 700;
  color: #0f172a;
}

.sim-points {
  font-weight: 700;
  color: #0f172a;
}

.sim-slider {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sim-slider input[type="range"] {
  flex: 1 1 auto;
  accent-color: #b30000;
}

.sim-slider-value {
  min-width: 54px;
  text-align: right;
  font-weight: 700;
  color: #111827;
  font-size: 14px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  background: #f3f4f6;
  color: #1f2937;
}

.status-pill--great {
  background: rgba(16, 185, 129, 0.15);
  color: #047857;
}

.status-pill--ok {
  background: rgba(59, 130, 246, 0.18);
  color: #1d4ed8;
}

.status-pill--warn {
  background: rgba(252, 211, 77, 0.25);
  color: #b45309;
}

.status-pill--alert {
  background: rgba(248, 113, 113, 0.2);
  color: #b91c1c;
}

.sim-summary {
  display: grid;
  grid-template-columns: minmax(0, 240px) minmax(0, 1fr) auto;
  align-items: start;
  gap: 18px;
  width: 100%;
}

.sim-summary > * {
  align-self: start;
}

.sim-total {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sim-total span {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.sim-total strong {
  font-size: 24px;
  color: #0f172a;
}

.sim-progress {
  flex: 1 1 200px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.sim-progress__track {
  width: 100%;
  height: 10px;
  border-radius: 999px;
  background: #e5e7eb;
  overflow: hidden;
  --bar-anim-duration: 0.6s;
}

.sim-progress__fill {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #b30000, #f97316);
  width: var(--target, 0%);
  transform-origin: left center;
  transform: scaleX(1);
  will-change: transform;
}

.sim-progress__track.is-animating .sim-progress__fill {
  animation: bar-fill-grow var(--bar-anim-duration, 0.6s) cubic-bezier(0.25, 0.9, 0.3, 1.2) forwards;
}

.sim-progress small {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
}

.sim-outcome {
  display: flex;
  align-items: center;
}

.status-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.status-tag--great {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.16), rgba(16, 185, 129, 0.05));
  color: #047857;
}

.status-tag--ok {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0.05));
  color: #1d4ed8;
}

.status-tag--warn {
  background: linear-gradient(135deg, rgba(252, 211, 77, 0.25), rgba(252, 211, 77, 0.08));
  color: #b45309;
}

.status-tag--alert {
  background: linear-gradient(135deg, rgba(248, 113, 113, 0.3), rgba(248, 113, 113, 0.08));
  color: #b91c1c;
}

/* Ranking */
.card--camp-ranking {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
}

#camp-ranking {
  overflow: auto;
}

.camp-ranking-wrapper {
  overflow-x: auto;
}

.camp-ranking-table {
  width: 100%;
  min-width: 760px;
  border-collapse: collapse;
}

.camp-ranking-table thead th {
  padding: 10px 12px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.camp-ranking-table tbody td {
  padding: 12px;
  border-bottom: 1px solid #edf1f9;
  font-size: 13px;
  color: #0f172a;
}

.camp-ranking-table tbody tr:hover {
  background: #f8fafc;
}

.camp-ranking-table .pos-col {
  width: 56px;
  font-weight: 800;
  text-align: center;
}

.camp-ranking-table .regional-col {
  font-weight: 700;
}

.indicator-bar {
  display: flex;
  align-items: center;
  gap: 10px;
}

.indicator-bar__track {
  position: relative;
  flex: 1;
  height: 6px;
  border-radius: 999px;
  background: #e5e7eb;
  overflow: hidden;
  --bar-anim-duration: 0.55s;
}

.indicator-bar__track span {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: var(--target, 0%);
  border-radius: inherit;
  background: linear-gradient(90deg, #2563eb, #38bdf8);
  transform-origin: left center;
  transform: scaleX(1);
}

.indicator-bar__track.is-animating span {
  animation: bar-fill-grow var(--bar-anim-duration, 0.55s) cubic-bezier(0.25, 0.9, 0.3, 1.2) forwards;
}

.indicator-bar__value {
  min-width: 58px;
  text-align: right;
  font-weight: 700;
}

.indicator-bar__points {
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}

.elegibility-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 12px;
}

.elegibility-badge--great {
  background: rgba(16, 185, 129, 0.16);
  color: #047857;
}

.elegibility-badge--ok {
  background: rgba(59, 130, 246, 0.18);
  color: #1d4ed8;
}

.elegibility-badge--warn {
  background: rgba(248, 113, 113, 0.2);
  color: #b91c1c;
}

.segmented {
  display: inline-flex;
  gap: 4px;
  padding: 4px;
  background: #f3f4f6;
  border-radius: 10px;
}

.seg-mini {
  padding: 2px;
}

.seg-btn {
  appearance: none;
  border: none;
  background: transparent;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s ease;
}

.seg-btn:hover {
  background: rgba(179, 0, 0, 0.08);
  color: var(--brand);
}

.seg-btn.is-active {
  background: var(--brand);
  color: #fff;
}

@keyframes bar-fill-grow {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}

@media (max-width: 900px) {
  .sim-grid {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }
}

@media (max-width: 860px) {
  .camp-hero {
    padding: 16px;
  }

  .camp-hero__stats {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  }
}

@media (max-width: 640px) {
  .camp-header__controls {
    align-items: flex-start;
    width: 100%;
  }

  .camp-header__controls select {
    width: 100%;
  }

  .sim-grid {
    grid-template-columns: 1fr;
  }

  .camp-ranking-table {
    min-width: 640px;
  }

  .sim-card {
    padding: 18px;
  }

  .sim-progress {
    flex-basis: 100%;
  }
}

@media (max-width: 540px) {
  .sim-grid {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 600px) {
  .sim-card {
    padding: 16px;
    gap: 14px;
  }

  .sim-card__head h5 {
    font-size: 16px;
  }

  .sim-card__head p {
    font-size: 13px;
  }

  .sim-hint {
    font-size: 11.5px;
  }

  .sim-table {
    border-collapse: separate;
    border-spacing: 0;
  }

  .sim-table thead {
    display: none;
  }

  .sim-table tbody {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .sim-table tbody tr {
    display: flex;
    flex-direction: column;
    gap: 10px;
    border: 1px solid #eef2ff;
    border-radius: 16px;
    padding: 12px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
  }

  .sim-table tbody td {
    padding: 0;
    border-bottom: none;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .sim-table tbody td + td {
    margin-top: 6px;
  }

  .sim-table tbody td::before {
    content: attr(data-label);
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .sim-summary {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .sim-total strong {
    font-size: 22px;
  }

  .sim-progress {
    width: 100%;
    gap: 4px;
  }

  .sim-progress__track {
    display: none;
  }
}
</style>

