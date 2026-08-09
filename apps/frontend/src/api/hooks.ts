import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import { setSession, type AuthUser } from '../lib/auth'
import type {
  AccountingPeriod,
  AgingReport,
  BalanceSheet,
  Customer,
  CreatePaymentPayload,
  DashboardSummary,
  IncomeStatement,
  Invoice,
  CreateInvoicePayload,
  Paginated,
  Payment,
  Product,
  Supplier,
  SupplierBill,
  TrialBalance,
  YearEndClosingResult,
} from './types'

// ── Auth ──────────────────────────────────────────────────────
export function useLogin() {
  return useMutation({
    mutationFn: async (credentials: { email: string; password: string }) => {
      const { data } = await api.post<{ token: string; user: AuthUser }>(
        '/auth/login',
        credentials,
      )
      return data
    },
    onSuccess: (data) => setSession(data.token, data.user),
  })
}

// ── Dashboard ─────────────────────────────────────────────────
export function useDashboard() {
  return useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => {
      const { data } = await api.get<DashboardSummary>('/dashboards/summary')
      return data
    },
  })
}

// ── Invoices ──────────────────────────────────────────────────
export function useInvoices(
  params: { status?: string; customer_id?: number } = {},
  options: { enabled?: boolean } = {},
) {
  return useQuery({
    queryKey: ['invoices', params],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Invoice>>('/e-invoicing/invoices', { params })
      return data
    },
    enabled: options.enabled ?? true,
  })
}

export function useCreateInvoice() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: CreateInvoicePayload) => {
      const { data } = await api.post<Invoice>('/e-invoicing/invoices', payload)
      return data
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['invoices'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}

export function useIssueInvoice() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await api.post<Invoice>(`/e-invoicing/invoices/${id}/issue`)
      return data
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['invoices'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}

// ── Supplier bills ────────────────────────────────────────────
export function useSupplierBills(
  params: { status?: string; supplier_id?: number } = {},
  options: { enabled?: boolean } = {},
) {
  return useQuery({
    queryKey: ['supplier-bills', params],
    queryFn: async () => {
      const { data } = await api.get<Paginated<SupplierBill>>(
        '/procurement/supplier-bills',
        { params },
      )
      return data
    },
    enabled: options.enabled ?? true,
  })
}

export function useApproveBill() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await api.post<SupplierBill>(
        `/procurement/supplier-bills/${id}/approve`,
      )
      return data
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['supplier-bills'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}

// ── Payments ──────────────────────────────────────────────────
export function usePayments(params: { type?: string } = {}) {
  return useQuery({
    queryKey: ['payments', params],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Payment>>('/finance/payments', { params })
      return data
    },
  })
}

export function useCreatePayment() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (payload: CreatePaymentPayload) => {
      const { data } = await api.post<Payment>('/finance/payments', payload)
      return data
    },
    onSuccess: () => {
      // السند بيأثر على الفواتير والتقارير واللوحة — نحدّثهم كلهم
      void queryClient.invalidateQueries({ queryKey: ['payments'] })
      void queryClient.invalidateQueries({ queryKey: ['invoices'] })
      void queryClient.invalidateQueries({ queryKey: ['supplier-bills'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
      void queryClient.invalidateQueries({ queryKey: ['reports'] })
    },
  })
}

export function useVoidPayment() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await api.post<Payment>(`/finance/payments/${id}/void`)
      return data
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['payments'] })
      void queryClient.invalidateQueries({ queryKey: ['invoices'] })
      void queryClient.invalidateQueries({ queryKey: ['supplier-bills'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
      void queryClient.invalidateQueries({ queryKey: ['reports'] })
    },
  })
}

// ── Parties ───────────────────────────────────────────────────
export function useCustomers() {
  return useQuery({
    queryKey: ['customers'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Customer>>('/sales/customers', {
        params: { per_page: 100 },
      })
      return data.data
    },
  })
}

export function useSuppliers() {
  return useQuery({
    queryKey: ['suppliers'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Supplier>>('/procurement/suppliers', {
        params: { per_page: 100 },
      })
      return data.data
    },
  })
}

export function useProducts() {
  return useQuery({
    queryKey: ['products'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Product>>('/inventory/products', {
        params: { per_page: 200 },
      })
      return data.data
    },
  })
}

// ── Accounting periods ────────────────────────────────────────
export function usePeriods(fiscalYear: number) {
  return useQuery({
    queryKey: ['periods', fiscalYear],
    queryFn: async () => {
      const { data } = await api.get<{ data: AccountingPeriod[] }>(
        '/accounting/periods',
        { params: { fiscal_year: fiscalYear } },
      )
      return data.data
    },
  })
}

export function useGeneratePeriods() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (fiscalYear: number) => {
      const { data } = await api.post('/accounting/periods/generate', {
        fiscal_year: fiscalYear,
      })
      return data
    },
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['periods'] }),
  })
}

export function useClosePeriod() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, action }: { id: number; action: 'close' | 'reopen' }) => {
      const { data } = await api.post<AccountingPeriod>(
        `/accounting/periods/${id}/${action}`,
      )
      return data
    },
    onSuccess: () => void queryClient.invalidateQueries({ queryKey: ['periods'] }),
  })
}

export function useYearEndClosing() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: async (fiscalYear: number) => {
      const { data } = await api.post<YearEndClosingResult>(
        '/accounting/periods/year-end-closing',
        { fiscal_year: fiscalYear },
      )
      return data
    },
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['periods'] })
      void queryClient.invalidateQueries({ queryKey: ['reports'] })
      void queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}

// ── Reports ───────────────────────────────────────────────────
export function useTrialBalance(period: { from: string; to: string }) {
  return useQuery({
    queryKey: ['reports', 'trial-balance', period],
    queryFn: async () => {
      const { data } = await api.get<TrialBalance>('/reports/trial-balance', {
        params: period,
      })
      return data
    },
  })
}

export function useIncomeStatement(period: { from: string; to: string }) {
  return useQuery({
    queryKey: ['reports', 'income-statement', period],
    queryFn: async () => {
      const { data } = await api.get<IncomeStatement>('/reports/income-statement', {
        params: period,
      })
      return data
    },
  })
}

export function useBalanceSheet(asOf: string) {
  return useQuery({
    queryKey: ['reports', 'balance-sheet', asOf],
    queryFn: async () => {
      const { data } = await api.get<BalanceSheet>('/reports/balance-sheet', {
        params: { as_of: asOf },
      })
      return data
    },
  })
}

export function useAging(kind: 'ar' | 'ap', asOf: string) {
  return useQuery({
    queryKey: ['reports', `${kind}-aging`, asOf],
    queryFn: async () => {
      const { data } = await api.get<AgingReport>(`/reports/${kind}-aging`, {
        params: { as_of: asOf },
      })
      return data
    },
  })
}
