import { activeOptions, options } from './options'
import type { Resource } from './types'

// ── المخزون ───────────────────────────────────────────────────
export const warehouses: Resource = {
  slug: 'warehouses',
  title: 'المستودعات',
  endpoint: '/inventory/warehouses',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  columns: [
    { key: 'code', label: 'الكود', format: 'code' },
    { key: 'name', label: 'الاسم' },
    { key: 'location', label: 'الموقع' },
    { key: 'is_active', label: 'الحالة', format: 'bool' },
  ],
  fields: [
    { name: 'code', label: 'كود المستودع', required: true, only: 'create' },
    { name: 'name', label: 'الاسم', required: true },
    { name: 'location', label: 'الموقع' },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
  ],
}

export const products: Resource = {
  slug: 'products',
  title: 'المنتجات والأصناف',
  endpoint: '/inventory/products',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [{ name: 'low_stock', label: 'المخزون', options: options({ '1': 'تحت حد الطلب' }) }],
  columns: [
    { key: 'sku', label: 'SKU', format: 'code' },
    { key: 'name', label: 'الاسم' },
    { key: 'category', label: 'التصنيف' },
    { key: 'unit', label: 'الوحدة' },
    { key: 'cost_price', label: 'التكلفة', format: 'money' },
    { key: 'sale_price', label: 'سعر البيع', format: 'money' },
    { key: 'quantity_on_hand', label: 'المتاح', format: 'number' },
    { key: 'reorder_level', label: 'حد الطلب', format: 'number' },
  ],
  fields: [
    { name: 'sku', label: 'SKU', required: true, only: 'create' },
    { name: 'name', label: 'الاسم', required: true },
    { name: 'category', label: 'التصنيف' },
    { name: 'unit', label: 'الوحدة', defaultValue: 'piece' },
    { name: 'cost_price', label: 'سعر التكلفة', kind: 'number', step: '0.01' },
    { name: 'sale_price', label: 'سعر البيع', kind: 'number', step: '0.01' },
    { name: 'reorder_level', label: 'حد إعادة الطلب', kind: 'number', step: '0.01' },
    {
      name: 'quantity_on_hand',
      label: 'الرصيد الافتتاحي',
      kind: 'number',
      step: '0.01',
      only: 'create',
      help: 'بعد كده الرصيد بيتغيّر من حركات المخزون بس.',
    },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
  ],
}

export const stockMovements: Resource = {
  slug: 'stock-movements',
  title: 'حركات المخزون',
  subtitle: 'كل حركة بتعدّل رصيد المنتج فورًا. الحركات مبتتعدّلش ومبتتحذفش.',
  endpoint: '/inventory/stock-movements',
  create: true,
  refreshes: ['products'],
  filters: [
    {
      name: 'type',
      label: 'النوع',
      options: options({ in: 'وارد', out: 'صادر', adjustment: 'تسوية' }),
    },
  ],
  columns: [
    { key: 'created_at', label: 'التاريخ', format: 'date' },
    { key: 'product.sku', label: 'SKU', format: 'code' },
    { key: 'product.name', label: 'المنتج' },
    { key: 'warehouse.name', label: 'المستودع' },
    { key: 'type', label: 'النوع', format: 'badge' },
    { key: 'quantity', label: 'الكمية', format: 'number' },
    { key: 'notes', label: 'ملاحظات' },
  ],
  fields: [
    { name: 'product_id', label: 'المنتج', kind: 'ref', ref: 'products', required: true },
    { name: 'warehouse_id', label: 'المستودع', kind: 'ref', ref: 'warehouses', required: true },
    {
      name: 'type',
      label: 'نوع الحركة',
      kind: 'select',
      required: true,
      options: options({ in: 'وارد', out: 'صادر', adjustment: 'تسوية' }),
    },
    { name: 'quantity', label: 'الكمية', kind: 'number', step: '0.01', required: true },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}

// ── التصنيع ───────────────────────────────────────────────────
export const boms: Resource = {
  slug: 'bom',
  title: 'قوائم المواد (BOM)',
  subtitle: 'مكوّنات كل منتج مُصنَّع — بتتخصم من المخزون عند بدء أمر الإنتاج.',
  endpoint: '/manufacturing/bom',
  create: true,
  edit: true,
  remove: true,
  columns: [
    { key: 'name', label: 'الاسم' },
    { key: 'product.sku', label: 'SKU المنتج', format: 'code' },
    { key: 'product.name', label: 'المنتج' },
    { key: 'version', label: 'الإصدار' },
    { key: 'is_active', label: 'الحالة', format: 'bool' },
  ],
  fields: [
    { name: 'product_id', label: 'المنتج النهائي', kind: 'ref', ref: 'products', required: true, only: 'create' },
    { name: 'name', label: 'اسم القائمة', required: true },
    { name: 'version', label: 'الإصدار', defaultValue: '1.0' },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  items: {
    name: 'items',
    label: 'المكوّنات',
    addLabel: 'أضف مكوّن',
    note: 'المكوّن لازم يكون منتج مختلف عن المنتج النهائي.',
    fields: [
      {
        name: 'component_product_id',
        label: 'المكوّن',
        kind: 'ref',
        ref: 'products',
        required: true,
      },
      {
        name: 'quantity',
        label: 'الكمية لكل وحدة',
        kind: 'number',
        step: '0.0001',
        required: true,
        defaultValue: 1,
      },
    ],
  },
}

export const productionOrders: Resource = {
  slug: 'production-orders',
  title: 'أوامر الإنتاج',
  endpoint: '/manufacturing/production-orders',
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
        in_progress: 'جاري التنفيذ',
        completed: 'مكتمل',
        cancelled: 'ملغي',
      }),
    },
  ],
  columns: [
    { key: 'order_number', label: 'رقم الأمر', format: 'code' },
    { key: 'product.name', label: 'المنتج' },
    { key: 'warehouse.name', label: 'المستودع' },
    { key: 'quantity_planned', label: 'المخطط', format: 'number' },
    { key: 'quantity_produced', label: 'المنتَج', format: 'number' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'order_number', label: 'رقم الأمر', required: true, only: 'create' },
    { name: 'product_id', label: 'المنتج', kind: 'ref', ref: 'products', required: true, only: 'create' },
    { name: 'bill_of_material_id', label: 'قائمة المواد', kind: 'ref', ref: 'boms', only: 'create' },
    { name: 'warehouse_id', label: 'المستودع', kind: 'ref', ref: 'warehouses', required: true, only: 'create' },
    { name: 'quantity_planned', label: 'الكمية المخططة', kind: 'number', step: '0.01', required: true },
    { name: 'planned_start_date', label: 'بداية مخططة', kind: 'date' },
    { name: 'planned_end_date', label: 'نهاية مخططة', kind: 'date' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'بدء',
      path: (row) => `/manufacturing/production-orders/${row.id}/start`,
      when: (row) => row.status === 'draft',
      confirm: 'البدء بيخصم مكوّنات قائمة المواد من المخزون. تأكيد؟',
    },
    {
      label: 'إنهاء',
      path: (row) => `/manufacturing/production-orders/${row.id}/complete`,
      when: (row) => row.status === 'in_progress',
      fields: [
        {
          name: 'quantity_produced',
          label: 'الكمية المنتَجة فعليًا',
          kind: 'number',
          step: '0.01',
          help: 'سيبها فاضية عشان تاخد الكمية المخططة.',
        },
      ],
    },
  ],
  note: 'البدء محتاج قائمة مواد محددة، والإنهاء بيضيف الكمية المنتَجة لرصيد المنتج.',
}
