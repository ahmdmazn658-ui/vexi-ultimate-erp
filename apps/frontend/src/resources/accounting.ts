import { accountType, activeOptions, options } from './options'
import type { Resource } from './types'

export const accounts: Resource = {
  slug: 'accounts',
  title: 'شجرة الحسابات',
  subtitle: 'الحسابات اللي كل القيود بتترحّل عليها. الحسابات الأساسية موجودة من الـ seed.',
  endpoint: '/accounting/chart-of-accounts',
  create: true,
  edit: true,
  remove: true,
  filters: [{ name: 'type', label: 'النوع', options: accountType }],
  columns: [
    { key: 'account_code', label: 'الكود', format: 'code' },
    { key: 'account_name', label: 'اسم الحساب' },
    { key: 'account_name_ar', label: 'الاسم بالعربي' },
    { key: 'account_type', label: 'النوع', format: 'badge' },
    { key: 'is_active', label: 'الحالة', format: 'bool' },
  ],
  fields: [
    { name: 'account_code', label: 'كود الحساب', required: true, only: 'create' },
    { name: 'account_name', label: 'اسم الحساب', required: true },
    { name: 'account_name_ar', label: 'الاسم بالعربي' },
    {
      name: 'account_type',
      label: 'نوع الحساب',
      kind: 'select',
      options: accountType,
      required: true,
      only: 'create',
    },
    {
      name: 'parent_id',
      label: 'الحساب الأب',
      kind: 'ref',
      ref: 'accounts',
      only: 'create',
      help: 'سيبه فاضي لو الحساب رئيسي.',
    },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
    { name: 'description', label: 'وصف', kind: 'textarea', span: 2 },
  ],
  note: 'الكود والنوع مش بيتغيّروا بعد الإنشاء — القيود المُرحّلة معتمدة عليهم في التقارير.',
}

export const journalEntries: Resource = {
  slug: 'journal-entries',
  title: 'القيود اليومية',
  subtitle: 'القيود اليدوية. أي قيد لازم يكون متوازن قبل الترحيل.',
  endpoint: '/accounting/journal-entries',
  create: true,
  remove: true,
  refreshes: ['accounts'],
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({ draft: 'مسودة', posted: 'مُرحّل', reversed: 'معكوس' }),
    },
  ],
  columns: [
    { key: 'entry_number', label: 'رقم القيد', format: 'code' },
    { key: 'entry_date', label: 'التاريخ', format: 'date' },
    { key: 'description', label: 'البيان' },
    { key: 'project.name', label: 'المشروع' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'entry_number', label: 'رقم القيد', required: true },
    { name: 'entry_date', label: 'تاريخ القيد', kind: 'date', required: true },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects' },
    { name: 'reference', label: 'المرجع' },
    { name: 'description', label: 'البيان', kind: 'textarea', span: 2 },
  ],
  items: {
    name: 'lines',
    label: 'سطور القيد',
    addLabel: 'أضف سطر',
    minRows: 2,
    note: 'لازم سطرين على الأقل، ومجموع المدين = مجموع الدائن.',
    fields: [
      { name: 'account_id', label: 'الحساب', kind: 'ref', ref: 'accounts', required: true },
      { name: 'debit', label: 'مدين', kind: 'number', step: '0.01', defaultValue: 0 },
      { name: 'credit', label: 'دائن', kind: 'number', step: '0.01', defaultValue: 0 },
      { name: 'memo', label: 'بيان السطر' },
    ],
  },
  actions: [
    {
      label: 'ترحيل',
      path: (row) => `/accounting/journal-entries/${row.id}/post`,
      when: (row) => row.status === 'draft',
      confirm: 'الترحيل بيخلّي القيد جزء من دفتر الأستاذ ومش هينفع يتعدّل. تأكيد؟',
    },
  ],
  note: 'القيد المُرحّل بيدخل التقارير فورًا. لو تاريخه في فترة مقفلة هيترفض.',
}
