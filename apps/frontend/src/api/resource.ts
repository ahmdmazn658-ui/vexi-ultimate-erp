import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import type { Paginated } from './types'

/**
 * طبقة CRUD عامة.
 *
 * كل موديولات النظام بتتكلم بنفس اللغة: `GET` بترجّع paginator، `POST` بتنشئ،
 * `PUT /{id}` بتعدّل، `DELETE /{id}` بتحذف، وأي عملية إضافية بتبقى `POST` على
 * مسار فرعي. فبدل ما نكرر نفس الـ hooks لكل موديول، الملف ده بيغطيهم كلهم
 * والتعريف بيتحدد من `src/resources`.
 */

export type Row = Record<string, unknown>

/** أي عملية على مورد بتلغي كاش المورد نفسه + التقارير واللوحة (لأن أغلبها ليها أثر مالي). */
const GLOBAL_KEYS = ['dashboard', 'reports']

export function useResourceList(
  key: string,
  endpoint: string,
  params: Record<string, unknown> = {},
  options: { enabled?: boolean } = {},
) {
  return useQuery({
    queryKey: ['resource', key, params],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Row>>(endpoint, { params })
      return data
    },
    enabled: options.enabled ?? true,
  })
}

function useInvalidate(key: string, extra: string[] = []) {
  const queryClient = useQueryClient()

  return () => {
    void queryClient.invalidateQueries({ queryKey: ['resource', key] })
    for (const other of [...extra, ...GLOBAL_KEYS]) {
      void queryClient.invalidateQueries({ queryKey: ['resource', other] })
      void queryClient.invalidateQueries({ queryKey: [other] })
    }
  }
}

export function useResourceCreate(key: string, endpoint: string, extra: string[] = []) {
  const invalidate = useInvalidate(key, extra)

  return useMutation({
    mutationFn: async (payload: Row | FormData) => {
      const { data } = await api.post<Row>(endpoint, payload)
      return data
    },
    onSuccess: invalidate,
  })
}

export function useResourceUpdate(key: string, endpoint: string, extra: string[] = []) {
  const invalidate = useInvalidate(key, extra)

  return useMutation({
    mutationFn: async ({ id, payload }: { id: number; payload: Row }) => {
      const { data } = await api.put<Row>(`${endpoint}/${id}`, payload)
      return data
    },
    onSuccess: invalidate,
  })
}

export function useResourceDelete(key: string, endpoint: string, extra: string[] = []) {
  const invalidate = useInvalidate(key, extra)

  return useMutation({
    mutationFn: async (id: number) => {
      await api.delete(`${endpoint}/${id}`)
    },
    onSuccess: invalidate,
  })
}

/** عمليات الحالة: ترحيل قيد، اعتماد فاتورة، تعيين موظف، إقفال تذكرة... */
export function useResourceAction(key: string, extra: string[] = []) {
  const invalidate = useInvalidate(key, extra)

  return useMutation({
    mutationFn: async ({ path, payload }: { path: string; payload?: Row }) => {
      const { data } = await api.post<Row>(path, payload ?? {})
      return data
    },
    onSuccess: invalidate,
  })
}

// ── قوائم الاختيار ────────────────────────────────────────────
/**
 * الحقول اللي بتشاور على كيان تاني (عميل، مشروع، حساب...) بتقرا من هنا.
 * كل مصدر بيتحمّل مرة واحدة وبيتكاش، لأن القوايم دي بتتغير نادرًا.
 */
export const refSources = {
  customers: { endpoint: '/sales/customers', label: (r: Row) => `${r.name as string}` },
  suppliers: { endpoint: '/procurement/suppliers', label: (r: Row) => `${r.name as string}` },
  products: {
    endpoint: '/inventory/products',
    label: (r: Row) => `${r.sku as string} — ${r.name as string}`,
  },
  projects: { endpoint: '/projects', label: (r: Row) => `${r.name as string}` },
  warehouses: {
    endpoint: '/inventory/warehouses',
    label: (r: Row) => `${r.code as string} — ${r.name as string}`,
  },
  accounts: {
    endpoint: '/accounting/chart-of-accounts',
    label: (r: Row) => `${r.account_code as string} — ${r.account_name as string}`,
    params: { flat: 1 },
  },
  users: { endpoint: '/core/users', label: (r: Row) => `${r.name as string}` },
  employees: {
    endpoint: '/hr/employees',
    label: (r: Row) => `${r.employee_code as string} — ${r.full_name as string}`,
  },
  jobOpenings: { endpoint: '/recruitment/job-openings', label: (r: Row) => `${r.title as string}` },
  bankAccounts: {
    endpoint: '/banking/accounts',
    label: (r: Row) => `${r.bank_name as string} — ${r.account_name as string}`,
  },
  boms: { endpoint: '/manufacturing/bom', label: (r: Row) => `${r.name as string}` },
  purchaseOrders: {
    endpoint: '/procurement/purchase-orders',
    label: (r: Row) => `${r.po_number as string}`,
  },
  salesOrders: { endpoint: '/sales/orders', label: (r: Row) => `${r.order_number as string}` },
  leads: { endpoint: '/crm/leads', label: (r: Row) => `${r.name as string}` },
  hotelRoomTypes: { endpoint: '/hotel/room-types', label: (r: Row) => `${r.name_ar as string}` },
  hotelGuests: { endpoint: '/hotel/guests', label: (r: Row) => `${r.full_name as string}` },
  fleetDrivers: { endpoint: '/fleet/drivers', label: (r: Row) => `${r.full_name as string}` },
  fleetVehicles: {
    endpoint: '/fleet/vehicles',
    label: (r: Row) => `${r.plate_number as string} — ${(r.make as string) ?? ''}`,
  },
  posRegisterSessions: {
    endpoint: '/retail/register-sessions',
    label: (r: Row) => `#${r.id as number} — ${r.register_name as string} (${r.status as string})`,
    params: { status: 'open' },
  },
} as const

export type RefKey = keyof typeof refSources

export interface RefOption {
  value: number
  label: string
  row: Row
}

export function useRefOptions(ref: RefKey | undefined) {
  const source = ref ? refSources[ref] : undefined

  return useQuery({
    queryKey: ['ref', ref],
    enabled: Boolean(source),
    staleTime: 5 * 60_000,
    queryFn: async (): Promise<RefOption[]> => {
      if (!source) return []

      const extra = 'params' in source ? source.params : {}
      const { data } = await api.get<Paginated<Row>>(source.endpoint, {
        params: { per_page: 200, ...extra },
      })

      return data.data.map((row) => ({
        value: row.id as number,
        label: source.label(row),
        row,
      }))
    },
  })
}
