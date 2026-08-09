import { activeOptions, options } from './options'
import type { Field, ItemsConfig, Resource } from './types'

/** بنود مشتركة بين طلبات البيع وفواتير الموردين وأوامر الشراء. */
const lineTotal = (row: Record<string, unknown>) =>
  Number(row.quantity ?? 0) * Number(row.unit_price ?? 0)

const productLine: Field[] = [
  { name: 'product_id', label: 'المنتج', kind: 'ref', ref: 'products' },
  { name: 'item_name', label: 'الوصف', required: true },
  { name: 'quantity', label: 'الكمية', kind: 'number', step: '0.01', required: true, defaultValue: 1 },
  { name: 'unit_price', label: 'سعر الوحدة', kind: 'number', step: '0.01', required: true },
]

const itemsBlock = (fields: Field[]): ItemsConfig => ({
  name: 'items',
  label: 'البنود',
  addLabel: 'أضف بند',
  fields,
  computed: { label: 'إجمالي السطر', of: lineTotal },
})

// ── المبيعات ──────────────────────────────────────────────────
export const customers: Resource = {
  slug: 'customers',
  title: 'العملاء',
  endpoint: '/sales/customers',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    {
      name: 'customer_type',
      label: 'النوع',
      options: options({ individual: 'فرد', company: 'شركة' }),
    },
  ],
  columns: [
    { key: 'customer_code', label: 'الكود', format: 'code' },
    { key: 'name', label: 'الاسم' },
    { key: 'contact_person', label: 'مسؤول التواصل' },
    { key: 'phone', label: 'الهاتف', format: 'code' },
    { key: 'email', label: 'البريد' },
    { key: 'customer_type', label: 'النوع', format: 'badge' },
    { key: 'credit_limit', label: 'حد الائتمان', format: 'money' },
  ],
  fields: [
    { name: 'customer_code', label: 'كود العميل', required: true, only: 'create' },
    { name: 'name', label: 'الاسم', required: true },
    {
      name: 'customer_type',
      label: 'النوع',
      kind: 'select',
      options: options({ individual: 'فرد', company: 'شركة' }),
      required: true,
      only: 'create',
    },
    { name: 'contact_person', label: 'مسؤول التواصل' },
    { name: 'phone', label: 'الهاتف' },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email' },
    { name: 'credit_limit', label: 'حد الائتمان', kind: 'number', step: '0.01' },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
    { name: 'address', label: 'العنوان', kind: 'textarea', span: 2 },
  ],
}

export const salesOrders: Resource = {
  slug: 'sales-orders',
  title: 'طلبات البيع',
  subtitle: 'التأكيد بيخصم الكمية من المستودع ويسجّل حركة مخزون.',
  endpoint: '/sales/orders',
  create: true,
  edit: true,
  remove: true,
  refreshes: ['products', 'stock-movements'],
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        draft: 'مسودة',
        confirmed: 'مؤكد',
        delivered: 'مُسلَّم',
        invoiced: 'مفوتر',
        cancelled: 'ملغي',
      }),
    },
  ],
  columns: [
    { key: 'order_number', label: 'رقم الطلب', format: 'code' },
    { key: 'customer.name', label: 'العميل' },
    { key: 'project.name', label: 'المشروع' },
    { key: 'order_date', label: 'التاريخ', format: 'date' },
    { key: 'delivery_date', label: 'التسليم', format: 'date' },
    { key: 'total_amount', label: 'الإجمالي', format: 'money' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'order_number', label: 'رقم الطلب', required: true, only: 'create' },
    { name: 'customer_id', label: 'العميل', kind: 'ref', ref: 'customers', required: true, only: 'create' },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects', only: 'create' },
    { name: 'order_date', label: 'تاريخ الطلب', kind: 'date', required: true, only: 'create' },
    { name: 'delivery_date', label: 'تاريخ التسليم', kind: 'date' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({
        draft: 'مسودة',
        confirmed: 'مؤكد',
        delivered: 'مُسلَّم',
        invoiced: 'مفوتر',
        cancelled: 'ملغي',
      }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  items: itemsBlock(productLine),
  actions: [
    {
      label: 'تأكيد',
      path: (row) => `/sales/orders/${row.id}/confirm`,
      when: (row) => row.status === 'draft',
      fields: [
        {
          name: 'warehouse_id',
          label: 'المستودع اللي هيتخصم منه',
          kind: 'ref',
          ref: 'warehouses',
          required: true,
        },
      ],
    },
  ],
  note: 'التأكيد بيفشل لو الكمية المتاحة من أي منتج أقل من المطلوب.',
}

// ── المشتريات ─────────────────────────────────────────────────
export const suppliers: Resource = {
  slug: 'suppliers',
  title: 'الموردين',
  endpoint: '/procurement/suppliers',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    {
      name: 'category',
      label: 'التصنيف',
      options: options({
        materials: 'مواد',
        equipment: 'معدات',
        subcontractor: 'مقاول باطن',
        services: 'خدمات',
        other: 'أخرى',
      }),
    },
  ],
  columns: [
    { key: 'supplier_code', label: 'الكود', format: 'code' },
    { key: 'name', label: 'الاسم' },
    { key: 'category', label: 'التصنيف', format: 'badge' },
    { key: 'contact_person', label: 'مسؤول التواصل' },
    { key: 'phone', label: 'الهاتف', format: 'code' },
    { key: 'rating', label: 'التقييم', format: 'number' },
  ],
  fields: [
    { name: 'supplier_code', label: 'كود المورد', required: true, only: 'create' },
    { name: 'name', label: 'الاسم', required: true },
    {
      name: 'category',
      label: 'التصنيف',
      kind: 'select',
      required: true,
      only: 'create',
      options: options({
        materials: 'مواد',
        equipment: 'معدات',
        subcontractor: 'مقاول باطن',
        services: 'خدمات',
        other: 'أخرى',
      }),
    },
    { name: 'contact_person', label: 'مسؤول التواصل' },
    { name: 'phone', label: 'الهاتف' },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email' },
    { name: 'rating', label: 'التقييم (0–5)', kind: 'number', step: '0.1', min: 0, max: 5 },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
    { name: 'address', label: 'العنوان', kind: 'textarea', span: 2 },
  ],
}

export const purchaseOrders: Resource = {
  slug: 'purchase-orders',
  title: 'أوامر الشراء',
  endpoint: '/procurement/purchase-orders',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        draft: 'مسودة',
        submitted: 'مُقدَّم',
        approved: 'معتمد',
        received: 'مستلم',
        cancelled: 'ملغي',
      }),
    },
  ],
  columns: [
    { key: 'po_number', label: 'رقم الأمر', format: 'code' },
    { key: 'supplier.name', label: 'المورد' },
    { key: 'project.name', label: 'المشروع' },
    { key: 'order_date', label: 'التاريخ', format: 'date' },
    { key: 'total_amount', label: 'الإجمالي', format: 'money' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'po_number', label: 'رقم أمر الشراء', required: true, only: 'create' },
    { name: 'supplier_id', label: 'المورد', kind: 'ref', ref: 'suppliers', required: true, only: 'create' },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects', only: 'create' },
    { name: 'order_date', label: 'تاريخ الأمر', kind: 'date', required: true, only: 'create' },
    { name: 'expected_delivery_date', label: 'التسليم المتوقع', kind: 'date' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({
        draft: 'مسودة',
        submitted: 'مُقدَّم',
        approved: 'معتمد',
        received: 'مستلم',
        cancelled: 'ملغي',
      }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  items: itemsBlock([
    { name: 'item_name', label: 'الوصف', required: true },
    { name: 'unit', label: 'الوحدة' },
    { name: 'quantity', label: 'الكمية', kind: 'number', step: '0.01', required: true, defaultValue: 1 },
    { name: 'unit_price', label: 'سعر الوحدة', kind: 'number', step: '0.01', required: true },
  ]),
  note: 'أمر الشراء لوحده مالوش أثر محاسبي — القيد بيتولّد لما فاتورة المورد تتعتمد.',
}

export const supplierBills: Resource = {
  slug: 'supplier-bills',
  title: 'فواتير الموردين',
  subtitle: 'الاعتماد بيولّد قيد: مدين المصروف + ضريبة المدخلات، دائن الذمم الدائنة.',
  endpoint: '/procurement/supplier-bills',
  create: true,
  edit: true,
  remove: true,
  refreshes: ['payments'],
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        draft: 'مسودة',
        approved: 'معتمدة',
        paid: 'مسددة',
        cancelled: 'ملغاة',
      }),
    },
  ],
  columns: [
    { key: 'bill_number', label: 'رقم الفاتورة', format: 'code' },
    { key: 'supplier.name', label: 'المورد' },
    { key: 'bill_date', label: 'التاريخ', format: 'date' },
    { key: 'due_date', label: 'الاستحقاق', format: 'date' },
    { key: 'total_amount', label: 'الإجمالي', format: 'money' },
    { key: 'balance_due', label: 'المتبقي', format: 'money' },
    { key: 'status', label: 'الحالة', format: 'badge' },
    { key: 'payment_status', label: 'السداد', format: 'badge' },
  ],
  fields: [
    { name: 'supplier_id', label: 'المورد', kind: 'ref', ref: 'suppliers', required: true, only: 'create' },
    { name: 'supplier_invoice_no', label: 'رقم فاتورة المورد' },
    { name: 'bill_date', label: 'تاريخ الفاتورة', kind: 'date', required: true, only: 'create' },
    { name: 'due_date', label: 'تاريخ الاستحقاق', kind: 'date' },
    { name: 'vat_rate', label: 'نسبة الضريبة %', kind: 'number', step: '0.01', defaultValue: 15 },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects' },
    {
      name: 'expense_account_id',
      label: 'حساب المصروف',
      kind: 'ref',
      ref: 'accounts',
      help: 'سيبه فاضي عشان يستخدم حساب المصروفات العمومية (5900).',
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  items: itemsBlock([
    { name: 'product_id', label: 'المنتج', kind: 'ref', ref: 'products' },
    { name: 'item_name', label: 'الوصف', required: true },
    { name: 'unit', label: 'الوحدة' },
    { name: 'quantity', label: 'الكمية', kind: 'number', step: '0.01', required: true, defaultValue: 1 },
    { name: 'unit_price', label: 'سعر الوحدة', kind: 'number', step: '0.01', required: true },
  ]),
  actions: [
    {
      label: 'اعتماد',
      path: (row) => `/procurement/supplier-bills/${row.id}/approve`,
      when: (row) => row.status === 'draft',
      confirm: 'الاعتماد بيرحّل القيد المحاسبي ومش هينفع تعدّل الفاتورة بعده. تأكيد؟',
    },
  ],
  note: 'التعديل والحذف متاحين للفواتير المسودة بس — الفاتورة المعتمدة ليها قيد في دفتر الأستاذ.',
}
