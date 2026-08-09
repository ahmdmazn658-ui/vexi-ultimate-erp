import { Trash2 } from 'lucide-react'
import { useRefOptions, type Row } from '../api/resource'
import { num } from '../lib/format'
import type { Field, ItemsConfig } from '../resources/types'
import { FieldInput, type FormValues } from './FormFields'
import { Button } from './ui'

export type ItemRow = FormValues

export function emptyItem(fields: Field[]): ItemRow {
  const row: ItemRow = {}
  for (const field of fields) {
    row[field.name] = String(field.defaultValue ?? '')
  }
  return row
}

/**
 * محرّر بنود المستندات (فواتير، أوامر شراء، قيود، قوائم مواد).
 *
 * لما يتحدد منتج في سطر، الاسم والسعر بيتملّوا تلقائيًا من بيانات المنتج —
 * نفس سلوك شاشة إنشاء الفاتورة الموجودة.
 */
export function LineItems({
  config,
  rows,
  onChange,
}: {
  config: ItemsConfig
  rows: ItemRow[]
  onChange: (rows: ItemRow[]) => void
}) {
  const productField = config.fields.find((field) => field.ref === 'products')
  const { data: products } = useRefOptions(productField ? 'products' : undefined)

  const minRows = config.minRows ?? 1

  function update(index: number, name: string, value: string) {
    const next = rows.map((row, i) => (i === index ? { ...row, [name]: value } : row))

    // ملء تلقائي من المنتج المختار
    if (productField && name === productField.name && value) {
      const product = products?.find((option) => option.value === Number(value))?.row as
        | Row
        | undefined

      if (product) {
        const nameField = config.fields.find((field) => field.name === 'item_name')
        const priceField = config.fields.find((field) => field.name === 'unit_price')

        if (nameField) next[index].item_name = String(product.name ?? '')
        if (priceField && !next[index].unit_price) {
          next[index].unit_price = String(product.sale_price ?? '')
        }
      }
    }

    onChange(next)
  }

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between">
        <h3 className="text-sm font-semibold text-ink-900">{config.label}</h3>
        <Button
          size="sm"
          variant="ghost"
          onClick={() => onChange([...rows, emptyItem(config.fields)])}
        >
          {config.addLabel}
        </Button>
      </div>

      <div className="space-y-3">
        {rows.map((row, index) => (
          <div
            key={index}
            className="rounded-lg border border-slate-200 bg-slate-50/60 p-3"
          >
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              {config.fields.map((field) => (
                <div
                  key={field.name}
                  className={field.span === 2 ? 'sm:col-span-2' : undefined}
                >
                  <span className="mb-1 block text-xs font-medium text-ink-600">
                    {field.label}
                  </span>
                  <FieldInput
                    field={field}
                    value={row[field.name] ?? ''}
                    onChange={(value) => update(index, field.name, value)}
                  />
                </div>
              ))}
            </div>

            <div className="mt-2 flex items-center justify-between">
              {config.computed ? (
                <p className="text-xs text-ink-600">
                  {config.computed.label}:{' '}
                  <span className="num font-medium">
                    {num(config.computed.of(row as unknown as Row))}
                  </span>
                </p>
              ) : (
                <span />
              )}

              {rows.length > minRows && (
                <button
                  onClick={() => onChange(rows.filter((_, i) => i !== index))}
                  className="flex items-center gap-1 text-xs text-rose-600 hover:text-rose-700"
                >
                  <Trash2 className="size-3.5" />
                  حذف السطر
                </button>
              )}
            </div>
          </div>
        ))}
      </div>

      {config.note && <p className="text-xs text-ink-400">{config.note}</p>}
    </div>
  )
}
