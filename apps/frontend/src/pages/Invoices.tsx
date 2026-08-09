import { useState } from 'react'
import { useInvoices, useIssueInvoice } from '../api/hooks'
import { errorMessage } from '../lib/api'
import { date, money } from '../lib/format'
import InvoiceForm from './InvoiceForm'
import {
  Badge,
  Button,
  Card,
  EmptyState,
  ErrorState,
  Loading,
  Table,
} from '../components/ui'

const filters = [
  { value: '', label: 'الكل' },
  { value: 'draft', label: 'مسودة' },
  { value: 'issued', label: 'صادرة' },
  { value: 'paid', label: 'مسددة' },
]

export default function Invoices() {
  const [status, setStatus] = useState('')
  const [showForm, setShowForm] = useState(false)
  const { data, isPending, isError, error } = useInvoices(status ? { status } : {})
  const issue = useIssueInvoice()

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-bold">فواتير المبيعات</h1>
        <Button onClick={() => setShowForm((open) => !open)}>
          {showForm ? 'إغلاق' : 'فاتورة جديدة'}
        </Button>
      </div>

      {showForm && <InvoiceForm onDone={() => setShowForm(false)} />}

      <div className="flex flex-wrap gap-2">
        {filters.map((filter) => (
          <button
            key={filter.value}
            onClick={() => setStatus(filter.value)}
            className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${
              status === filter.value
                ? 'bg-brand-600 text-white'
                : 'bg-white text-ink-600 ring-1 ring-slate-200 hover:bg-slate-50'
            }`}
          >
            {filter.label}
          </button>
        ))}
      </div>

      {issue.isError && <ErrorState message={errorMessage(issue.error)} />}

      <Card>
        {isPending ? (
          <Loading />
        ) : isError ? (
          <ErrorState message={errorMessage(error)} />
        ) : data.data.length === 0 ? (
          <EmptyState message="مفيش فواتير مطابقة." />
        ) : (
          <Table
            headers={[
              'رقم الفاتورة',
              'العميل',
              'التاريخ',
              'الاستحقاق',
              'الإجمالي',
              'المتبقي',
              'الحالة',
              'السداد',
              '',
            ]}
          >
            {data.data.map((invoice) => (
              <tr key={invoice.id} className="hover:bg-slate-50">
                <td className="num px-3 py-2 font-medium">{invoice.invoice_number}</td>
                <td className="px-3 py-2">{invoice.customer?.name ?? '—'}</td>
                <td className="px-3 py-2 text-ink-600">{date(invoice.invoice_date)}</td>
                <td className="px-3 py-2 text-ink-600">{date(invoice.due_date)}</td>
                <td className="num px-3 py-2">{money(invoice.total_amount)}</td>
                <td className="num px-3 py-2 font-medium">{money(invoice.balance_due)}</td>
                <td className="px-3 py-2">
                  <Badge value={invoice.status} />
                </td>
                <td className="px-3 py-2">
                  <Badge value={invoice.payment_status} />
                </td>
                <td className="px-3 py-2">
                  {invoice.status === 'draft' && (
                    <Button
                      size="sm"
                      disabled={issue.isPending}
                      onClick={() => issue.mutate(invoice.id)}
                    >
                      إصدار
                    </Button>
                  )}
                </td>
              </tr>
            ))}
          </Table>
        )}
      </Card>

      <p className="text-xs text-ink-400">
        الإصدار بيولّد رقم فاتورة رسمي و QR بصيغة ZATCA، وبيرحّل القيد المحاسبي تلقائياً.
      </p>
    </div>
  )
}
