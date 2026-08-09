/** أنواع مطابقة لاستجابات Laravel API (apps/backend/routes/api.php). */

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface Customer {
  id: number
  name: string
  customer_code: string
}

export interface Supplier {
  id: number
  name: string
  supplier_code: string
}

export interface Product {
  id: number
  sku: string
  name: string
  unit: string
  sale_price: string
}

export interface InvoiceItemInput {
  product_id?: number
  item_name: string
  quantity: number
  unit_price: number
}

export interface CreateInvoicePayload {
  customer_id: number
  due_date?: string
  vat_rate?: number
  notes?: string
  items: InvoiceItemInput[]
}

export interface AccountingPeriod {
  id: number
  name: string
  fiscal_year: number
  period_number: number
  start_date: string
  end_date: string
  status: 'open' | 'closed'
  closed_at: string | null
  closed_by?: { id: number; name: string } | null
}

export interface YearEndClosingResult {
  message: string
  net_income: number
}

export type PaymentStatus = 'unpaid' | 'partial' | 'paid'

export interface Invoice {
  id: number
  invoice_number: string
  customer_id: number
  invoice_date: string | null
  due_date: string | null
  subtotal: string
  vat_amount: string
  total_amount: string
  paid_amount: string
  balance_due: number
  payment_status: PaymentStatus
  status: 'draft' | 'issued' | 'paid' | 'cancelled'
  customer?: Customer
}

export interface SupplierBill {
  id: number
  bill_number: string
  supplier_id: number
  bill_date: string | null
  due_date: string | null
  subtotal: string
  vat_amount: string
  total_amount: string
  paid_amount: string
  balance_due: number
  payment_status: PaymentStatus
  status: 'draft' | 'approved' | 'paid' | 'cancelled'
  supplier?: Supplier
}

export interface Payment {
  id: number
  payment_number: string
  type: 'receipt' | 'payment'
  payment_date: string
  amount: string
  allocated_amount: string
  unallocated_amount: number
  method: string
  reference: string | null
  status: 'draft' | 'posted' | 'cancelled'
  customer?: Customer | null
  supplier?: Supplier | null
  bank_account?: { id: number; bank_name: string; account_name: string } | null
}

export interface CreatePaymentPayload {
  type: 'receipt' | 'payment'
  customer_id?: number
  supplier_id?: number
  bank_account_id?: number
  payment_date: string
  amount: number
  method?: string
  reference?: string
  allocations?: { target_id: number; amount: number }[]
}

export interface DashboardSummary {
  generated_at: string
  financial: {
    revenue_mtd: number
    expenses_mtd: number
    net_income_mtd: number
    revenue_ytd: number
    expenses_ytd: number
    net_income_ytd: number
    cash_balance: number
  }
  receivables: { outstanding: number; overdue: number; overdue_count: number }
  payables: { outstanding: number; overdue_count: number }
  sales_trend: { month: string; label: string; total: number; invoices: number }[]
  top_customers: {
    customer_id: number
    customer_name: string | null
    revenue: number
    invoices: number
  }[]
  operations: {
    active_projects: number
    open_purchase_orders: number
    open_purchase_orders_value: number
    draft_invoices: number
    open_tickets: number
    active_employees: number
  }
}

export interface TrialBalanceRow {
  account_id: number
  account_code: string
  account_name: string
  account_name_ar: string | null
  account_type: string
  total_debit: number
  total_credit: number
  balance_debit: number
  balance_credit: number
}

export interface TrialBalance {
  period: { from: string | null; to: string | null }
  rows: TrialBalanceRow[]
  totals: { debit: number; credit: number; is_balanced: boolean }
}

export interface StatementLine {
  account_id: number
  account_code: string
  account_name: string
  account_name_ar: string | null
  amount: number
}

export interface IncomeStatement {
  period: { from: string; to: string }
  revenue: { lines: StatementLine[]; total: number }
  expenses: { lines: StatementLine[]; total: number }
  net_income: number
  margin_percent: number | null
}

export interface BalanceSheet {
  as_of: string
  assets: { lines: StatementLine[]; total: number }
  liabilities: { lines: StatementLine[]; total: number }
  equity: { lines: StatementLine[]; retained_earnings: number; total: number }
  check: {
    assets: number
    liabilities_plus_equity: number
    is_balanced: boolean
  }
}

export type AgingBucket = 'current' | '1_30' | '31_60' | '61_90' | 'over_90'

export interface AgingReport {
  as_of: string
  buckets: Record<AgingBucket, number>
  total: number
  rows: {
    id: number
    number: string
    party: string | null
    due_date: string | null
    total_amount: number
    balance: number
    days_overdue: number
    bucket: AgingBucket
  }[]
}
