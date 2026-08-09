import { useEffect, type ReactNode } from 'react'
import { X } from 'lucide-react'

export function Card({
  title,
  action,
  children,
  className = '',
}: {
  title?: string
  action?: ReactNode
  children: ReactNode
  className?: string
}) {
  return (
    <section
      className={`rounded-xl border border-slate-200 bg-white shadow-sm ${className}`}
    >
      {(title || action) && (
        <header className="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          {title && <h2 className="text-sm font-semibold text-ink-900">{title}</h2>}
          {action}
        </header>
      )}
      <div className="p-5">{children}</div>
    </section>
  )
}

export function StatCard({
  label,
  value,
  hint,
  tone = 'default',
}: {
  label: string
  value: string
  hint?: string
  tone?: 'default' | 'positive' | 'negative' | 'warning'
}) {
  const toneClass = {
    default: 'text-ink-900',
    positive: 'text-emerald-600',
    negative: 'text-rose-600',
    warning: 'text-amber-600',
  }[tone]

  return (
    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
      <p className="text-xs font-medium text-ink-400">{label}</p>
      <p className={`num mt-2 text-2xl font-bold ${toneClass}`}>{value}</p>
      {hint && <p className="mt-1 text-xs text-ink-400">{hint}</p>}
    </div>
  )
}

const badgeTones: Record<string, string> = {
  // أخضر — الحالة النهائية الناجحة
  paid: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  posted: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  active: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  completed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  passed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  hired: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  won: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  resolved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  sold: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  received: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  delivered: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  available: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  qualified: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  reconciled: 'bg-emerald-50 text-emerald-700 ring-emerald-200',

  // أزرق — حالة جارية أو مؤكدة
  issued: 'bg-blue-50 text-blue-700 ring-blue-200',
  confirmed: 'bg-blue-50 text-blue-700 ring-blue-200',
  in_progress: 'bg-blue-50 text-blue-700 ring-blue-200',
  submitted: 'bg-blue-50 text-blue-700 ring-blue-200',
  open: 'bg-blue-50 text-blue-700 ring-blue-200',
  invoiced: 'bg-blue-50 text-blue-700 ring-blue-200',
  interview: 'bg-blue-50 text-blue-700 ring-blue-200',
  negotiation: 'bg-blue-50 text-blue-700 ring-blue-200',
  rented: 'bg-blue-50 text-blue-700 ring-blue-200',
  deposit: 'bg-blue-50 text-blue-700 ring-blue-200',

  // كهرماني — محتاج انتباه
  partial: 'bg-amber-50 text-amber-700 ring-amber-200',
  pending: 'bg-amber-50 text-amber-700 ring-amber-200',
  on_hold: 'bg-amber-50 text-amber-700 ring-amber-200',
  on_leave: 'bg-amber-50 text-amber-700 ring-amber-200',
  needs_rework: 'bg-amber-50 text-amber-700 ring-amber-200',
  under_maintenance: 'bg-amber-50 text-amber-700 ring-amber-200',
  under_construction: 'bg-amber-50 text-amber-700 ring-amber-200',
  reserved: 'bg-amber-50 text-amber-700 ring-amber-200',
  offer: 'bg-amber-50 text-amber-700 ring-amber-200',
  high: 'bg-amber-50 text-amber-700 ring-amber-200',

  // أحمر — إلغاء أو فشل
  cancelled: 'bg-rose-50 text-rose-700 ring-rose-200',
  terminated: 'bg-rose-50 text-rose-700 ring-rose-200',
  failed: 'bg-rose-50 text-rose-700 ring-rose-200',
  rejected: 'bg-rose-50 text-rose-700 ring-rose-200',
  lost: 'bg-rose-50 text-rose-700 ring-rose-200',
  unqualified: 'bg-rose-50 text-rose-700 ring-rose-200',
  disposed: 'bg-rose-50 text-rose-700 ring-rose-200',
  urgent: 'bg-rose-50 text-rose-700 ring-rose-200',
  withdrawal: 'bg-rose-50 text-rose-700 ring-rose-200',
  out: 'bg-rose-50 text-rose-700 ring-rose-200',

  // رمادي — مسودة أو محايد
  draft: 'bg-slate-100 text-ink-600 ring-slate-200',
  unpaid: 'bg-slate-100 text-ink-600 ring-slate-200',
  closed: 'bg-slate-100 text-ink-600 ring-slate-200',
  planning: 'bg-slate-100 text-ink-600 ring-slate-200',
  new: 'bg-slate-100 text-ink-600 ring-slate-200',
}

const badgeLabels: Record<string, string> = {
  // عام
  draft: 'مسودة',
  issued: 'صادرة',
  paid: 'مسددة',
  partial: 'سداد جزئي',
  unpaid: 'غير مسددة',
  approved: 'معتمدة',
  posted: 'مُرحّل',
  cancelled: 'ملغاة',
  active: 'نشط',
  completed: 'مكتمل',
  pending: 'قيد الانتظار',
  open: 'مفتوح',
  closed: 'مقفل',
  submitted: 'مُقدَّم',
  confirmed: 'مؤكد',
  delivered: 'مُسلَّم',
  invoiced: 'مفوتر',
  received: 'مستلم',

  // مشاريع
  planning: 'تخطيط',
  in_progress: 'جاري التنفيذ',
  on_hold: 'متوقف مؤقتًا',
  construction: 'مقاولات',
  real_estate: 'عقاري',
  infrastructure: 'بنية تحتية',
  service: 'خدمي',
  other: 'أخرى',

  // عقود وعقارات
  main: 'عقد رئيسي',
  subcontract: 'عقد باطن',
  supply: 'توريد',
  consultancy: 'استشارات',
  lease: 'إيجار',
  terminated: 'مفسوخ',
  available: 'متاح',
  reserved: 'محجوز',
  sold: 'مُباع',
  rented: 'مؤجر',
  under_construction: 'تحت الإنشاء',
  residential: 'سكني',
  commercial: 'تجاري',
  land: 'أرض',
  industrial: 'صناعي',
  mixed_use: 'متعدد الاستخدام',

  // موارد بشرية
  on_leave: 'في إجازة',
  full_time: 'دوام كامل',
  part_time: 'دوام جزئي',
  contractor: 'متعاقد',
  daily_wage: 'أجر يومي',
  engineering: 'الهندسة',
  finance: 'المالية',
  procurement: 'المشتريات',
  hr: 'الموارد البشرية',
  operations: 'العمليات',
  management: 'الإدارة',
  applied: 'تقدّم',
  screening: 'فرز',
  interview: 'مقابلة',
  offer: 'عرض',
  hired: 'تم التعيين',
  rejected: 'مرفوض',

  // CRM
  new: 'جديد',
  contacted: 'تم التواصل',
  qualified: 'مؤهل',
  unqualified: 'غير مؤهل',
  converted: 'مُحوَّل',
  prospecting: 'استكشاف',
  qualification: 'تأهيل',
  proposal: 'عرض سعر',
  negotiation: 'تفاوض',
  won: 'مكسوبة',
  lost: 'خسرانة',

  // دعم فني
  resolved: 'تم الحل',
  low: 'منخفضة',
  medium: 'متوسطة',
  high: 'عالية',
  urgent: 'عاجلة',

  // أصول ومخزون وجودة
  under_maintenance: 'تحت الصيانة',
  disposed: 'مُستبعد',
  heavy_equipment: 'معدات ثقيلة',
  vehicle: 'مركبة',
  tool: 'عدة',
  building: 'مبنى',
  furniture: 'أثاث',
  it_equipment: 'أجهزة حاسب',
  straight_line: 'قسط ثابت',
  declining_balance: 'رصيد متناقص',
  passed: 'ناجح',
  failed: 'راسب',
  needs_rework: 'يحتاج إعادة',
  in: 'وارد',
  out: 'صادر',
  adjustment: 'تسوية',

  // بنوك وسندات
  deposit: 'إيداع',
  withdrawal: 'سحب',
  reconciled: 'مُسوّى',
  receipt: 'سند قبض',
  payment: 'سند صرف',
  bank_transfer: 'تحويل بنكي',
  cash: 'نقدي',
  cheque: 'شيك',
  card: 'بطاقة',

  // موردين وحسابات
  materials: 'مواد',
  equipment: 'معدات',
  services: 'خدمات',
  individual: 'فرد',
  company: 'شركة',
  asset: 'أصول',
  liability: 'التزامات',
  equity: 'حقوق ملكية',
  revenue: 'إيرادات',
  expense: 'مصروفات',

  // أدوار المستخدمين
  admin: 'مدير النظام',
  manager: 'مدير',
  accountant: 'محاسب',
  employee: 'موظف',
}

export function Badge({ value }: { value: string }) {
  const tone = badgeTones[value] ?? 'bg-slate-100 text-ink-600 ring-slate-200'
  return (
    <span
      className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${tone}`}
    >
      {badgeLabels[value] ?? value}
    </span>
  )
}

export function Button({
  children,
  onClick,
  type = 'button',
  variant = 'primary',
  disabled = false,
  size = 'md',
}: {
  children: ReactNode
  onClick?: () => void
  type?: 'button' | 'submit'
  variant?: 'primary' | 'ghost' | 'danger'
  disabled?: boolean
  size?: 'sm' | 'md'
}) {
  const variants = {
    primary: 'bg-brand-600 text-white hover:bg-brand-700 disabled:bg-brand-300',
    ghost:
      'bg-white text-ink-600 ring-1 ring-slate-200 hover:bg-slate-50 disabled:text-ink-400',
    danger: 'bg-rose-600 text-white hover:bg-rose-700 disabled:bg-rose-300',
  }[variant]

  const sizes = { sm: 'px-2.5 py-1 text-xs', md: 'px-4 py-2 text-sm' }[size]

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`rounded-lg font-medium transition-colors disabled:cursor-not-allowed ${variants} ${sizes}`}
    >
      {children}
    </button>
  )
}

export function Table({
  headers,
  children,
}: {
  headers: string[]
  children: ReactNode
}) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-right text-sm">
        <thead>
          <tr className="border-b border-slate-200">
            {headers.map((header) => (
              <th
                key={header}
                className="whitespace-nowrap px-3 py-2 text-xs font-semibold text-ink-400"
              >
                {header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100">{children}</tbody>
      </table>
    </div>
  )
}

export function EmptyState({ message }: { message: string }) {
  return <p className="py-8 text-center text-sm text-ink-400">{message}</p>
}

export function ErrorState({ message }: { message: string }) {
  return (
    <div className="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200">
      {message}
    </div>
  )
}

export function Loading() {
  return (
    <div className="flex items-center justify-center py-10">
      <div className="size-6 animate-spin rounded-full border-2 border-slate-200 border-t-brand-600" />
    </div>
  )
}

export function Field({
  label,
  children,
}: {
  label: string
  children: ReactNode
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-xs font-medium text-ink-600">{label}</span>
      {children}
    </label>
  )
}

export const inputClass =
  'w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100'

export const selectClass = `${inputClass} bg-white`

/** ترقيم الصفحات — Laravel بيرجّع current_page / last_page مع كل قائمة. */
export function Pagination({
  page,
  lastPage,
  total,
  onChange,
}: {
  page: number
  lastPage: number
  total: number
  onChange: (page: number) => void
}) {
  if (lastPage <= 1) {
    return (
      <p className="pt-3 text-xs text-ink-400">
        <span className="num">{total}</span> سجل
      </p>
    )
  }

  return (
    <div className="flex items-center justify-between pt-4">
      <p className="text-xs text-ink-400">
        صفحة <span className="num">{page}</span> من <span className="num">{lastPage}</span> ·{' '}
        <span className="num">{total}</span> سجل
      </p>

      <div className="flex gap-2">
        <Button
          size="sm"
          variant="ghost"
          disabled={page <= 1}
          onClick={() => onChange(page - 1)}
        >
          السابق
        </Button>
        <Button
          size="sm"
          variant="ghost"
          disabled={page >= lastPage}
          onClick={() => onChange(page + 1)}
        >
          التالي
        </Button>
      </div>
    </div>
  )
}

/** نافذة منبثقة للنماذج. الإغلاق بـ Escape أو الضغط على الخلفية. */
export function Modal({
  title,
  onClose,
  children,
  wide = false,
}: {
  title: string
  onClose: () => void
  children: ReactNode
  wide?: boolean
}) {
  useEffect(() => {
    function onKeyDown(event: KeyboardEvent) {
      if (event.key === 'Escape') onClose()
    }

    document.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [onClose])

  return (
    <div
      className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/40 p-4 sm:p-8"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) onClose()
      }}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-label={title}
        className={`w-full rounded-xl bg-white shadow-xl ${wide ? 'max-w-4xl' : 'max-w-2xl'}`}
      >
        <header className="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          <h2 className="text-sm font-semibold text-ink-900">{title}</h2>
          <button
            onClick={onClose}
            aria-label="إغلاق"
            className="rounded-lg p-1 text-ink-400 transition-colors hover:bg-slate-50 hover:text-ink-600"
          >
            <X className="size-4" />
          </button>
        </header>

        <div className="p-5">{children}</div>
      </div>
    </div>
  )
}

/** تأكيد قبل عملية لا رجعة فيها (حذف، إلغاء سند، إقفال سنة). */
export function ConfirmDialog({
  message,
  confirmLabel = 'تأكيد',
  onConfirm,
  onCancel,
  busy = false,
}: {
  message: string
  confirmLabel?: string
  onConfirm: () => void
  onCancel: () => void
  busy?: boolean
}) {
  return (
    <Modal title="تأكيد" onClose={onCancel}>
      <p className="text-sm text-ink-600">{message}</p>
      <div className="mt-5 flex justify-start gap-2">
        <Button variant="danger" onClick={onConfirm} disabled={busy}>
          {busy ? 'جارٍ التنفيذ…' : confirmLabel}
        </Button>
        <Button variant="ghost" onClick={onCancel} disabled={busy}>
          رجوع
        </Button>
      </div>
    </Modal>
  )
}
