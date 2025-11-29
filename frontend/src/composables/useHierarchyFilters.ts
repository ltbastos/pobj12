/**
 * Composable para gerenciar a hierarquia de filtros
 * Implementa a lógica de dependência entre os campos hierárquicos
 */

import { ref, computed, watch, type Ref } from 'vue'
import type { EstruturaData } from '../services/estruturaService'
import type { FilterOption, HierarchySelection } from '../types'

const normalizeId = (val: any): string => {
  if (val == null || val === '') return ''
  return String(val).trim()
}

/**
 * Formata label para campos que usam id - nome
 */
const formatIdNomeLabel = (id: string, nome: string): string => {
  if (!id && !nome) return ''
  if (!id) return nome
  if (!nome || nome === id) return id
  return `${id} - ${nome}`
}

/**
 * Formata label para campos que usam funcional - nome
 */
const formatFuncionalNomeLabel = (funcional: string, nome: string): string => {
  if (!funcional && !nome) return ''
  if (!funcional) return nome
  if (!nome || nome === funcional) return funcional
  return `${funcional} - ${nome}`
}

const normalizeOption = (item: any, fieldType?: 'segmento' | 'diretoria' | 'regional' | 'agencia' | 'ggestao' | 'gerente'): FilterOption => {
  // Para gerente de gestão e gerente, o ID deve ser o funcional para exibição
  // Mas preservamos o id original para comparações hierárquicas
  let id: string
  let funcional: string | undefined
  let idOriginal: string | undefined // ID numérico original para comparações

  if (fieldType === 'ggestao' || fieldType === 'gerente') {
    // Para gerente de gestão e gerente: usa funcional como ID de exibição
    funcional = normalizeId(item.funcional || '')
    idOriginal = normalizeId(item.id || '') // Preserva o ID numérico original
    id = funcional || idOriginal // ID usado no filtro é o funcional
  } else {
    // Para segmento, diretoria, regional e agência: usa id normal
    id = normalizeId(
      item.id || item.codigo || item.id_diretoria || item.id_regional ||
      item.id_agencia || ''
    )
    funcional = item.funcional ? normalizeId(item.funcional) : undefined
    idOriginal = id
  }

  // Extrai nome do item
  const nomeRaw = item.nome || item.label || ''
  const nome = String(nomeRaw).trim()

  // Formata label baseado no tipo de campo
  let label = nome || id
  if (fieldType === 'ggestao' || fieldType === 'gerente') {
    // Para gerente de gestão e gerente: funcional - nome
    label = formatFuncionalNomeLabel(funcional || id, nome)
  } else if (fieldType === 'segmento' || fieldType === 'diretoria' || fieldType === 'regional' || fieldType === 'agencia') {
    // Para segmento, diretoria, regional e agência: id - nome
    label = formatIdNomeLabel(id, nome)
  } else {
    // Fallback: usa o label original ou id - nome
    label = item.label || formatIdNomeLabel(id, nome)
  }

  // Preserva campos de relacionamento hierárquico (suporta snake_case e camelCase)
  const idSegmento = item.id_segmento ?? item.idSegmento
  const idDiretoria = item.id_diretoria ?? item.idDiretoria
  const idRegional = item.id_regional ?? item.idRegional ?? item.gerencia_id ?? item.gerenciaId
  const idAgencia = item.id_agencia ?? item.idAgencia
  const idGestor = item.id_gestor ?? item.idGestor ?? item.gerente_gestao_id ?? item.gerenteGestaoId

  return {
    id,
    label,
    nome: nome || label,
    id_segmento: idSegmento ? normalizeId(idSegmento) : undefined,
    id_diretoria: idDiretoria ? normalizeId(idDiretoria) : undefined,
    id_regional: idRegional ? normalizeId(idRegional) : undefined,
    id_agencia: idAgencia ? normalizeId(idAgencia) : undefined,
    id_gestor: idGestor ? normalizeId(idGestor) : undefined,
    funcional,
    // Preserva o ID original numérico para comparações hierárquicas
    id_original: idOriginal
  }
}

export function useHierarchyFilters(estruturaData: Ref<EstruturaData | null>) {
  // Seleções atuais
  const segmento = ref('')
  const diretoria = ref('')
  const gerencia = ref('')
  const agencia = ref('')
  const ggestao = ref('')
  const gerente = ref('')

  // Opções base (todas as opções disponíveis)
  const allSegmentos = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.segmentos.map(item => normalizeOption(item, 'segmento')).filter(opt => opt.id)
  })

  // Opções base (todas as opções disponíveis)
  const allDiretorias = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.diretorias.map(item => normalizeOption(item, 'diretoria')).filter(opt => opt.id)
  })

  const allRegionais = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.regionais.map(item => normalizeOption(item, 'regional')).filter(opt => opt.id)
  })

  const allAgencias = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.agencias.map(item => normalizeOption(item, 'agencia')).filter(opt => opt.id)
  })

  const allGerentesGestao = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.gerentes_gestao.map(item => normalizeOption(item, 'ggestao')).filter(opt => opt.id)
  })

  const allGerentes = computed(() => {
    if (!estruturaData.value) return []
    return estruturaData.value.gerentes.map(item => normalizeOption(item, 'gerente')).filter(opt => opt.id)
  })

  // Opções filtradas baseadas na hierarquia
  // Agora mostra todas as opções quando não há filtro pai, mas ainda filtra quando há
  const segmentos = computed(() => allSegmentos.value)

  const diretorias = computed(() => {
    // Se não há segmento selecionado, mostra todas
    if (!segmento.value) return allDiretorias.value
    // Se há segmento, filtra por ele
    return allDiretorias.value.filter(opt =>
      opt.id_segmento && normalizeId(opt.id_segmento) === normalizeId(segmento.value)
    )
  })

  const regionais = computed(() => {
    // Se não há diretoria selecionada, mostra todas
    if (!diretoria.value) return allRegionais.value
    // Se há diretoria, filtra por ela
    return allRegionais.value.filter(opt =>
      opt.id_diretoria && normalizeId(opt.id_diretoria) === normalizeId(diretoria.value)
    )
  })

  const agencias = computed(() => {
    // Se não há gerencia selecionada, mostra todas
    if (!gerencia.value) return allAgencias.value
    // Se há gerencia, filtra por ela
    return allAgencias.value.filter(opt =>
      opt.id_regional && normalizeId(opt.id_regional) === normalizeId(gerencia.value)
    )
  })

  const gerentesGestao = computed(() => {
    let filtered = allGerentesGestao.value

    // Filtra por agência se selecionada
    if (agencia.value) {
      filtered = filtered.filter(opt =>
        opt.id_agencia && normalizeId(opt.id_agencia) === normalizeId(agencia.value)
      )
    }

    // Se um gerente está selecionado, mostra apenas o gerente de gestão que é gestor daquele gerente
    // (ou seja, gerentes_gestao cujo id_original corresponde ao id_gestor do gerente selecionado)
    if (gerente.value) {
      const gerenteMeta = findItemMeta(gerente.value, allGerentes.value)
      if (gerenteMeta?.id_gestor) {
        const gestorId = normalizeId(gerenteMeta.id_gestor)
        // O id_gestor do gerente é o ID numérico do gerente de gestão
        // Compara com o id_original (numérico) do gerente de gestão
        filtered = filtered.filter(opt => {
          const optIdOriginal = opt.id_original || opt.id
          const optIdOriginalNormalized = normalizeId(optIdOriginal)
          return optIdOriginalNormalized === gestorId
        })
      } else {
        // Se o gerente não tem id_gestor, não mostra nenhum gerente de gestão
        filtered = []
      }
    }

    return filtered
  })

  const gerentes = computed(() => {
    let filtered = allGerentes.value

    // Se há gerente de gestão selecionado, filtra os gerentes que têm aquele gerente de gestão como gestor
    if (ggestao.value) {
      // Busca o gerente de gestão selecionado para obter seu id_original (numérico)
      const ggestaoMeta = findItemMeta(ggestao.value, allGerentesGestao.value)
      if (!ggestaoMeta) return filtered

      // O id_gestor do gerente é o ID numérico do gerente de gestão
      // Compara com o id_original (numérico) do gerente de gestão
      const ggestaoIdOriginal = ggestaoMeta.id_original || ggestaoMeta.id
      const ggestaoIdOriginalNormalized = normalizeId(ggestaoIdOriginal)

      filtered = filtered.filter(opt => {
        if (!opt.id_gestor) return false
        const gestorId = normalizeId(opt.id_gestor)
        // Compara o id_gestor (numérico) do gerente com o id_original (numérico) do gerente de gestão
        return gestorId === ggestaoIdOriginalNormalized
      })
    }

    return filtered
  })

  // Função para encontrar metadados de um item
  const findItemMeta = (id: string, items: FilterOption[]): FilterOption | null => {
    return items.find(item => normalizeId(item.id) === normalizeId(id)) || null
  }

  // Ajusta a seleção quando um campo é alterado
  const adjustSelection = (changedField: keyof HierarchySelection, value: string) => {
    const normalizedValue = normalizeId(value)

    // Limpa campos filhos quando um campo pai é alterado
    if (changedField === 'segmento') {
      diretoria.value = ''
      gerencia.value = ''
      agencia.value = ''
      ggestao.value = ''
      gerente.value = ''
    } else if (changedField === 'diretoria') {
      gerencia.value = ''
      agencia.value = ''
      ggestao.value = ''
      gerente.value = ''
      // Tenta preencher campos pais
      if (normalizedValue) {
        const diretoriaMeta = findItemMeta(normalizedValue, allDiretorias.value)
        if (diretoriaMeta?.id_segmento) {
          segmento.value = normalizeId(diretoriaMeta.id_segmento)
        }
      }
    } else if (changedField === 'gerencia') {
      agencia.value = ''
      ggestao.value = ''
      gerente.value = ''
      // Tenta preencher campos pais
      if (normalizedValue) {
        const gerenciaMeta = findItemMeta(normalizedValue, allRegionais.value)
        if (gerenciaMeta?.id_diretoria) {
          diretoria.value = normalizeId(gerenciaMeta.id_diretoria)
          const diretoriaMeta = findItemMeta(diretoria.value, allDiretorias.value)
          if (diretoriaMeta?.id_segmento) {
            segmento.value = normalizeId(diretoriaMeta.id_segmento)
          }
        }
      }
    } else if (changedField === 'agencia') {
      ggestao.value = ''
      gerente.value = ''
      // Tenta preencher campos pais
      if (normalizedValue) {
        const agenciaMeta = findItemMeta(normalizedValue, allAgencias.value)
        if (agenciaMeta) {
          if (agenciaMeta.id_regional) {
            gerencia.value = normalizeId(agenciaMeta.id_regional)
            const gerenciaMeta = findItemMeta(gerencia.value, allRegionais.value)
            if (gerenciaMeta?.id_diretoria) {
              diretoria.value = normalizeId(gerenciaMeta.id_diretoria)
              const diretoriaMeta = findItemMeta(diretoria.value, allDiretorias.value)
              if (diretoriaMeta?.id_segmento) {
                segmento.value = normalizeId(diretoriaMeta.id_segmento)
              }
            }
          }
        }
      }
    } else if (changedField === 'ggestao') {
      gerente.value = ''
      // Tenta preencher campos pais
      if (normalizedValue) {
        const ggestaoMeta = findItemMeta(normalizedValue, allGerentesGestao.value)
        if (ggestaoMeta?.id_agencia) {
          agencia.value = normalizeId(ggestaoMeta.id_agencia)
          const agenciaMeta = findItemMeta(agencia.value, allAgencias.value)
          if (agenciaMeta) {
            if (agenciaMeta.id_regional) {
              gerencia.value = normalizeId(agenciaMeta.id_regional)
              const gerenciaMeta = findItemMeta(gerencia.value, allRegionais.value)
              if (gerenciaMeta?.id_diretoria) {
                diretoria.value = normalizeId(gerenciaMeta.id_diretoria)
                const diretoriaMeta = findItemMeta(diretoria.value, allDiretorias.value)
                if (diretoriaMeta?.id_segmento) {
                  segmento.value = normalizeId(diretoriaMeta.id_segmento)
                }
              }
            }
          }
        }
      }
    } else if (changedField === 'gerente') {
      // Quando seleciona um gerente, tenta preencher o gerente de gestão correspondente
      // mas não limpa o gerente de gestão se já estiver selecionado
      if (normalizedValue) {
        const gerenteMeta = findItemMeta(normalizedValue, allGerentes.value)
        if (gerenteMeta?.id_gestor) {
          const gestorId = normalizeId(gerenteMeta.id_gestor)
          // O id_gestor do gerente é o ID numérico do gerente de gestão
          // Compara com o id_original (numérico) do gerente de gestão
          const ggestaoMatch = allGerentesGestao.value.find(gg => {
            const ggIdOriginal = gg.id_original || gg.id
            const ggIdOriginalNormalized = normalizeId(ggIdOriginal)
            return ggIdOriginalNormalized === gestorId
          })
          if (ggestaoMatch) {
            // Usa o id (funcional) para seleção, mas encontrou pelo id_original
            ggestao.value = normalizeId(ggestaoMatch.id)
          }

          // Preenche campos pais se o gerente de gestão foi encontrado
          if (ggestao.value) {
            const ggestaoMeta = findItemMeta(ggestao.value, allGerentesGestao.value)
            if (ggestaoMeta?.id_agencia) {
              agencia.value = normalizeId(ggestaoMeta.id_agencia)
              const agenciaMeta = findItemMeta(agencia.value, allAgencias.value)
              if (agenciaMeta) {
                if (agenciaMeta.id_regional) {
                  gerencia.value = normalizeId(agenciaMeta.id_regional)
                  const gerenciaMeta = findItemMeta(gerencia.value, allRegionais.value)
                  if (gerenciaMeta?.id_diretoria) {
                    diretoria.value = normalizeId(gerenciaMeta.id_diretoria)
                    const diretoriaMeta = findItemMeta(diretoria.value, allDiretorias.value)
                    if (diretoriaMeta?.id_segmento) {
                      segmento.value = normalizeId(diretoriaMeta.id_segmento)
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  // Handlers para mudanças nos selects
  const handleSegmentoChange = (value: string) => {
    segmento.value = value
    adjustSelection('segmento', value)
  }

  const handleDiretoriaChange = (value: string) => {
    diretoria.value = value
    adjustSelection('diretoria', value)
  }

  const handleGerenciaChange = (value: string) => {
    gerencia.value = value
    adjustSelection('gerencia', value)
  }

  const handleAgenciaChange = (value: string) => {
    agencia.value = value
    adjustSelection('agencia', value)
  }

  const handleGerenteGestaoChange = (value: string) => {
    ggestao.value = value
    adjustSelection('ggestao', value)
  }

  const handleGerenteChange = (value: string) => {
    gerente.value = value
    adjustSelection('gerente', value)
  }

  // Limpar todos os filtros
  const clearAll = () => {
    segmento.value = ''
    diretoria.value = ''
    gerencia.value = ''
    agencia.value = ''
    ggestao.value = ''
    gerente.value = ''
  }

  return {
    // Valores
    segmento,
    diretoria,
    gerencia,
    agencia,
    ggestao,
    gerente,
    // Opções filtradas
    segmentos,
    diretorias,
    regionais,
    agencias,
    gerentesGestao,
    gerentes,
    // Handlers
    handleSegmentoChange,
    handleDiretoriaChange,
    handleGerenciaChange,
    handleAgenciaChange,
    handleGerenteGestaoChange,
    handleGerenteChange,
    clearAll
  }
}

