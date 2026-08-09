import {
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { useDashboard } from '../api/hooks'
import { errorMessage } from '../lib/api'
import { money, num } from '../lib/format'
import {
  Card,
  EmptyState,
  ErrorState,
  Loading,
  StatCard,
  Table,
} from '../components/ui'

export default function Dashboard() {
  const { data, isPending, isError, error } = useDashboard()

  if (isPending) return <Loading />
  if (isError) return <ErrorState message={errorMessage(error)} />

  const { financial, receivables, payables, operations } = data

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold">لوحة المؤشرات</h1>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard
          label="إيرادات الشهر"
          value={money(financial.revenue_mtd)}
          hint={`السنة: ${money(financial.revenue_ytd)}`}
        />
        <StatCard
          label="صافي ربح الشهر"
          value={money(financial.net_income_mtd)}
          tone={financial.net_income_mtd >= 0 ? 'positive' : 'negative'}
          hint={`السنة: ${money(financial.net_income_ytd)}`}
        />
        <StatCard label="رصيد النقدية" value={money(financial.cash_balance)} />
        <StatCard
          label="مستحق على العملاء"
          value={money(receivables.outstanding)}
          tone={receivables.overdue > 0 ? 'warning' : 'default'}
          hint={
            receivables.overdue > 0
              ? `متأخر: ${money(receivables.overdue)} — ${num(receivables.overdue_count)} فاتورة`
              : 'مفيش متأخرات'
          }
        />
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <Card title="اتجاه المبيعات — آخر 12 شهر" className="lg:col-span-2">
          {data.sales_trend.every((point) => point.total === 0) ? (
            <EmptyState message="لسه مفيش فواتير صادرة." />
          ) : (
            <div className="h-72" dir="ltr">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data.sales_trend}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" vertical={false} />
                  <XAxis dataKey="label" tick={{ fontSize: 11 }} tickLine={false} />
                  <YAxis
                    tick={{ fontSize: 11 }}
                    tickLine={false}
                    axisLine={false}
                    width={70}
                  />
                  <Tooltip
                    formatter={(value) => [money(Number(value)), 'المبيعات']}
                    labelStyle={{ direction: 'rtl' }}
                  />
                  <Bar dataKey="total" fill="oklch(0.62 0.13 165)" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          )}
        </Card>

        <Card title="أعلى العملاء (من بداية السنة)">
          {data.top_customers.length === 0 ? (
            <EmptyState message="لسه مفيش بيانات." />
          ) : (
            <ul className="space-y-3">
              {data.top_customers.map((customer) => (
                <li
                  key={customer.customer_id}
                  className="flex items-center justify-between gap-3"
                >
                  <div className="min-w-0">
                    <p className="truncate text-sm font-medium">
                      {customer.customer_name ?? '—'}
                    </p>
                    <p className="text-xs text-ink-400">
                      {num(customer.invoices)} فاتورة
                    </p>
                  </div>
                  <span className="num shrink-0 text-sm font-semibold">
                    {money(customer.revenue)}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card title="الذمم">
          <Table headers={['البند', 'القيمة']}>
            <tr>
              <td className="px-3 py-2">مستحق على العملاء</td>
              <td className="num px-3 py-2 font-medium">
                {money(receivables.outstanding)}
              </td>
            </tr>
            <tr>
              <td className="px-3 py-2">منه متأخر</td>
              <td className="num px-3 py-2 font-medium text-amber-600">
                {money(receivables.overdue)}
              </td>
            </tr>
            <tr>
              <td className="px-3 py-2">مستحق للموردين</td>
              <td className="num px-3 py-2 font-medium">
                {money(payables.outstanding)}
              </td>
            </tr>
            <tr>
              <td className="px-3 py-2">فواتير موردين متأخرة</td>
              <td className="num px-3 py-2 font-medium">
                {num(payables.overdue_count)}
              </td>
            </tr>
          </Table>
        </Card>

        <Card title="التشغيل">
          <div className="grid grid-cols-2 gap-4 text-sm">
            <Metric label="مشاريع نشطة" value={operations.active_projects} />
            <Metric label="أوامر شراء مفتوحة" value={operations.open_purchase_orders} />
            <Metric label="فواتير مسودة" value={operations.draft_invoices} />
            <Metric label="تذاكر مفتوحة" value={operations.open_tickets} />
            <Metric label="موظفون نشطون" value={operations.active_employees} />
            <div>
              <p className="text-xs text-ink-400">قيمة أوامر الشراء</p>
              <p className="num mt-1 font-semibold">
                {money(operations.open_purchase_orders_value)}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  )
}

function Metric({ label, value }: { label: string; value: number }) {
  return (
    <div>
      <p className="text-xs text-ink-400">{label}</p>
      <p className="num mt-1 font-semibold">{num(value)}</p>
    </div>
  )
}
