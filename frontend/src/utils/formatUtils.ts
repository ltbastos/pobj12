export function formatBRL(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return 'R$ 0,00'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

export function formatPoints(value: number | string | null | undefined, options?: { withUnit?: boolean }): string {
  if (value === null || value === undefined || value === '') return options?.withUnit ? '0 pts' : '0'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return options?.withUnit ? '0 pts' : '0'
  const formatted = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(Math.round(num))
  return options?.withUnit ? `${formatted} pts` : formatted
}

export function formatPeso(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '0'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return '0'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

// Regras de sufixo para formatação abreviada
const SUFFIX_RULES = [
  { value: 1_000_000_000_000, singular: 'trilhão', plural: 'trilhões' },
  { value: 1_000_000_000, singular: 'bilhão', plural: 'bilhões' },
  { value: 1_000_000, singular: 'milhão', plural: 'milhões' },
  { value: 1_000, singular: 'mil', plural: 'mil' }
]

const CURRENCY_SYMBOL = 'R$'
const CURRENCY_LITERAL = ' '

function toNumber(value: number | string | null | undefined): number {
  if (value === null || value === undefined || value === '') return 0
  const n = typeof value === 'string' ? parseFloat(value) : value
  return Number.isFinite(n) ? n : 0
}

// Formata número com sufixo (mil, milhões, etc.)
export function formatNumberWithSuffix(value: number | string | null | undefined, options: { currency?: boolean } = {}): string {
  const n = toNumber(value)
  if (!Number.isFinite(n)) {
    return options.currency ? formatBRL(0) : '0'
  }
  
  const abs = Math.abs(n)
  
  // Se for menor que 1000, formata normalmente
  if (abs < 1000) {
    return options.currency ? formatBRL(n) : new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(Math.round(n))
  }
  
  // Encontra a regra de sufixo apropriada
  const rule = SUFFIX_RULES.find(r => abs >= r.value)
  if (!rule) {
    return options.currency ? formatBRL(n) : new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(Math.round(n))
  }
  
  const absScaled = abs / rule.value
  const nearInteger = Math.abs(absScaled - Math.round(absScaled)) < 0.05
  
  // Define número de casas decimais
  let digits: number
  if (absScaled >= 100) {
    digits = 0
  } else if (absScaled >= 10) {
    digits = nearInteger ? 0 : 1
  } else {
    digits = nearInteger ? 0 : 1
  }
  
  const numberFmt = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: digits,
    maximumFractionDigits: digits
  })
  
  const formatted = numberFmt.format(absScaled)
  const isSingular = Math.abs(absScaled - 1) < 0.05
  const label = isSingular ? rule.singular : rule.plural
  
  if (options.currency) {
    const sign = n < 0 ? '-' : ''
    return `${sign}${CURRENCY_SYMBOL}${CURRENCY_LITERAL}${formatted} ${label}`
  }
  
  const sign = n < 0 ? '-' : ''
  return `${sign}${formatted} ${label}`
}

export function formatByMetric(metric: string, value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return 'N/A'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return 'N/A'

  const metricLower = metric?.toLowerCase() || 'valor'
  
  switch (metricLower) {
    case 'valor':
    case 'brl':
    case 'variavel':
      return formatNumberWithSuffix(num, { currency: true })
    case 'qtd':
    case 'quantidade':
      return formatNumberWithSuffix(num, { currency: false })
    case 'perc':
    case 'percentual':
    case 'percent':
      return new Intl.NumberFormat('pt-BR', { style: 'percent', minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(num / 100)
    default:
      return formatNumberWithSuffix(num, { currency: false })
  }
}

export function formatMetricFull(metric: string, value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return 'N/A'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return 'N/A'

  const metricLower = metric?.toLowerCase() || 'valor'
  
  switch (metricLower) {
    case 'valor':
    case 'brl':
    case 'variavel':
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(num)
    case 'qtd':
    case 'quantidade':
      return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 }).format(num)
    case 'perc':
    case 'percentual':
    case 'percent':
      return new Intl.NumberFormat('pt-BR', { style: 'percent', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num / 100)
    default:
      return String(num)
  }
}

// Formata BRL de forma legível (abreviado)
export function formatBRLReadable(value: number | string | null | undefined): string {
  return formatNumberWithSuffix(value, { currency: true })
}

// Formata inteiro de forma legível (abreviado)
export function formatIntReadable(value: number | string | null | undefined): string {
  return formatNumberWithSuffix(value, { currency: false })
}

// Formata número inteiro simples (sem abreviação)
export function formatINT(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '0'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return '0'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(Math.round(num))
}

// Formata moeda (alias para formatBRL)
export function formatCurrency(value: number | string | null | undefined): string {
  return formatBRL(value)
}

// Formata data ISO para formato brasileiro
export function formatDate(isoDate: string | null | undefined): string {
  if (!isoDate) return '—'
  try {
    const [year, month, day] = isoDate.split('-')
    return `${day}/${month}/${year}`
  } catch {
    return isoDate
  }
}


