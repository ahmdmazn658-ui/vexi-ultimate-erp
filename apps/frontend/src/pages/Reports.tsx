import { useState } from 'react'
import {
  useAging,
  useBalanceSheet,
  useIncomeStatement,
  useTrialBalance,
} from '../api/hooks'
import { errorMessage } from '../lib/api'
import { money, startOfYear, today } from '../lib/format'
import {
  Card,
  EmptyState,
  ErrorState,
  Field,
  Loading,
  Table,
  inputClass,
} from '../components/ui'
import type { AgingBucket, StatementLine } from '../api/types'

type Tab = 'trial' | 'income' | 'balance' | 'ar' | 'ap'

const tabs: { key: Tab; label: string }[] = [
  { key: 'trial', label: 'ميزان المراجعة' },
  { key: 'income', label: 'قائمة الدخل' },
  { key: 'balance', label: 'المركز المالي' },
  { key: 'ar', label: 'أعمار الذمم المدينة' },
  { key: 'ap', label: 'أعمار الذمم الدائنة' },
]

export default function Reports() {
  const [tab, setTab] = useState<Tab>('trial')
  const [from, setFrom] = useState(startOfYear())
  const [to, setTo] = useState(today())

  const usesRange = tab === 'trial' || tab === 'income'

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold">التقارير المالية</h1>

      <div className="flex flex-wrap gap-2">
        {tabs.map((item) => (
          <button
            key={item.key}
            onClick={() => setTab(item.key)}
            className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${
              tab === item.key
                ? 'bg-brand-600 text-white'
                : 'bg-white text-ink-600 ring-1 ring-slate-200 hover:bg-slate-50'
            }`}
          >
            {item.label}
          </button>
        ))}
      </div>

      <div className="flex flex-wrap gap-4">
        {usesRange && (
          <Field label="من">
            <input
              type="date"
              value={from}
              onChange={(e) => setFrom(e.target.value)}
              className={inputClass}
              dir="ltr"
            />
          </Field>
        )}
        <Field label={usesRange ? 'إلى' : 'كما في تاريخ'}>
          <input
            type="date"
            value={to}
            onChange={(e) => setTo(e.target.value)}
            className={inputClass}
            dir="ltr"
          />
        </Field>
      </div>

      {tab === 'trial' && <TrialBalanceReport from={from} to={to} />}
      {tab === 'income' && <IncomeStatementReport from={from} to={to} />}
      {tab === 'balance' && <BalanceSheetReport asOf={to} />}
      {tab === 'ar' && <AgingReportView kind="ar" asOf={to} />}
      {tab === 'ap' && <AgingReportView kind="ap" asOf={to} />}
    </div>
  )
}

function BalanceCheck({ ok }: { ok: boolean }) {
  return (
    <span
      className={`rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${
        ok
          ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
          : 'bg-rose-50 text-rose-700 ring-rose-200'
      }`}
    >
      {ok ? 'متوازن ✓' : 'غير متوازن ✗'}
    </span>
  )
}

function TrialBalanceReport({ from, to }: { from: string; to: string }) {
  const { data, isPending, isError, error } = useTrialBalance({ from, to })

  if (isPending) return <Loading />
  if (isError) return <ErrorState message={errorMessage(error)} />

  return (
    <Card
      title="ميزان المراجعة"
      action={<BalanceCheck ok={data.totals.is_balanced} />}
    >
      {data.rows.length === 0 ? (
        <EmptyState message="مفيش قيود مُرحّلة في الفترة دي." />
      ) : (
        <Table headers={['الكود', 'الحساب', 'مدين', 'دائن']}>
          {data.rows.map((row) => (
            <tr key={row.account_id} className="hover:bg-slate-50">
              <td className="num px-3 py-2 text-ink-400">{row.account_code}</td>
              <td className="px-3 py-2">{row.account_name_ar ?? row.account_name}</td>
              <td className="num px-3 py-2">
                {row.balance_debit > 0 ? money(row.balance_debit) : '—'}
              </td>
              <td className="num px-3 py-2">
                {row.balance_credit > 0 ? money(row.balance_credit) : '—'}
              </td>
            </tr>
          ))}
          <tr className="border-t-2 border-slate-300 font-bold">
            <td className="px-3 py-2" colSpan={2}>
              الإجمالي
            </td>
            <td className="num px-3 py-2">{money(data.totals.debit)}</td>
            <td className="num px-3 py-2">{money(data.totals.credit)}</td>
          </tr>
        </Table>
      )}
    </Card>
  )
}

function LineRows({ lines }: { lines: StatementLine[] }) {
  return (
    <>
      {lines.map((line) => (
        <tr key={line.account_id} className="hover:bg-slate-50">
          <td className="num px-3 py-2 text-ink-400">{line.account_code}</td>
          <td className="px-3 py-2">{line.account_name_ar ?? line.account_name}</td>
          <td className="num px-3 py-2">{money(line.amount)}</td>
        </tr>
      ))}
    </>
  )
}

function IncomeStatementReport({ from, to }: { from: string; to: string }) {
  const { data, isPending, isError, error } = useIncomeStatement({ from, to })

  if (isPending) return <Loading />
  if (isError) return <ErrorState message={errorMessage(error)} />

  return (
    <div className="grid gap-6 lg:grid-cols-2">
      <Card title="الإيرادات">
        <Table headers={['الكود', 'الحساب', 'المبلغ']}>
          <LineRows lines={data.revenue.lines} />
          <tr className="border-t-2 border-slate-300 font-bold">
            <td className="px-3 py-2" colSpan={2}>
              إجمالي الإيرادات
            </td>
            <td className="num px-3 py-2">{money(data.revenue.total)}</td>
          </tr>
        </Table>
      </Card>

      <Card title="المصروفات">
        <Table headers={['الكود', 'الحساب', 'المبلغ']}>
          <LineRows lines={data.expenses.lines} />
          <tr className="border-t-2 border-slate-300 font-bold">
            <td className="px-3 py-2" colSpan={2}>
              إجمالي المصروفات
            </td>
            <td className="num px-3 py-2">{money(data.expenses.total)}</td>
          </tr>
        </Table>
      </Card>

      <Card title="النتيجة" className="lg:col-span-2">
        <div className="flex flex-wrap items-baseline gap-6">
          <div>
            <p className="text-xs text-ink-400">صافي الربح</p>
            <p
              className={`num mt-1 text-2xl font-bold ${
                data.net_income >= 0 ? 'text-emerald-600' : 'text-rose-600'
              }`}
            >
              {money(data.net_income)}
            </p>
          </div>
          {data.margin_percent !== null && (
            <div>
              <p className="text-xs text-ink-400">هامش الربح</p>
              <p className="num mt-1 text-2xl font-bold">{data.margin_percent}%</p>
            </div>
          )}
        </div>
      </Card>
    </div>
  )
}

function BalanceSheetReport({ asOf }: { asOf: string }) {
  const { data, isPending, isError, error } = useBalanceSheet(asOf)

  if (isPending) return <Loading />
  if (isError) return <ErrorState message={errorMessage(error)} />

  return (
    <div className="space-y-6">
      <div className="flex justify-end">
        <BalanceCheck ok={data.check.is_balanced} />
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card title="الأصول">
          <Table headers={['الكود', 'الحساب', 'المبلغ']}>
            <LineRows lines={data.assets.lines} />
            <tr className="border-t-2 border-slate-300 font-bold">
              <td className="px-3 py-2" colSpan={2}>
                إجمالي الأصول
              </td>
              <td className="num px-3 py-2">{money(data.assets.total)}</td>
            </tr>
          </Table>
        </Card>

        <Card title="الالتزامات وحقوق الملكية">
          <Table headers={['الكود', 'الحساب', 'المبلغ']}>
            <LineRows lines={data.liabilities.lines} />
            <LineRows lines={data.equity.lines} />
            <tr className="hover:bg-slate-50">
              <td className="px-3 py-2 text-ink-400">—</td>
              <td className="px-3 py-2">أرباح محتجزة (الفترة)</td>
              <td className="num px-3 py-2">{money(data.equity.retained_earnings)}</td>
            </tr>
            <tr className="border-t-2 border-slate-300 font-bold">
              <td className="px-3 py-2" colSpan={2}>
                الإجمالي
              </td>
              <td className="num px-3 py-2">
                {money(data.check.liabilities_plus_equity)}
              </td>
            </tr>
          </Table>
        </Card>
      </div>
    </div>
  )
}

const bucketLabels: Record<AgingBucket, string> = {
  current: 'غير مستحقة',
  '1_30': '1–30 يوم',
  '31_60': '31–60 يوم',
  '61_90': '61–90 يوم',
  over_90: 'أكثر من 90 يوم',
}

function AgingReportView({ kind, asOf }: { kind: 'ar' | 'ap'; asOf: string }) {
  const { data, isPending, isError, error } = useAging(kind, asOf)

  if (isPending) return <Loading />
  if (isError) return <ErrorState message={errorMessage(error)} />

  const buckets = Object.entries(data.buckets) as [AgingBucket, number][]

  return (
    <div className="space-y-6">
      <div className="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
        {buckets.map(([bucket, amount]) => (
          <div
            key={bucket}
            className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
          >
            <p className="text-xs text-ink-400">{bucketLabels[bucket]}</p>
            <p
              className={`num mt-1 font-bold ${
                bucket === 'over_90' && amount > 0 ? 'text-rose-600' : ''
              }`}
            >
              {money(amount)}
            </p>
          </div>
        ))}
      </div>

      <Card
        title={kind === 'ar' ? 'تفاصيل ذمم العملاء' : 'تفاصيل ذمم الموردين'}
        action={
          <span className="num text-sm font-semibold">الإجمالي: {money(data.total)}</span>
        }
      >
        {data.rows.length === 0 ? (
          <EmptyState message="مفيش أرصدة قائمة." />
        ) : (
          <Table
            headers={['المستند', 'الطرف', 'الاستحقاق', 'المتبقي', 'أيام التأخير', 'الشريحة']}
          >
            {data.rows.map((row) => (
              <tr key={row.id} className="hover:bg-slate-50">
                <td className="num px-3 py-2 font-medium">{row.number}</td>
                <td className="px-3 py-2">{row.party ?? '—'}</td>
                <td className="px-3 py-2 text-ink-600">{row.due_date ?? '—'}</td>
                <td className="num px-3 py-2">{money(row.balance)}</td>
                <td className="num px-3 py-2">
                  {row.days_overdue > 0 ? row.days_overdue : '—'}
                </td>
                <td className="px-3 py-2 text-ink-600">{bucketLabels[row.bucket]}</td>
              </tr>
            ))}
          </Table>
        )}
      </Card>
    </div>
  )
}
