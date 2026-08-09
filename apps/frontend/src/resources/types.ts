import type { RefKey, Row } from '../api/resource'

export interface Option {
  value: string
  label: string
}

export type FieldKind =
  | 'text'
  | 'textarea'
  | 'number'
  | 'date'
  | 'select'
  | 'ref'
  | 'email'
  | 'password'
  | 'file'

export interface Field {
  name: string
  label: string
  kind?: FieldKind
  required?: boolean
  /** لحقول `select` */
  options?: Option[]
  /** لحقول `ref` — بتقرا القائمة من `refSources` */
  ref?: RefKey
  /** الحقول اللي بتتحدد عند الإنشاء بس (زي الأكواد) أو عند التعديل بس (زي الحالة) */
  only?: 'create' | 'edit'
  placeholder?: string
  step?: string
  min?: number
  max?: number
  defaultValue?: string | number
  /** عرض الحقل في الشبكة — الافتراضي عمود واحد */
  span?: 1 | 2
  help?: string
}

export type ColumnFormat =
  | 'text'
  | 'code'
  | 'money'
  | 'number'
  | 'date'
  | 'badge'
  | 'percent'
  | 'bool'

export interface Column {
  /** بيقبل مسار متداخل: `customer.name` */
  key: string
  label: string
  format?: ColumnFormat
}

export interface RowAction {
  label: string
  /** المسار الكامل للـ endpoint — مثال: `(row) => `/sales/orders/${row.id}/confirm`` */
  path: (row: Row) => string
  /** الزرار بيظهر بس لو رجّعت true */
  when?: (row: Row) => boolean
  variant?: 'primary' | 'ghost' | 'danger'
  confirm?: string
  /** لو العملية محتاجة مدخلات (زي اختيار مستودع عند تأكيد طلب البيع) */
  fields?: Field[]
}

export interface ItemsConfig {
  /** اسم الحقل في الـ payload: `items` أو `lines` */
  name: string
  label: string
  addLabel: string
  fields: Field[]
  minRows?: number
  /** أعمدة بتتحسب وتتعرض في آخر كل سطر */
  computed?: { label: string; of: (row: Row) => number }
  /** تحذير/شرح تحت الجدول */
  note?: string
}

export interface Resource {
  slug: string
  title: string
  subtitle?: string
  endpoint: string
  columns: Column[]
  fields?: Field[]
  filters?: { name: string; label: string; options: Option[] }[]
  searchable?: boolean
  create?: boolean
  edit?: boolean
  remove?: boolean
  actions?: RowAction[]
  items?: ItemsConfig
  /** ملاحظة بتتعرض تحت الجدول — بتشرح الأثر المحاسبي للعملية */
  note?: string
  /** مفاتيح كاش إضافية تتحدّث بعد أي تعديل */
  refreshes?: string[]
  /** الإنشاء بيستخدم multipart بدل JSON (شاشة المستندات) */
  multipart?: boolean
}
export type ResourceConfig = Resource;
