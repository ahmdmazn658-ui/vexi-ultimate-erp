import { useState } from 'react'
import { Trash2 } from 'lucide-react'
import { useCreateInvoice, useCustomers, useProducts } from '../api/hooks'
import { errorMessage } from '../lib/api'
import { money } from '../lib/format'
import {
  Button,
  Card,
  ErrorState,
  Field,
  Table,
  inputClass,
} from '../components/ui'
import type { CreateInvoicePayload } from '../api/types'

interface DraftLine {
  key: number
  product_id: string
  item_name: string
  quantity: string
  unit_price: string
}

let nextKey = 1
const blankLine = (): DraftLine => ({
  key: nextKey++,
  product_id: '',
  item_name: '',
  quantity: '1',
  unit_price: '',
})

export default function InvoiceForm({ onDone }: { onDone: () => void }) {
  const [customerId, setCustomerId] = useState('')
  const [dueDate, setDueDate] = useState('')
  const [vatRate, setVatRate] = useState('15')
  const [notes, setNotes] = useState('')
  const [lines, setLines] = useState<DraftLine[]>([blankLine()])

  const customers = useCustomers()
  const products = useProducts()
  const createInvoice = useCreateInvoice()

  function updateLine(key: number, patch: Partial<DraftLine>) {
    setLines((current) =>
      current.map((line) => (line.key === key ? { ...line, ...patch } : line)),
    )
  }

  /** اختيار منتج بيملّي الاسم والسعر تلقائياً، مع إمكانية التعديل بعدها. */
  function selectProduct(key: number, productId: string) {
    const product = products.data?.find((p) => String(p.id) === productId)
    updateLine(key, {
      product_id: productId,
      ...(product
        ? { item_name: product.name, unit_price: String(product.sale_price) }
        : {}),
    })
  }

  const subtotal = lines.reduce(
    (sum, line) => sum + (Number(line.quantity) || 0) * (Number(line.unit_price) || 0),
    0,
  )
  const vatAmount = subtotal * ((Number(vatRate) || 0) / 100)

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()

    const items = lines
      .filter((line) => line.item_name.trim() && Number(line.quantity) > 0)
      .map((line) => ({
        ...(line.product_id ? { product_id: Number(line.product_id) } : {}),
        item_name: line.item_name.trim(),
        quantity: Number(line.quantity),
        unit_price: Number(line.unit_price) || 0,
      }))

    const payload: CreateInvoicePayload = {
      customer_id: Number(customerId),
      vat_rate: Number(vatRate),
      items,
      ...(dueDate ? { due_date: dueDate } : {}),
      ...(notes ? { notes } : {}),
    }

    createInvoice.mutate(payload, { onSuccess: onDone })
  }

  const canSubmit =
    customerId !== '' &&
    lines.some((line) => line.item_name.trim() && Number(line.quantity) > 0)

  return (
    <Card title="فاتورة مبيعات جديدة">
      <form onSubmit={handleSubmit} className="space-y-5">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Field label="العميل">
            <select
              value={customerId}
              onChange={(e) => setCustomerId(e.target.value)}
              className={inputClass}
              required
            >
              <option value="">اختر…</option>
              {(customers.data ?? []).map((customer) => (
                <option key={customer.id} value={customer.id}>
                  {customer.name}
                </option>
              ))}
            </select>
          </Field>

          <Field label="تاريخ الاستحقاق (اختياري)">
            <input
              type="date"
              value={dueDate}
              onChange={(e) => setDueDate(e.target.value)}
              className={inputClass}
              dir="ltr"
            />
          </Field>

          <Field label="نسبة الضريبة %">
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              value={vatRate}
              onChange={(e) => setVatRate(e.target.value)}
              className={inputClass}
              dir="ltr"
            />
          </Field>

          <Field label="ملاحظات (اختياري)">
            <input
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              className={inputClass}
            />
          </Field>
        </div>

        <div className="rounded-lg border border-slate-200 p-4">
          <p className="mb-3 text-xs font-semibold text-ink-600">بنود الفاتورة</p>

          <Table headers={['المنتج (اختياري)', 'الوصف', 'الكمية', 'سعر الوحدة', 'الإجمالي', '']}>
            {lines.map((line) => {
              const lineTotal =
                (Number(line.quantity) || 0) * (Number(line.unit_price) || 0)

              return (
                <tr key={line.key}>
                  <td className="px-2 py-2">
                    <select
                      value={line.product_id}
                      onChange={(e) => selectProduct(line.key, e.target.value)}
                      className={inputClass}
                    >
                      <option value="">— بند حر —</option>
                      {(products.data ?? []).map((product) => (
                        <option key={product.id} value={product.id}>
                          {product.name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td className="px-2 py-2">
                    <input
                      value={line.item_name}
                      onChange={(e) => updateLine(line.key, { item_name: e.target.value })}
                      className={inputClass}
                      placeholder="وصف البند"
                    />
                  </td>
                  <td className="px-2 py-2">
                    <input
                      type="number"
                      step="0.01"
                      min="0.01"
                      value={line.quantity}
                      onChange={(e) => updateLine(line.key, { quantity: e.target.value })}
                      className={`${inputClass} w-24`}
                      dir="ltr"
                    />
                  </td>
                  <td className="px-2 py-2">
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      value={line.unit_price}
                      onChange={(e) => updateLine(line.key, { unit_price: e.target.value })}
                      className={`${inputClass} w-32`}
                      dir="ltr"
                    />
                  </td>
                  <td className="num px-2 py-2 whitespace-nowrap">{money(lineTotal)}</td>
                  <td className="px-2 py-2">
                    {lines.length > 1 && (
                      <button
                        type="button"
                        onClick={() =>
                          setLines((current) => current.filter((l) => l.key !== line.key))
                        }
                        className="rounded p-1 text-ink-400 transition-colors hover:bg-rose-50 hover:text-rose-600"
                        aria-label="حذف البند"
                      >
                        <Trash2 className="size-4" />
                      </button>
                    )}
                  </td>
                </tr>
              )
            })}
          </Table>

          <div className="mt-3">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setLines((current) => [...current, blankLine()])}
            >
              + إضافة بند
            </Button>
          </div>
        </div>

        <div className="flex flex-wrap justify-end gap-8 border-t border-slate-100 pt-4 text-sm">
          <div>
            <p className="text-xs text-ink-400">المجموع قبل الضريبة</p>
            <p className="num mt-1 font-semibold">{money(subtotal)}</p>
          </div>
          <div>
            <p className="text-xs text-ink-400">الضريبة ({vatRate || 0}%)</p>
            <p className="num mt-1 font-semibold">{money(vatAmount)}</p>
          </div>
          <div>
            <p className="text-xs text-ink-400">الإجمالي</p>
            <p className="num mt-1 text-lg font-bold text-brand-700">
              {money(subtotal + vatAmount)}
            </p>
          </div>
        </div>

        {createInvoice.isError && <ErrorState message={errorMessage(createInvoice.error)} />}

        <div className="flex gap-2">
          <Button type="submit" disabled={createInvoice.isPending || !canSubmit}>
            {createInvoice.isPending ? 'جارٍ الحفظ…' : 'حفظ كمسودة'}
          </Button>
          <Button variant="ghost" onClick={onDone}>
            إلغاء
          </Button>
        </div>

        <p className="text-xs text-ink-400">
          الفاتورة بتتحفظ كمسودة — الإصدار (اللي بيولّد الرقم الرسمي والقيد والـ QR) خطوة منفصلة.
        </p>
      </form>
    </Card>
  )
}
