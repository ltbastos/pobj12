<script setup lang="ts">
import { computed } from 'vue'
import { useGlobalFilters, type FilterState } from '../composables/useGlobalFilters'

const { filterState, clearFilter } = useGlobalFilters()

interface AppliedFilter {
  key: string
  label: string
  value: string
}

const appliedFilters = computed<AppliedFilter[]>(() => {
  const filters: AppliedFilter[] = []
  
  if (filterState.value.segmento && filterState.value.segmento !== 'Todos') {
    filters.push({
      key: 'segmento',
      label: 'Segmento',
      value: filterState.value.segmento
    })
  }
  
  if (filterState.value.diretoria && filterState.value.diretoria !== 'Todas') {
    filters.push({
      key: 'diretoria',
      label: 'Diretoria',
      value: filterState.value.diretoria
    })
  }
  
  if (filterState.value.gerencia && filterState.value.gerencia !== 'Todas') {
    filters.push({
      key: 'gerencia',
      label: 'Gerência',
      value: filterState.value.gerencia
    })
  }
  
  if (filterState.value.agencia && filterState.value.agencia !== 'Todas') {
    filters.push({
      key: 'agencia',
      label: 'Agência',
      value: filterState.value.agencia
    })
  }
  
  if (filterState.value.ggestao && filterState.value.ggestao !== 'Todos') {
    filters.push({
      key: 'ggestao',
      label: 'Gerente de gestão',
      value: filterState.value.ggestao
    })
  }
  
  if (filterState.value.gerente && filterState.value.gerente !== 'Todos') {
    filters.push({
      key: 'gerente',
      label: 'Gerente',
      value: filterState.value.gerente
    })
  }
  
  if (filterState.value.familia && filterState.value.familia !== 'Todas') {
    filters.push({
      key: 'familia',
      label: 'Família',
      value: filterState.value.familia
    })
  }
  
  if (filterState.value.indicador && filterState.value.indicador !== 'Todos') {
    filters.push({
      key: 'indicador',
      label: 'Indicador',
      value: filterState.value.indicador
    })
  }
  
  if (filterState.value.subindicador && filterState.value.subindicador !== 'Todos') {
    filters.push({
      key: 'subindicador',
      label: 'Subindicador',
      value: filterState.value.subindicador
    })
  }
  
  return filters
})

const handleRemove = (key: string) => {
  clearFilter(key as keyof FilterState)
}
</script>

<template>
  <div v-if="appliedFilters.length > 0" class="applied-bar">
    <div
      v-for="filter in appliedFilters"
      :key="filter.key"
      class="applied-chip"
    >
      <span class="k">{{ filter.label }}</span>
      <span class="v">{{ filter.value }}</span>
      <button
        type="button"
        title="Limpar"
        class="applied-x"
        :aria-label="`Remover ${filter.label}`"
        @click="handleRemove(filter.key)"
      >
        <i class="ti ti-x"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.applied-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 8px 0 6px;
}

.applied-chip {
  background: var(--omega-badge-bg, rgba(36, 107, 253, 0.12));
  border: 1px solid var(--stroke, #e7eaf2);
  color: var(--info, #246BFD);
  padding: 6px 8px 6px 10px;
  border-radius: 999px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  box-sizing: border-box;
}

.applied-chip .k {
  opacity: 0.85;
  font-weight: 800;
}

.applied-chip .v {
  font-weight: 800;
}

.applied-x {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  border: 1px solid var(--stroke, #e7eaf2);
  display: grid;
  place-items: center;
  background: var(--panel, #ffffff);
  color: var(--info, #246BFD);
  cursor: pointer;
  padding: 0;
  transition: all 0.15s ease;
  box-sizing: border-box;
}

.applied-x:hover {
  box-shadow: var(--shadow, 0 12px 28px rgba(17, 23, 41, 0.08));
  transform: translateY(-1px);
}

.applied-x i {
  font-size: 14px;
}
</style>

