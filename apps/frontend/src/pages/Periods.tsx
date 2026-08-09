import { useState } from 'react'
import { Lock, LockOpen } from 'lucide-react'
import {
  useClosePeriod,
  useGeneratePeriods,
  usePeriods,
  useYearEndClosing,
} from '../api/hooks'
import { errorMessage } from '../lib/api'
import { money } from '../lib/format'
import {
  Button,
  Card,
  EmptyState,
  ErrorState,
  Field,
  Loading,
  Table,
  inputClass,
} from '../components/ui'

export default function Periods() {
  const currentYear = new Date().getFullYear()
  const [fiscalYear, setFiscalYear] = useState(currentYear)

  const { data, isPending, isError, error } = usePeriods(fiscalYear)
  const generate = useGeneratePeriods()
  const togglePeriod = useClosePeriod()
  const yearEnd = useYearEndClosing()

  const allClosed = (data ?? []).length > 0 && (data ?? []).every((p) => p.status === 'closed')

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold">الفترات المحاسبية</h1>

      <div className="flex flex-wrap items-end gap-4">
        <Field label="السنة المالية">
          <input
            type="number"
            min="2000"
            max="2100"
            value={fiscalYear}
            onChange={(e) => setFiscalYear(Number(e.target.value))}
            className={`${inputClass} w-32`}
            dir="ltr"
          />
        </Field>

        <Button
          variant="ghost"
          disabled={generate.isPending}
          onClick={() => generate.mutate(fiscalYear)}
        >
          {generate.isPending ? 'جارٍ الإنشاء…' : 'إنشاء فترات السنة'}
        </Button>
      </div>

      {generate.isError && <ErrorState message={errorMessage(generate.error)} />}
      {togglePeriod.isError && <ErrorState message={errorMessage(togglePeriod.error)} />}

      <Card title={`فترات ${fiscalYear}`}>
        {isPending ? (
          <Loading />
        ) : isError ? (
          <ErrorState message={errorMessage(error)} />
        ) : data.length === 0 ? (
          <EmptyState message="مفيش فترات معرّفة للسنة دي — اضغط «إنشاء فترات السنة»." />
        ) : (
          <Table headers={['الفترة', 'من', 'إلى', 'الحالة', 'أُقفلت بواسطة', '']}>
            {data.map((period) => (
              <tr key={period.id} className="hover:bg-slate-50">
                <td className="num px-3 py-2 font-medium">{period.name}</td>
                <td className="num px-3 py-2 text-ink-600">{period.start_date}</td>
                <td className="num px-3 py-2 text-ink-600">{period.end_date}</td>
                <td className="px-3 py-2">
                  <span
                    className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${
                      period.status === 'closed'
                        ? 'bg-slate-100 text-ink-600 ring-slate-200'
                        : 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                    }`}
                  >
                    {period.status === 'closed' ? (
                      <>
                        <Lock className="size-3" /> مقفلة
                      </>
                    ) : (
                      <>
                        <LockOpen className="size-3" /> مفتوحة
                      </>
                    )}
                  </span>
                </td>
                <td className="px-3 py-2 text-ink-600">{period.closed_by?.name ?? '—'}</td>
                <td className="px-3 py-2">
                  <Button
                    size="sm"
                    variant={period.status === 'closed' ? 'danger' : 'ghost'}
                    disabled={togglePeriod.isPending}
                    onClick={() =>
                      togglePeriod.mutate({
                        id: period.id,
                        action: period.status === 'closed' ? 'reopen' : 'close',
                      })
                    }
                  >
                    {period.status === 'closed' ? 'إعادة فتح' : 'إقفال'}
                  </Button>
                </td>
              </tr>
            ))}
          </Table>
        )}
      </Card>

      <Card title="الإقفال السنوي">
        <p className="mb-4 text-sm text-ink-600">
          بيصفّر حسابات الإيرادات والمصروفات وينقل صافي النتيجة للأرباح المحتجزة (3200) بقيد
          مُرحّل بتاريخ ٣١ ديسمبر. الإجراء بيتنفّذ مرة واحدة للسنة.
        </p>

        {yearEnd.isSuccess && (
          <div className="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">
            {yearEnd.data.message} — صافي النتيجة:{' '}
            <span className="num font-semibold">{money(yearEnd.data.net_income)}</span>
          </div>
        )}

        {yearEnd.isError && <ErrorState message={errorMessage(yearEnd.error)} />}

        <Button
          disabled={yearEnd.isPending}
          onClick={() => yearEnd.mutate(fiscalYear)}
        >
          {yearEnd.isPending ? 'جارٍ الإقفال…' : `إقفال السنة المالية ${fiscalYear}`}
        </Button>

        {!allClosed && (data ?? []).length > 0 && (
          <p className="mt-3 text-xs text-amber-600">
            تنبيه: لسه في فترات مفتوحة في {fiscalYear} — يُفضّل تقفلها الأول عشان ما تتسجّلش
            حركات بعد الإقفال.
          </p>
        )}
      </Card>
    </div>
  )
}
