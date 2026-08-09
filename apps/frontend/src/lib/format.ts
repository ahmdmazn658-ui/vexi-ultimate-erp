const currencyFormatter = new Intl.NumberFormat('ar-SA', {
  style: 'currency',
  currency: 'SAR',
  maximumFractionDigits: 2,
})

const numberFormatter = new Intl.NumberFormat('ar-SA', {
  maximumFractionDigits: 2,
  minimumFractionDigits: 2,
})

export function money(value: number | string | null | undefined): string {
  const n = Number(value ?? 0)
  return currencyFormatter.format(Number.isFinite(n) ? n : 0)
}

export function num(value: number | string | null | undefined): string {
  const n = Number(value ?? 0)
  return numberFormatter.format(Number.isFinite(n) ? n : 0)
}

export function date(value: string | null | undefined): string {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '—'
  return new Intl.DateTimeFormat('ar-SA', { dateStyle: 'medium' }).format(d)
}

export function today(): string {
  return new Date().toISOString().slice(0, 10)
}

export function startOfYear(): string {
  return `${new Date().getFullYear()}-01-01`
}
