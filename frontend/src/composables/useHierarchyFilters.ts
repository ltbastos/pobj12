/**
 * Composable para gerenciar a hierarquia de filtros
 * Implementa a lógica de dependência entre os campos hierárquicos
 */

import { ref, computed, watch, type Ref } from 'vue'
import type { InitData } from '../services/initService'
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

  // Preserva campos de relacionamento hierárquico do JSON
  // O JSON usa: segmento_id, diretoria_id, regional_id, agencia_id, id_gestor
  const idSegmento = item.segmento_id ?? item.id_segmento ?? item.idSegmento
  const idDiretoria = item.diretoria_id ?? item.id_diretoria ?? item.idDiretoria
  const idRegional = item.regional_id ?? item.id_regional ?? item.idRegional ?? item.gerencia_id ?? item.gerenciaId
  const idAgencia = item.agencia_id ?? item.id_agencia ?? item.idAgencia
  const idGestor = item.id_gestor ?? item.idGestor ?? item.gerente_gestao_id ?? item.gerenteGestaoId

  return {
    id,
    nome: label, // Usa o label formatado (com ID/funcional) para exibição
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

export function useHierarchyFilters(estruturaData: Ref<InitData | null>) {
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
    // Compara usando o ID numérico do segmento
    const segmentoMeta = findItemMeta(segmento.value, allSegmentos.value)
    if (!segmentoMeta) return []
    const segmentoIdNumero = normalizeId(segmentoMeta.id_segmento || segmentoMeta.id)
    return allDiretorias.value.filter(opt => {
      const optSegmentoId = normalizeId(opt.id_segmento || '')
      return optSegmentoId === segmentoIdNumero
    })
  })

  const regionais = computed(() => {
    // Se não há diretoria selecionada, mostra todas
    if (!diretoria.value) return allRegionais.value
    // Se há diretoria, filtra por ela
    // Compara usando o ID numérico da diretoria
    const diretoriaMeta = findItemMeta(diretoria.value, allDiretorias.value)
    if (!diretoriaMeta) return []
    const diretoriaIdNumero = normalizeId(diretoriaMeta.id_diretoria || diretoriaMeta.id)
    return allRegionais.value.filter(opt => {
      const optDiretoriaId = normalizeId(opt.id_diretoria || '')
      return optDiretoriaId === diretoriaIdNumero
    })
  })

  const agencias = computed(() => {
    // Se não há gerencia selecionada, mostra todas
    if (!gerencia.value) return allAgencias.value
    // Se há gerencia, filtra por ela
    // Compara usando o ID numérico da regional
    const gerenciaMeta = findItemMeta(gerencia.value, allRegionais.value)
    if (!gerenciaMeta) return []
    const gerenciaIdNumero = normalizeId(gerenciaMeta.id_regional || gerenciaMeta.id)
    return allAgencias.value.filter(opt => {
      const optRegionalId = normalizeId(opt.id_regional || '')
      return optRegionalId === gerenciaIdNumero
    })
  })

  const gerentesGestao = computed(() => {
    let filtered = allGerentesGestao.value

    // Filtra por agência se selecionada
    // Compara usando o ID numérico da agência (id_original) com o id_agencia do gerente de gestão
    if (agencia.value) {
      const agenciaMeta = findItemMeta(agencia.value, allAgencias.value)
      if (agenciaMeta) {
        const agenciaIdNumero = normalizeId(agenciaMeta.id_original || agenciaMeta.id)
        filtered = filtered.filter(opt => {
          // opt.id_agencia é o ID numérico da agência do gerente de gestão
          const optAgenciaId = normalizeId(opt.id_agencia || '')
          return optAgenciaId === agenciaIdNumero
        })
      } else {
        filtered = []
      }
    }

    // Se um gerente está selecionado, mostra apenas o gerente de gestão que é gestor daquele gerente
    // O id_gestor do gerente é o ID numérico do gerente de gestão
    if (gerente.value) {
      const gerenteMeta = findItemMeta(gerente.value, allGerentes.value)
      if (gerenteMeta?.id_gestor) {
        const gestorId = normalizeId(gerenteMeta.id_gestor)
        // Compara com o id_original (numérico) do gerente de gestão
        filtered = filtered.filter(opt => {
          const optIdOriginal = normalizeId(opt.id_original || '')
          return optIdOriginal === gestorId
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
      const ggestaoIdOriginal = normalizeId(ggestaoMeta.id_original || '')

      filtered = filtered.filter(opt => {
        if (!opt.id_gestor) return false
        const gestorId = normalizeId(opt.id_gestor)
        // Compara o id_gestor (numérico) do gerente com o id_original (numérico) do gerente de gestão
        return gestorId === ggestaoIdOriginal
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
          // Encontra o segmento pelo ID numérico
          const segmentoMatch = allSegmentos.value.find(s => 
            normalizeId(s.id_original || s.id) === normalizeId(diretoriaMeta.id_segmento)
          )
          if (segmentoMatch) {
            segmento.value = normalizeId(segmentoMatch.id)
          }
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
          // Encontra a diretoria pelo ID numérico
          const diretoriaMatch = allDiretorias.value.find(d => 
            normalizeId(d.id_original || d.id) === normalizeId(gerenciaMeta.id_diretoria)
          )
          if (diretoriaMatch) {
            diretoria.value = normalizeId(diretoriaMatch.id)
            // Encontra o segmento pelo ID numérico
            if (diretoriaMatch.id_segmento) {
              const segmentoMatch = allSegmentos.value.find(s => 
                normalizeId(s.id_original || s.id) === normalizeId(diretoriaMatch.id_segmento)
              )
              if (segmentoMatch) {
                segmento.value = normalizeId(segmentoMatch.id)
              }
            }
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
            // Encontra a regional pelo ID numérico
            const gerenciaMatch = allRegionais.value.find(r => 
              normalizeId(r.id_original || r.id) === normalizeId(agenciaMeta.id_regional)
            )
            if (gerenciaMatch) {
              gerencia.value = normalizeId(gerenciaMatch.id)
              // Encontra a diretoria pelo ID numérico
              if (gerenciaMatch.id_diretoria) {
                const diretoriaMatch = allDiretorias.value.find(d => 
                  normalizeId(d.id_original || d.id) === normalizeId(gerenciaMatch.id_diretoria)
                )
                if (diretoriaMatch) {
                  diretoria.value = normalizeId(diretoriaMatch.id)
                  // Encontra o segmento pelo ID numérico
                  if (diretoriaMatch.id_segmento) {
                    const segmentoMatch = allSegmentos.value.find(s => 
                      normalizeId(s.id_original || s.id) === normalizeId(diretoriaMatch.id_segmento)
                    )
                    if (segmentoMatch) {
                      segmento.value = normalizeId(segmentoMatch.id)
                    }
                  }
                }
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
          // Encontra a agência pelo ID numérico
          const agenciaMatch = allAgencias.value.find(a => 
            normalizeId(a.id_original || a.id) === normalizeId(ggestaoMeta.id_agencia)
          )
          if (agenciaMatch) {
            agencia.value = normalizeId(agenciaMatch.id)
            // Encontra a regional pelo ID numérico
            if (agenciaMatch.id_regional) {
              const gerenciaMatch = allRegionais.value.find(r => 
                normalizeId(r.id_original || r.id) === normalizeId(agenciaMatch.id_regional)
              )
              if (gerenciaMatch) {
                gerencia.value = normalizeId(gerenciaMatch.id)
                // Encontra a diretoria pelo ID numérico
                if (gerenciaMatch.id_diretoria) {
                  const diretoriaMatch = allDiretorias.value.find(d => 
                    normalizeId(d.id_original || d.id) === normalizeId(gerenciaMatch.id_diretoria)
                  )
                  if (diretoriaMatch) {
                    diretoria.value = normalizeId(diretoriaMatch.id)
                    // Encontra o segmento pelo ID numérico
                    if (diretoriaMatch.id_segmento) {
                      const segmentoMatch = allSegmentos.value.find(s => 
                        normalizeId(s.id_original || s.id) === normalizeId(diretoriaMatch.id_segmento)
                      )
                      if (segmentoMatch) {
                        segmento.value = normalizeId(segmentoMatch.id)
                      }
                    }
                  }
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
              // Encontra a agência pelo ID numérico
              const agenciaMatch = allAgencias.value.find(a => 
                normalizeId(a.id_original || a.id) === normalizeId(ggestaoMeta.id_agencia)
              )
              if (agenciaMatch) {
                agencia.value = normalizeId(agenciaMatch.id)
                // Encontra a regional pelo ID numérico
                if (agenciaMatch.id_regional) {
                  const gerenciaMatch = allRegionais.value.find(r => 
                    normalizeId(r.id_original || r.id) === normalizeId(agenciaMatch.id_regional)
                  )
                  if (gerenciaMatch) {
                    gerencia.value = normalizeId(gerenciaMatch.id)
                    // Encontra a diretoria pelo ID numérico
                    if (gerenciaMatch.id_diretoria) {
                      const diretoriaMatch = allDiretorias.value.find(d => 
                        normalizeId(d.id_original || d.id) === normalizeId(gerenciaMatch.id_diretoria)
                      )
                      if (diretoriaMatch) {
                        diretoria.value = normalizeId(diretoriaMatch.id)
                        // Encontra o segmento pelo ID numérico
                        if (diretoriaMatch.id_segmento) {
                          const segmentoMatch = allSegmentos.value.find(s => 
                            normalizeId(s.id_original || s.id) === normalizeId(diretoriaMatch.id_segmento)
                          )
                          if (segmentoMatch) {
                            segmento.value = normalizeId(segmentoMatch.id)
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

