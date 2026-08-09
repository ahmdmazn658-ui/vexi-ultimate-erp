import { options } from './options'
import type { Resource } from './types'

/**
 * موديول الأسطول والنقل — مركبات، سائقين، صيانة، وقود بشاشات CRUD عامة.
 * شاشة "الرحلات" هنا بتغطي الإنشاء والإلغاء بس (زي أي مورد عادي)؛ عمليات
 * "بدء الرحلة" و"إقفال الرحلة" (اللي بتحدّث عداد العربية تلقائيًا) معرّفة
 * كـ actions على الصف، لأنها مش تعديل حر للحقول.
 */

const vehicleTypeOptions = options({
  car: 'سيارة',
  truck: 'شاحنة',
  van: 'فان',
  bus: 'باص',
  heavy_equipment: 'معدات ثقيلة',
  motorcycle: 'دراجة نارية',
  other: 'أخرى',
})

const vehicleStatusOptions = options({
  active: 'نشطة',
  under_maintenance: 'تحت الصيانة',
  out_of_service: 'خارج الخدمة',
  sold: 'مُباعة',
  disposed: 'مُستبعدة',
})

export const drivers: Resource = {
  slug: 'fleet-drivers',
  title: 'السائقين',
  endpoint: '/fleet/drivers',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({ active: 'نشط', suspended: 'موقوف', inactive: 'غير نشط' }),
    },
  ],
  columns: [
    { key: 'full_name', label: 'الاسم' },
    { key: 'phone', label: 'الهاتف' },
    { key: 'license_number', label: 'رقم الرخصة', format: 'code' },
    { key: 'license_type', label: 'نوع الرخصة', format: 'badge' },
    { key: 'license_expiry_date', label: 'انتهاء الرخصة', format: 'date' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'full_name', label: 'الاسم الكامل', required: true },
    { name: 'employee_id', label: 'الموظف المرتبط (لو موجود)', kind: 'ref', ref: 'employees' },
    { name: 'phone', label: 'الهاتف' },
    { name: 'license_number', label: 'رقم الرخصة' },
    {
      name: 'license_type',
      label: 'نوع الرخصة',
      kind: 'select',
      options: options({
        private: 'خاصة',
        heavy: 'ثقيلة',
        public_transport: 'نقل عام',
        motorcycle: 'دراجة نارية',
      }),
    },
    { name: 'license_expiry_date', label: 'تاريخ انتهاء الرخصة', kind: 'date' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({ active: 'نشط', suspended: 'موقوف', inactive: 'غير نشط' }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}

export const vehicles: Resource = {
  slug: 'fleet-vehicles',
  title: 'المركبات',
  subtitle: 'العداد بيتحدّث تلقائيًا من إقفال الرحلات وتسجيل الصيانة والوقود بعداد أعلى.',
  endpoint: '/fleet/vehicles',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  filters: [
    { name: 'status', label: 'الحالة', options: vehicleStatusOptions },
    { name: 'vehicle_type', label: 'النوع', options: vehicleTypeOptions },
  ],
  columns: [
    { key: 'plate_number', label: 'رقم اللوحة', format: 'code' },
    { key: 'vehicle_type', label: 'النوع', format: 'badge' },
    { key: 'make', label: 'الماركة' },
    { key: 'model', label: 'الموديل' },
    { key: 'odometer_km', label: 'العداد (كم)', format: 'number' },
    { key: 'assignedDriver.full_name', label: 'السائق المسؤول' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'plate_number', label: 'رقم اللوحة', required: true, only: 'create' },
    { name: 'vehicle_type', label: 'النوع', kind: 'select', options: vehicleTypeOptions, required: true },
    { name: 'make', label: 'الماركة' },
    { name: 'model', label: 'الموديل' },
    { name: 'year', label: 'سنة الصنع', kind: 'number', min: 1980, max: 2030 },
    {
      name: 'fuel_type',
      label: 'نوع الوقود',
      kind: 'select',
      options: options({ petrol: 'بنزين', diesel: 'ديزل', electric: 'كهرباء', hybrid: 'هجين' }),
    },
    {
      name: 'ownership',
      label: 'الملكية',
      kind: 'select',
      options: options({ owned: 'مملوكة', leased: 'تمويل تأجيري', rented: 'مستأجرة' }),
    },
    { name: 'odometer_km', label: 'العداد الحالي (كم)', kind: 'number', min: 0 },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects' },
    { name: 'assigned_driver_id', label: 'السائق المسؤول', kind: 'ref', ref: 'fleetDrivers' },
    { name: 'status', label: 'الحالة', kind: 'select', options: vehicleStatusOptions, only: 'edit' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}

export const trips: Resource = {
  slug: 'fleet-trips',
  title: 'الرحلات',
  endpoint: '/fleet/trips',
  create: true,
  remove: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        scheduled: 'مجدولة',
        in_progress: 'جارية',
        completed: 'مكتملة',
        cancelled: 'ملغية',
      }),
    },
  ],
  columns: [
    { key: 'vehicle.plate_number', label: 'المركبة', format: 'code' },
    { key: 'driver.full_name', label: 'السائق' },
    { key: 'destination', label: 'الوجهة' },
    { key: 'start_at', label: 'بداية الرحلة', format: 'date' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'fleet_vehicle_id', label: 'المركبة', kind: 'ref', ref: 'fleetVehicles', required: true },
    { name: 'fleet_driver_id', label: 'السائق', kind: 'ref', ref: 'fleetDrivers', required: true },
    { name: 'project_id', label: 'المشروع', kind: 'ref', ref: 'projects' },
    { name: 'purpose', label: 'الغرض من الرحلة' },
    { name: 'origin', label: 'نقطة الانطلاق' },
    { name: 'destination', label: 'الوجهة' },
    { name: 'start_at', label: 'موعد البداية', kind: 'date', required: true },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'بدء الرحلة',
      variant: 'ghost',
      path: (row) => `/fleet/trips/${row.id}/start`,
      when: (row) => row.status === 'scheduled',
      confirm: 'هيتسجّل بداية الرحلة الآن بعداد المركبة الحالي. تأكيد؟',
    },
    {
      label: 'إقفال الرحلة',
      variant: 'primary',
      path: (row) => `/fleet/trips/${row.id}/complete`,
      when: (row) => row.status === 'in_progress',
      fields: [
        { name: 'end_odometer_km', label: 'عداد النهاية (كم)', kind: 'number', required: true, min: 0 },
      ],
    },
  ],
}

export const maintenanceRecords: Resource = {
  slug: 'fleet-maintenance-records',
  title: 'صيانة المركبات',
  endpoint: '/fleet/maintenance-records',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'maintenance_type',
      label: 'النوع',
      options: options({
        scheduled: 'دورية',
        repair: 'إصلاح',
        inspection: 'فحص',
        tire_change: 'تغيير إطارات',
        oil_change: 'تغيير زيت',
        other: 'أخرى',
      }),
    },
  ],
  columns: [
    { key: 'vehicle.plate_number', label: 'المركبة', format: 'code' },
    { key: 'maintenance_type', label: 'النوع', format: 'badge' },
    { key: 'service_date', label: 'تاريخ الصيانة', format: 'date' },
    { key: 'cost', label: 'التكلفة', format: 'money' },
    { key: 'next_due_date', label: 'الصيانة القادمة', format: 'date' },
  ],
  fields: [
    { name: 'fleet_vehicle_id', label: 'المركبة', kind: 'ref', ref: 'fleetVehicles', required: true, only: 'create' },
    {
      name: 'maintenance_type',
      label: 'نوع الصيانة',
      kind: 'select',
      required: true,
      options: options({
        scheduled: 'دورية',
        repair: 'إصلاح',
        inspection: 'فحص',
        tire_change: 'تغيير إطارات',
        oil_change: 'تغيير زيت',
        other: 'أخرى',
      }),
    },
    { name: 'service_date', label: 'تاريخ الصيانة', kind: 'date', required: true },
    { name: 'odometer_km', label: 'العداد وقت الصيانة (كم)', kind: 'number', min: 0 },
    { name: 'cost', label: 'التكلفة', kind: 'number', step: '0.01' },
    { name: 'vendor_name', label: 'الجهة المنفّذة' },
    { name: 'next_due_date', label: 'موعد الصيانة القادمة', kind: 'date' },
    { name: 'next_due_odometer_km', label: 'عداد الصيانة القادمة (كم)', kind: 'number', min: 0 },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}

const violationTypeOptions = options({
  speeding: 'تجاوز السرعة',
  parking: 'وقوف مخالف',
  red_light: 'تجاوز إشارة حمراء',
  no_permit: 'بدون تصريح',
  lane_violation: 'مخالفة مسار',
  seatbelt: 'حزام الأمان',
  phone_use: 'استخدام الهاتف',
  other: 'أخرى',
})

const violationStatusOptions = options({
  unpaid: 'غير مسددة',
  paid: 'مسددة',
  disputed: 'معترض عليها',
  waived: 'مُلغاة',
})

export const violations: Resource = {
  slug: 'fleet-violations',
  title: 'المخالفات المرورية',
  subtitle: 'القيد المحاسبي بيتولّد تلقائيًا لحظة تسجيل السداد (مصروف على الشركة أو ذمة على السائق).',
  endpoint: '/fleet/violations',
  create: true,
  edit: true,
  remove: true,
  filters: [
    { name: 'status', label: 'الحالة', options: violationStatusOptions },
    { name: 'violation_type', label: 'النوع', options: violationTypeOptions },
  ],
  columns: [
    { key: 'vehicle.plate_number', label: 'المركبة', format: 'code' },
    { key: 'driver.full_name', label: 'السائق' },
    { key: 'violation_type', label: 'النوع', format: 'badge' },
    { key: 'violation_date', label: 'التاريخ', format: 'date' },
    { key: 'amount', label: 'المبلغ', format: 'money' },
    { key: 'liability', label: 'المسؤولية', format: 'badge' },
    { key: 'status', label: 'الحالة', format: 'badge' },
  ],
  fields: [
    { name: 'fleet_vehicle_id', label: 'المركبة', kind: 'ref', ref: 'fleetVehicles', required: true, only: 'create' },
    { name: 'fleet_driver_id', label: 'السائق', kind: 'ref', ref: 'fleetDrivers' },
    { name: 'violation_number', label: 'رقم المخالفة' },
    { name: 'violation_type', label: 'نوع المخالفة', kind: 'select', options: violationTypeOptions },
    { name: 'violation_date', label: 'تاريخ المخالفة', kind: 'date', required: true },
    { name: 'location', label: 'الموقع' },
    { name: 'amount', label: 'المبلغ', kind: 'number', step: '0.01' },
    {
      name: 'liability',
      label: 'المسؤولية',
      kind: 'select',
      options: options({ company: 'الشركة', driver: 'السائق' }),
    },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
  actions: [
    {
      label: 'تسجيل السداد',
      variant: 'primary',
      path: (row) => `/fleet/violations/${row.id}/pay`,
      when: (row) => row.status === 'unpaid',
      confirm: 'هيتقيّد مصروف/ذمة بقيد محاسبي مُرحّل الآن. تأكيد السداد؟',
    },
  ],
}

export const fuelLogs: Resource = {
  slug: 'fleet-fuel-logs',
  title: 'سجلات الوقود',
  endpoint: '/fleet/fuel-logs',
  create: true,
  edit: true,
  remove: true,
  columns: [
    { key: 'vehicle.plate_number', label: 'المركبة', format: 'code' },
    { key: 'driver.full_name', label: 'السائق' },
    { key: 'log_date', label: 'التاريخ', format: 'date' },
    { key: 'liters', label: 'اللترات', format: 'number' },
    { key: 'cost', label: 'التكلفة', format: 'money' },
    { key: 'fuel_station', label: 'المحطة' },
  ],
  fields: [
    { name: 'fleet_vehicle_id', label: 'المركبة', kind: 'ref', ref: 'fleetVehicles', required: true, only: 'create' },
    { name: 'fleet_driver_id', label: 'السائق', kind: 'ref', ref: 'fleetDrivers' },
    { name: 'log_date', label: 'التاريخ', kind: 'date', required: true },
    { name: 'odometer_km', label: 'العداد وقت التعبئة (كم)', kind: 'number', min: 0 },
    { name: 'liters', label: 'اللترات', kind: 'number', step: '0.01' },
    { name: 'cost', label: 'التكلفة', kind: 'number', step: '0.01' },
    { name: 'fuel_station', label: 'محطة الوقود' },
  ],
}
