import { activeOptions, options } from './options'
import type { Resource } from './types'

export const bankAccounts: Resource = {
  slug: 'bank-accounts',
  title: 'الحسابات البنكية',
  subtitle: 'السندات بتسجّل حركة بنكية تلقائيًا على الحساب المختار.',
  endpoint: '/banking/accounts',
  create: true,
  edit: true,
  remove: true,
  columns: [
    { key: 'bank_name', label: 'البنك' },
    { key: 'account_name', label: 'اسم الحساب' },
    { key: 'account_number', label: 'رقم الحساب', format: 'code' },
    { key: 'iban', label: 'الآيبان', format: 'code' },
    { key: 'currency', label: 'العملة' },
    { key: 'account.account_code', label: 'الحساب المحاسبي', format: 'code' },
    { key: 'is_active', label: 'الحالة', format: 'bool' },
  ],
  fields: [
    { name: 'bank_name', label: 'اسم البنك', required: true },
    { name: 'account_name', label: 'اسم الحساب', required: true },
    { name: 'account_number', label: 'رقم الحساب', required: true, only: 'create' },
    { name: 'iban', label: 'الآيبان' },
    { name: 'currency', label: 'العملة', defaultValue: 'SAR', only: 'create' },
    {
      name: 'account_id',
      label: 'الحساب المحاسبي',
      kind: 'ref',
      ref: 'accounts',
      only: 'create',
      help: 'الحساب اللي حركات البنك دي بتترحّل عليه في دفتر الأستاذ.',
    },
    { name: 'opening_balance', label: 'الرصيد الافتتاحي', kind: 'number', step: '0.01', only: 'create' },
    { name: 'is_active', label: 'الحالة', kind: 'select', options: activeOptions, only: 'edit' },
  ],
}

export const bankTransactions: Resource = {
  slug: 'bank-transactions',
  title: 'الحركات البنكية',
  subtitle: 'حركات يدوية للتسوية. حركات السندات بتتسجّل هنا تلقائيًا.',
  endpoint: '/banking/transactions',
  create: true,
  remove: true,
  filters: [
    {
      name: 'type',
      label: 'النوع',
      options: options({ deposit: 'إيداع', withdrawal: 'سحب' }),
    },
  ],
  columns: [
    { key: 'transaction_date', label: 'التاريخ', format: 'date' },
    { key: 'type', label: 'النوع', format: 'badge' },
    { key: 'amount', label: 'المبلغ', format: 'money' },
    { key: 'reference', label: 'المرجع', format: 'code' },
    { key: 'description', label: 'البيان' },
    { key: 'is_reconciled', label: 'التسوية', format: 'bool' },
  ],
  fields: [
    { name: 'bank_account_id', label: 'الحساب البنكي', kind: 'ref', ref: 'bankAccounts', required: true },
    { name: 'transaction_date', label: 'تاريخ الحركة', kind: 'date', required: true },
    {
      name: 'type',
      label: 'النوع',
      kind: 'select',
      required: true,
      options: options({ deposit: 'إيداع', withdrawal: 'سحب' }),
    },
    { name: 'amount', label: 'المبلغ', kind: 'number', step: '0.01', required: true },
    { name: 'reference', label: 'المرجع' },
    { name: 'description', label: 'البيان', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'تسوية',
      variant: 'ghost',
      path: (row) => `/banking/transactions/${row.id}/reconcile`,
      when: (row) => !row.is_reconciled,
    },
  ],
}

export const budgets: Resource = {
  slug: 'budgets',
  title: 'الموازنات',
  subtitle: 'المقارنة بالفعلي بتتحسب من القيود المُرحّلة على نفس الحساب والفترة.',
  endpoint: '/budgeting/budgets',
  create: true,
  edit: true,
  remove: true,
  columns: [
    { key: 'name', label: 'الموازنة' },
    { key: 'period', label: 'الفترة', format: 'code' },
    { key: 'account.account_code', label: 'كود الحساب', format: 'code' },
    { key: 'account.account_name', label: 'الحساب' },
    { key: 'project.name', label: 'المشروع' },
    { key: 'period_start', label: 'من', format: 'date' },
    { key: 'period_end', label: 'إلى', format: 'date' },
    { key: 'budgeted_amount', label: 'المبلغ المخطط', format: 'money' },
  ],
  fields: [
    { name: 'name', label: 'اسم الموازنة', required: true },
    {
      name: 'period',
      label: 'الفترة',
      required: true,
      placeholder: '2026-Q3',
      only: 'create',
    },
    { name: 'account_id', label: 'الحساب', kind: 'ref', ref: 'accounts', required: true, only: 'create' },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects', only: 'create' },
    { name: 'period_start', label: 'بداية الفترة', kind: 'date', required: true },
    { name: 'period_end', label: 'نهاية الفترة', kind: 'date', required: true },
    { name: 'budgeted_amount', label: 'المبلغ المخطط', kind: 'number', step: '0.01', required: true },
  ],
}

export const documents: Resource = {
  slug: 'documents',
  title: 'المستندات',
  subtitle: 'الملفات محفوظة خارج المجلد العام، والتحميل بيمر على التحقق من الصلاحية.',
  endpoint: '/document-management/documents',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  multipart: true,
  columns: [
    { key: 'title', label: 'العنوان' },
    { key: 'original_name', label: 'اسم الملف' },
    { key: 'category', label: 'التصنيف' },
    { key: 'mime_type', label: 'النوع' },
    { key: 'uploader.name', label: 'رفعه' },
    { key: 'created_at', label: 'التاريخ', format: 'date' },
  ],
  fields: [
    { name: 'title', label: 'عنوان المستند', required: true },
    { name: 'category', label: 'التصنيف', placeholder: 'عقد، رخصة، مخطط…' },
    {
      name: 'file',
      label: 'الملف',
      kind: 'file',
      only: 'create',
      span: 2,
      help: 'الحد الأقصى 8 ميجابايت. الصيغ المدعومة: PDF، Office، صور، ZIP، DWG.',
    },
    {
      name: 'documentable_type',
      label: 'مرتبط بـ (اسم الموديل)',
      only: 'create',
      placeholder: 'App\\Models\\Project',
    },
    { name: 'documentable_id', label: 'رقم السجل المرتبط', kind: 'number', only: 'create' },
  ],
  note: 'الحذف بيمسح الملف من السيرفر كمان، مش السجل بس.',
}

export const users: Resource = {
  slug: 'users',
  title: 'المستخدمين',
  subtitle: 'الإضافة والتعديل والحذف متاحين لدور admin بس.',
  endpoint: '/core/users',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    {
      name: 'role',
      label: 'الدور',
      options: options({
        admin: 'مدير النظام',
        manager: 'مدير',
        accountant: 'محاسب',
        employee: 'موظف',
      }),
    },
  ],
  columns: [
    { key: 'name', label: 'الاسم' },
    { key: 'email', label: 'البريد' },
    { key: 'role', label: 'الدور', format: 'badge' },
    { key: 'created_at', label: 'الإنشاء', format: 'date' },
  ],
  fields: [
    { name: 'name', label: 'الاسم', required: true },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email', required: true },
    {
      name: 'password',
      label: 'كلمة المرور',
      kind: 'password',
      help: '٨ حروف على الأقل. عند التعديل، سيبها فاضية عشان تفضل زي ما هي.',
    },
    {
      name: 'role',
      label: 'الدور',
      kind: 'select',
      required: true,
      options: options({
        admin: 'مدير النظام',
        manager: 'مدير',
        accountant: 'محاسب',
        employee: 'موظف',
      }),
    },
  ],
  note: 'الأدوار بتتحكم في العمليات الحساسة: اعتماد فواتير الموردين، إلغاء السندات، وإقفال الفترات.',
}
