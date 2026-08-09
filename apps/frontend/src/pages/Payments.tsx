import { useState } from 'react'
import {
  useCreatePayment,
  useCustomers,
  useInvoices,
  usePayments,
  useSupplierBills,
  useSuppliers,
  useVoidPayment,
} from '../api/hooks'
import { errorMessage } from '../lib/api'
import { date, money, today } from '../lib/format'
import {
  Badge,
  Button,
  Card,
  EmptyState,
  ErrorState,
  Field,
  Loading,
  Table,
  inputClass,
} from '../components/ui'
import type { CreatePaymentPayload } from '../api/types'

export default function Payments() {
  const [showForm, setShowForm] = useState(false)
  const { data, isPending, isError, error } = usePayments()
  const voidPayment = useVoidPayment()

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-bold">سندات القبض والصرف</h1>
        <Button onClick={() => setShowForm((open) => !open)}>
          {showForm ? 'إغلاق' : 'سند جديد'}
        </Button>
      </div>

      {showForm && <PaymentForm onDone={() => setShowForm(false)} />}

      {voidPayment.isError && <ErrorState message={errorMessage(voidPayment.error)} />}

      <Card>
        {isPending ? (
          <Loading />
        ) : isError ? (
          <ErrorState message={errorMessage(error)} />
        ) : data.data.length === 0 ? (
          <EmptyState message="مفيش سندات مسجلة." />
        ) : (
          <Table
            headers={[
              'رقم السند',
              'النوع',
              'الطرف',
              'التاريخ',
              'المبلغ',
              'غير مخصّص',
              'الحالة',
              '',
            ]}
          >
            {data.data.map((payment) => (
              <tr key={payment.id} className="hover:bg-slate-50">
                <td className="num px-3 py-2 font-medium">{payment.payment_number}</td>
                <td className="px-3 py-2">
                  {payment.type === 'receipt' ? 'قبض' : 'صرف'}
                </td>
                <td className="px-3 py-2">
                  {payment.customer?.name ?? payment.supplier?.name ?? '—'}
                </td>
                <td className="px-3 py-2 text-ink-600">{date(payment.payment_date)}</td>
                <td className="num px-3 py-2">{money(payment.amount)}</td>
                <td className="num px-3 py-2 text-ink-600">
                  {money(payment.unallocated_amount)}
                </td>
                <td className="px-3 py-2">
                  <Badge value={payment.status} />
                </td>
                <td className="px-3 py-2">
                  {payment.status === 'posted' && (
                    <Button
                      size="sm"
                      variant="danger"
                      disabled={voidPayment.isPending}
                      onClick={() => voidPayment.mutate(payment.id)}
                    >
                      إلغاء
                    </Button>
                  )}
                </td>
              </tr>
            ))}
          </Table>
        )}
      </Card>

      <p className="text-xs text-ink-400">
        الإلغاء بيعمل قيد عكسي وحركة بنكية عكسية — السند بيفضل ظاهر للتدقيق ومش بيتحذف.
      </p>
    </div>
  )
}

function PaymentForm({ onDone }: { onDone: () => void }) {
  const [type, setType] = useState<'receipt' | 'payment'>('receipt')
  const [partyId, setPartyId] = useState('')
  const [amount, setAmount] = useState('')
  const [paymentDate, setPaymentDate] = useState(today())
  const [method, setMethod] = useState('bank_transfer')
  const [reference, setReference] = useState('')
  const [allocations, setAllocations] = useState<Record<number, string>>({})

  const customers = useCustomers()
  const suppliers = useSuppliers()
  const createPayment = useCreatePayment()

  // مستندات الطرف المختار اللي لسه عليها متبقي — متاحة للتخصيص.
  // enabled بيمنع أي نداء لغاية ما الطرف يتحدد ونوع السند يطابق.
  const isReceipt = type === 'receipt'
  const partySelected = partyId !== ''

  const invoices = useInvoices(
    isReceipt && partySelected
      ? { status: 'issued', customer_id: Number(partyId) }
      : {},
    { enabled: isReceipt && partySelected },
  )

  const bills = useSupplierBills(
    !isReceipt && partySelected
      ? { status: 'approved', supplier_id: Number(partyId) }
      : {},
    { enabled: !isReceipt && partySelected },
  )

  const loadingTargets = isReceipt ? invoices.isPending : bills.isPending

  /** المستندات المفتوحة للطرف — فواتير مبيعات للقبض، فواتير موردين للصرف. */
  const openTargets: { id: number; number: string; balance: number }[] = !partySelected
    ? []
    : isReceipt
      ? (invoices.data?.data ?? [])
          .filter((invoice) => invoice.balance_due > 0)
          .map((invoice) => ({
            id: invoice.id,
            number: invoice.invoice_number,
            balance: invoice.balance_due,
          }))
      : (bills.data?.data ?? [])
          .filter((bill) => bill.balance_due > 0)
          .map((bill) => ({
            id: bill.id,
            number: bill.bill_number,
            balance: bill.balance_due,
          }))

  const allocatedTotal = Object.values(allocations).reduce(
    (sum, value) => sum + (Number(value) || 0),
    0,
  )

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()

    const payload: CreatePaymentPayload = {
      type,
      payment_date: paymentDate,
      amount: Number(amount),
      method,
      ...(reference ? { reference } : {}),
      ...(type === 'receipt'
        ? { customer_id: Number(partyId) }
        : { supplier_id: Number(partyId) }),
    }

    const entries = Object.entries(allocations)
      .map(([targetId, value]) => ({
        target_id: Number(targetId),
        amount: Number(value) || 0,
      }))
      .filter((entry) => entry.amount > 0)

    if (entries.length > 0) {
      payload.allocations = entries
    }

    createPayment.mutate(payload, { onSuccess: onDone })
  }

  const parties = type === 'receipt' ? customers.data : suppliers.data

  return (
    <Card title="سند جديد">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <Field label="النوع">
            <select
              value={type}
              onChange={(e) => {
                setType(e.target.value as 'receipt' | 'payment')
                setPartyId('')
                setAllocations({})
              }}
              className={inputClass}
            >
              <option value="receipt">سند قبض (من عميل)</option>
              <option value="payment">سند صرف (لمورد)</option>
            </select>
          </Field>

          <Field label={type === 'receipt' ? 'العميل' : 'المورد'}>
            <select
              value={partyId}
              onChange={(e) => {
                setPartyId(e.target.value)
                setAllocations({})
              }}
              className={inputClass}
              required
            >
              <option value="">اختر…</option>
              {(parties ?? []).map((party) => (
                <option key={party.id} value={party.id}>
                  {party.name}
                </option>
              ))}
            </select>
          </Field>

          <Field label="المبلغ">
            <input
              type="number"
              step="0.01"
              min="0.01"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className={inputClass}
              dir="ltr"
              required
            />
          </Field>

          <Field label="التاريخ">
            <input
              type="date"
              value={paymentDate}
              onChange={(e) => setPaymentDate(e.target.value)}
              className={inputClass}
              dir="ltr"
              required
            />
          </Field>

          <Field label="طريقة الدفع">
            <select
              value={method}
              onChange={(e) => setMethod(e.target.value)}
              className={inputClass}
            >
              <option value="bank_transfer">تحويل بنكي</option>
              <option value="cash">نقدي</option>
              <option value="cheque">شيك</option>
              <option value="card">بطاقة</option>
              <option value="other">أخرى</option>
            </select>
          </Field>

          <Field label="المرجع (اختياري)">
            <input
              value={reference}
              onChange={(e) => setReference(e.target.value)}
              className={inputClass}
              placeholder="رقم الحوالة أو الشيك"
            />
          </Field>
        </div>

        {partySelected && (
          <div className="rounded-lg border border-slate-200 p-4">
            <p className="mb-3 text-xs font-semibold text-ink-600">
              تخصيص المبلغ على {isReceipt ? 'فواتير مبيعات' : 'فواتير موردين'} (اختياري — من
              غيره بيتسجّل كدفعة على الحساب)
            </p>

            {loadingTargets ? (
              <Loading />
            ) : openTargets.length === 0 ? (
              <EmptyState
                message={
                  isReceipt
                    ? 'مفيش فواتير صادرة عليها متبقي للعميل ده.'
                    : 'مفيش فواتير معتمدة عليها متبقي للمورد ده.'
                }
              />
            ) : (
              <div className="space-y-2">
                {openTargets.map((target) => (
                  <div
                    key={target.id}
                    className="flex flex-wrap items-center gap-3 text-sm"
                  >
                    <span className="num min-w-40 font-medium">{target.number}</span>
                    <span className="num text-ink-400">
                      متبقي: {money(target.balance)}
                    </span>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      max={target.balance}
                      value={allocations[target.id] ?? ''}
                      onChange={(e) =>
                        setAllocations((current) => ({
                          ...current,
                          [target.id]: e.target.value,
                        }))
                      }
                      placeholder="0.00"
                      dir="ltr"
                      className="w-32 rounded-lg border border-slate-200 px-2 py-1 text-sm outline-none focus:border-brand-500"
                    />
                  </div>
                ))}

                <p className="num pt-2 text-xs text-ink-400">
                  إجمالي المخصّص: {money(allocatedTotal)}
                  {Number(amount) > 0 && allocatedTotal > Number(amount) && (
                    <span className="text-rose-600"> — أكبر من قيمة السند</span>
                  )}
                </p>
              </div>
            )}
          </div>
        )}

        {createPayment.isError && <ErrorState message={errorMessage(createPayment.error)} />}

        <div className="flex gap-2">
          <Button type="submit" disabled={createPayment.isPending}>
            {createPayment.isPending ? 'جارٍ الترحيل…' : 'ترحيل السند'}
          </Button>
          <Button variant="ghost" onClick={onDone}>
            إلغاء
          </Button>
        </div>
      </form>
    </Card>
  )
}
