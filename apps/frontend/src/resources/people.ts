import { department, employmentType, options, priority } from './options'
import type { Resource } from './types'

// ── الموارد البشرية ───────────────────────────────────────────
export const employees: Resource = {
  slug: 'employees',
  title: 'الموظفين',
  endpoint: '/hr/employees',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    { name: 'department', label: 'القسم', options: department },
    {
      name: 'status',
      label: 'الحالة',
      options: options({ active: 'نشط', on_leave: 'في إجازة', terminated: 'منتهي' }),
    },
  ],
  columns: [
    { key: 'employee_code', label: 'الكود', format: 'code' },
    { key: 'full_name', label: 'الاسم' },
    { key: 'position', label: 'الوظيفة' },
    { key: 'department', label: 'القسم', format: 'badge' },
    { key: 'project.name', label: 'المشروع' },
    { key: 'basic_salary', label: 'الراتب الأساسي', format: 'money' },
    { key: 'hire_date', label: 'التعيين', format: 'date' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'employee_code', label: 'كود الموظف', required: true, only: 'create' },
    { name: 'full_name', label: 'الاسم الكامل', required: true, only: 'create' },
    { name: 'national_id', label: 'رقم الهوية', only: 'create' },
    { name: 'position', label: 'الوظيفة', required: true },
    { name: 'department', label: 'القسم', kind: 'select', options: department, required: true },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects' },
    { name: 'basic_salary', label: 'الراتب الأساسي', kind: 'number', step: '0.01', required: true },
    { name: 'hire_date', label: 'تاريخ التعيين', kind: 'date', required: true, only: 'create' },
    {
      name: 'employment_type',
      label: 'نوع التعاقد',
      kind: 'select',
      options: employmentType,
      required: true,
      only: 'create',
    },
    { name: 'phone', label: 'الهاتف' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({ active: 'نشط', on_leave: 'في إجازة', terminated: 'منتهي' }),
    },
    { name: 'termination_date', label: 'تاريخ انتهاء الخدمة', kind: 'date', only: 'edit' },
  ],
  note: 'الرواتب بتتحسب من الراتب الأساسي للموظفين النشطين وقت إنشاء دورة الرواتب.',
}

export const payrollRuns: Resource = {
  slug: 'payroll-runs',
  title: 'دورات الرواتب',
  subtitle: 'الترحيل بيولّد قيد: مدين مصروف الرواتب، دائن الرواتب المستحقة.',
  endpoint: '/payroll/runs',
  create: true,
  remove: true,
  filters: [
    { name: 'status', label: 'الحالة', options: options({ draft: 'مسودة', posted: 'مُرحّلة' }) },
  ],
  columns: [
    { key: 'period', label: 'الفترة', format: 'code' },
    { key: 'run_date', label: 'تاريخ الصرف', format: 'date' },
    { key: 'total_amount', label: 'الإجمالي', format: 'money' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    {
      name: 'period',
      label: 'الفترة',
      required: true,
      placeholder: '2026-08',
      help: 'صيغة السنة-الشهر. مينفعش تتكرر.',
    },
    { name: 'run_date', label: 'تاريخ الصرف', kind: 'date', required: true },
  ],
  actions: [
    {
      label: 'ترحيل',
      path: (row) => `/payroll/runs/${row.id}/post`,
      when: (row) => row.status === 'draft',
      confirm: 'الترحيل بيولّد قيد الرواتب في دفتر الأستاذ. تأكيد؟',
    },
  ],
  note: 'إنشاء الدورة بيولّد قسيمة لكل موظف نشط. الحذف متاح للمسودات بس.',
}

export const jobOpenings: Resource = {
  slug: 'job-openings',
  title: 'الوظائف الشاغرة',
  endpoint: '/recruitment/job-openings',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({ open: 'مفتوح', on_hold: 'متوقف مؤقتًا', closed: 'مقفل' }),
    },
  ],
  columns: [
    { key: 'title', label: 'المسمى الوظيفي' },
    { key: 'department', label: 'القسم' },
    { key: 'employment_type', label: 'نوع التعاقد', format: 'badge' },
    { key: 'status', label: 'الحالة', format: 'badge' },
    { key: 'created_at', label: 'تاريخ النشر', format: 'date' },
  ],
  fields: [
    { name: 'title', label: 'المسمى الوظيفي', required: true },
    { name: 'department', label: 'القسم' },
    {
      name: 'employment_type',
      label: 'نوع التعاقد',
      kind: 'select',
      only: 'create',
      options: options({ full_time: 'دوام كامل', part_time: 'دوام جزئي', contractor: 'متعاقد' }),
    },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects', only: 'create' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({ open: 'مفتوح', on_hold: 'متوقف مؤقتًا', closed: 'مقفل' }),
    },
    { name: 'description', label: 'الوصف الوظيفي', kind: 'textarea', span: 2 },
  ],
}

export const candidates: Resource = {
  slug: 'candidates',
  title: 'المتقدمين للوظائف',
  endpoint: '/recruitment/candidates',
  create: true,
  edit: true,
  remove: true,
  refreshes: ['employees'],
  filters: [
    {
      name: 'stage',
      label: 'المرحلة',
      options: options({
        applied: 'تقدّم',
        screening: 'فرز',
        interview: 'مقابلة',
        offer: 'عرض',
        hired: 'تم التعيين',
        rejected: 'مرفوض',
      }),
    },
  ],
  columns: [
    { key: 'full_name', label: 'الاسم' },
    { key: 'job_opening.title', label: 'الوظيفة' },
    { key: 'email', label: 'البريد' },
    { key: 'phone', label: 'الهاتف', format: 'code' },
    { key: 'stage', label: 'المرحلة', format: 'badge' },
  ],
  fields: [
    { name: 'job_opening_id', label: 'الوظيفة', kind: 'ref', ref: 'jobOpenings', required: true, only: 'create' },
    { name: 'full_name', label: 'الاسم الكامل', required: true, only: 'create' },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email', only: 'create' },
    { name: 'phone', label: 'الهاتف', only: 'create' },
    {
      name: 'stage',
      label: 'المرحلة',
      kind: 'select',
      only: 'edit',
      options: options({
        applied: 'تقدّم',
        screening: 'فرز',
        interview: 'مقابلة',
        offer: 'عرض',
        hired: 'تم التعيين',
        rejected: 'مرفوض',
      }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'تعيين',
      path: (row) => `/recruitment/candidates/${row.id}/hire`,
      when: (row) => row.stage !== 'hired' && row.stage !== 'rejected',
      fields: [
        { name: 'position', label: 'الوظيفة', required: true },
        { name: 'department', label: 'القسم', kind: 'select', options: department, required: true },
        { name: 'hire_date', label: 'تاريخ التعيين', kind: 'date', required: true },
        { name: 'basic_salary', label: 'الراتب الأساسي', kind: 'number', step: '0.01', required: true },
        {
          name: 'employee_code',
          label: 'كود الموظف',
          help: 'سيبه فاضي عشان يتولّد تلقائيًا.',
        },
      ],
    },
  ],
  note: 'التعيين بينشئ سجل موظف جديد ويربطه بالمتقدم.',
}

// ── CRM ───────────────────────────────────────────────────────
export const leads: Resource = {
  slug: 'leads',
  title: 'العملاء المحتملين',
  endpoint: '/crm/leads',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  refreshes: ['customers'],
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        new: 'جديد',
        contacted: 'تم التواصل',
        qualified: 'مؤهل',
        unqualified: 'غير مؤهل',
        converted: 'مُحوَّل',
      }),
    },
  ],
  columns: [
    { key: 'name', label: 'الاسم' },
    { key: 'company_name', label: 'الشركة' },
    { key: 'email', label: 'البريد' },
    { key: 'phone', label: 'الهاتف', format: 'code' },
    { key: 'source', label: 'المصدر' },
    { key: 'assigned_to.name', label: 'المسؤول' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'name', label: 'الاسم', required: true },
    { name: 'company_name', label: 'الشركة' },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email' },
    { name: 'phone', label: 'الهاتف' },
    { name: 'source', label: 'المصدر', placeholder: 'معرض، إحالة، الموقع…', only: 'create' },
    { name: 'assigned_to', label: 'المسؤول', kind: 'ref', ref: 'users' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      options: options({
        new: 'جديد',
        contacted: 'تم التواصل',
        qualified: 'مؤهل',
        unqualified: 'غير مؤهل',
      }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'تحويل لعميل',
      path: (row) => `/crm/leads/${row.id}/convert`,
      when: (row) => row.status !== 'converted',
      confirm: 'هيتعمل سجل عميل جديد من بيانات العميل المحتمل. تأكيد؟',
    },
  ],
}

export const opportunities: Resource = {
  slug: 'opportunities',
  title: 'الفرص البيعية',
  endpoint: '/crm/opportunities',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'stage',
      label: 'المرحلة',
      options: options({
        prospecting: 'استكشاف',
        qualification: 'تأهيل',
        proposal: 'عرض سعر',
        negotiation: 'تفاوض',
        won: 'مكسوبة',
        lost: 'خسرانة',
      }),
    },
  ],
  columns: [
    { key: 'name', label: 'الفرصة' },
    { key: 'customer.name', label: 'العميل' },
    { key: 'expected_amount', label: 'القيمة المتوقعة', format: 'money' },
    { key: 'probability', label: 'الاحتمالية', format: 'percent' },
    { key: 'expected_close_date', label: 'الإغلاق المتوقع', format: 'date' },
    { key: 'owner.name', label: 'المسؤول' },
    { key: 'stage', label: 'المرحلة', format: 'badge' },
  ],
  fields: [
    { name: 'name', label: 'اسم الفرصة', required: true },
    { name: 'customer_id', label: 'العميل', kind: 'ref', ref: 'customers', only: 'create' },
    { name: 'lead_id', label: 'العميل المحتمل', kind: 'ref', ref: 'leads', only: 'create' },
    { name: 'expected_amount', label: 'القيمة المتوقعة', kind: 'number', step: '0.01', required: true },
    { name: 'probability', label: 'الاحتمالية %', kind: 'number', min: 0, max: 100 },
    { name: 'expected_close_date', label: 'تاريخ الإغلاق المتوقع', kind: 'date' },
    { name: 'owner_id', label: 'المسؤول', kind: 'ref', ref: 'users' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'نقل المرحلة',
      variant: 'ghost',
      path: (row) => `/crm/opportunities/${row.id}/move-stage`,
      when: (row) => row.stage !== 'won' && row.stage !== 'lost',
      fields: [
        {
          name: 'stage',
          label: 'المرحلة الجديدة',
          kind: 'select',
          required: true,
          options: options({
            prospecting: 'استكشاف',
            qualification: 'تأهيل',
            proposal: 'عرض سعر',
            negotiation: 'تفاوض',
            won: 'مكسوبة',
            lost: 'خسرانة',
          }),
        },
      ],
    },
  ],
}

export const tickets: Resource = {
  slug: 'tickets',
  title: 'تذاكر الدعم',
  endpoint: '/helpdesk/tickets',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        open: 'مفتوح',
        in_progress: 'جاري العمل',
        resolved: 'تم الحل',
        closed: 'مقفل',
      }),
    },
    { name: 'priority', label: 'الأولوية', options: priority },
  ],
  columns: [
    { key: 'ticket_number', label: 'رقم التذكرة', format: 'code' },
    { key: 'subject', label: 'الموضوع' },
    { key: 'customer.name', label: 'العميل' },
    { key: 'assigned_to.name', label: 'المسؤول' },
    { key: 'priority', label: 'الأولوية', format: 'badge' },
    { key: 'status', label: 'الحالة', format: 'badge' },
    { key: 'created_at', label: 'الإنشاء', format: 'date' },
  ],
  fields: [
    { name: 'subject', label: 'الموضوع', required: true },
    { name: 'customer_id', label: 'العميل', kind: 'ref', ref: 'customers', only: 'create' },
    { name: 'assigned_to', label: 'المسؤول', kind: 'ref', ref: 'users' },
    { name: 'priority', label: 'الأولوية', kind: 'select', options: priority },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({
        open: 'مفتوح',
        in_progress: 'جاري العمل',
        resolved: 'تم الحل',
        closed: 'مقفل',
      }),
    },
    { name: 'description', label: 'الوصف', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'تم الحل',
      path: (row) => `/helpdesk/tickets/${row.id}/resolve`,
      when: (row) => row.status !== 'resolved' && row.status !== 'closed',
    },
    {
      label: 'إقفال',
      variant: 'ghost',
      path: (row) => `/helpdesk/tickets/${row.id}/close`,
      when: (row) => row.status !== 'closed',
    },
  ],
}
