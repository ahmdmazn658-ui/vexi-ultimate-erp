import { options } from './options'
import type { Field, ItemsConfig, Resource } from './types'

/**
 * موديول نقطة البيع (Retail POS). شاشة "الشيفتات" بتغطي فتح/قفل الكاشير،
 * وشاشة "المبيعات" بتستخدم نفس آلية البنود المتعددة المستخدمة في طلبات
 * البيع (`items`) عشان تبني سلة بيع كاملة. الـ checkout واحد وذرّي:
 * صرف مخزون + فاتورة مدفوعة + قيد محاسبي مُرحّل (إيراد + تكلفة بضاعة مباعة)
 * كلهم بيحصلوا مع بعض لحظة إنشاء البيع.
 */

const lineTotal = (row: Record<string, unknown>) =>
  Number(row.quantity ?? 0) * Number(row.unit_price ?? 0)

const saleItemFields: Field[] = [
  { name: 'product_id', label: 'المنتج', kind: 'ref', ref: 'products', required: true },
  { name: 'quantity', label: 'الكمية', kind: 'number', step: '0.01', required: true, defaultValue: 1 },
  { name: 'unit_price', label: 'سعر الوحدة (افتراضيًا سعر المنتج)', kind: 'number', step: '0.01' },
]

const saleItems: ItemsConfig = {
  name: 'items',
  label: 'سلة البيع',
  addLabel: 'أضف صنف',
  fields: saleItemFields,
  minRows: 1,
  computed: { label: 'إجمالي السطر', of: lineTotal },
  note: 'الضريبة بتُحسب تلقائيًا بنسبة 15%، والقيد المحاسبي (إيراد + تكلفة بضاعة مباعة) بيتولّد فور الحفظ.',
}

export const registerSessions: Resource = {
  slug: 'retail-register-sessions',
  title: 'شيفتات الكاشير',
  subtitle: 'قفل الشيفت بيحسب فرق النقدية تلقائيًا من إجمالي مبيعات الكاش المسجّلة عليه.',
  endpoint: '/retail/register-sessions',
  create: true,
  searchable: true,
  filters: [
    { name: 'status', label: 'الحالة', options: options({ open: 'مفتوح', closed: 'مقفول' }) },
  ],
  columns: [
    { key: 'register_name', label: 'الماكينة' },
    { key: 'warehouse.name', label: 'المخزن' },
    { key: 'opening_cash', label: 'النقدية الافتتاحية', format: 'money' },
    { key: 'closing_cash', label: 'النقدية الختامية', format: 'money' },
    { key: 'cash_difference', label: 'الفرق', format: 'money' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'register_name', label: 'اسم/رقم الماكينة', required: true },
    { name: 'warehouse_id', label: 'المخزن', kind: 'ref', ref: 'warehouses', required: true },
    { name: 'opening_cash', label: 'النقدية الافتتاحية', kind: 'number', step: '0.01' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'قفل الشيفت',
      variant: 'primary',
      path: (row) => `/retail/register-sessions/${row.id}/close`,
      when: (row) => row.status === 'open',
      fields: [
        { name: 'closing_cash', label: 'النقدية الفعلية في الدرج', kind: 'number', step: '0.01', required: true },
      ],
    },
  ],
}

export const posSales: Resource = {
  slug: 'retail-pos-sales',
  title: 'مبيعات نقطة البيع',
  subtitle: 'كل بيع هنا وراه فاتورة مدفوعة وقيد محاسبي مُرحّل تلقائيًا (إيراد + تكلفة بضاعة مباعة).',
  endpoint: '/retail/sales',
  create: true,
  refreshes: ['products', 'stock-movements', 'retail-register-sessions'],
  note: 'صرف المخزون نهائي لحظة الحفظ — مفيش تعديل على بيع بعد إتمامه، سجّل بيع جديد بعلامة سالبة لو محتاج ترجيع.',
  columns: [
    { key: 'sale_number', label: 'رقم البيع', format: 'code' },
    { key: 'customer.name', label: 'العميل' },
    { key: 'payment_method', label: 'طريقة الدفع', format: 'badge' },
    { key: 'subtotal', label: 'الصافي', format: 'money' },
    { key: 'vat_amount', label: 'الضريبة', format: 'money' },
    { key: 'total_amount', label: 'الإجمالي', format: 'money' },
    { key: 'created_at', label: 'التاريخ', format: 'date' },
  ],
  fields: [
    {
      name: 'pos_register_session_id',
      label: 'الشيفت',
      kind: 'ref',
      ref: 'posRegisterSessions',
      required: true,
      only: 'create',
    },
    { name: 'customer_id', label: 'العميل (اختياري)', kind: 'ref', ref: 'customers', only: 'create' },
    {
      name: 'payment_method',
      label: 'طريقة الدفع',
      kind: 'select',
      options: options({ cash: 'كاش', card: 'شبكة', mixed: 'مختلط' }),
      only: 'create',
    },
  ],
  items: saleItems,
}
