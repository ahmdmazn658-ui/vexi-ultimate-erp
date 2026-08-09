import type { Option } from './types'

/** بيحوّل خريطة قيمة→تسمية لقائمة اختيارات. القيم مطابقة لـ enum في المايجريشن. */
export function options(map: Record<string, string>): Option[] {
  return Object.entries(map).map(([value, label]) => ({ value, label }))
}

export const activeOptions = options({ '1': 'نشط', '0': 'موقوف' })

export const projectStatus = options({
  planning: 'تخطيط',
  in_progress: 'جاري التنفيذ',
  on_hold: 'متوقف مؤقتًا',
  completed: 'مكتمل',
  cancelled: 'ملغي',
})

export const projectType = options({
  construction: 'مقاولات',
  real_estate: 'عقاري',
  infrastructure: 'بنية تحتية',
  service: 'خدمي',
  other: 'أخرى',
})

export const department = options({
  engineering: 'الهندسة',
  finance: 'المالية',
  procurement: 'المشتريات',
  hr: 'الموارد البشرية',
  operations: 'العمليات',
  management: 'الإدارة',
  other: 'أخرى',
})

export const employmentType = options({
  full_time: 'دوام كامل',
  part_time: 'دوام جزئي',
  contractor: 'متعاقد',
  daily_wage: 'أجر يومي',
})

export const accountType = options({
  asset: 'أصول',
  liability: 'التزامات',
  equity: 'حقوق ملكية',
  revenue: 'إيرادات',
  expense: 'مصروفات',
})

export const priority = options({
  low: 'منخفضة',
  medium: 'متوسطة',
  high: 'عالية',
  urgent: 'عاجلة',
})
