import { options } from './options'
import type { Resource } from './types'

/**
 * موديول الفندقة — الشاشات دي بتغطي الإعداد الأساسي (أنواع الغرف، الغرف،
 * النزلاء). شاشة "الحجوزات" مقصودة إنها متبنيش هنا لأنها محتاجة واجهة خاصة
 * (اختيار أكتر من غرفة + عرض الإتاحة بالتاريخ) — الـ backend جاهز
 * (`/hotel/reservations`, `/hotel/rooms/availability`) وناقص بس شاشة مخصصة
 * زي `InvoiceForm.tsx`.
 */

export const roomTypes: Resource = {
  slug: 'hotel-room-types',
  title: 'أنواع الغرف',
  endpoint: '/hotel/room-types',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  columns: [
    { key: 'name', label: 'الاسم' },
    { key: 'name_ar', label: 'الاسم بالعربي' },
    { key: 'max_occupancy', label: 'أقصى إشغال', format: 'number' },
    { key: 'base_rate', label: 'السعر الأساسي/الليلة', format: 'money' },
    { key: 'is_active', label: 'نشط', format: 'bool' },
  ],
  fields: [
    { name: 'name', label: 'الاسم (إنجليزي)', required: true },
    { name: 'name_ar', label: 'الاسم بالعربي', required: true },
    { name: 'max_occupancy', label: 'أقصى إشغال', kind: 'number', min: 1, max: 20, required: true },
    { name: 'base_rate', label: 'السعر الأساسي لليلة', kind: 'number', step: '0.01', required: true },
    { name: 'is_active', label: 'نشط', kind: 'select', options: options({ '1': 'نشط', '0': 'موقوف' }) },
    { name: 'description', label: 'الوصف', kind: 'textarea', span: 2 },
  ],
}

export const rooms: Resource = {
  slug: 'hotel-rooms',
  title: 'الغرف',
  subtitle: 'حالة الغرفة بتتحدّث تلقائيًا مع عمليات تسجيل الوصول/المغادرة والتدبير المنزلي.',
  endpoint: '/hotel/rooms',
  create: true,
  edit: true,
  remove: true,
  filters: [
    {
      name: 'status',
      label: 'الحالة',
      options: options({
        vacant_clean: 'فاضية ونظيفة',
        vacant_dirty: 'فاضية وتحتاج تنظيف',
        occupied_clean: 'مشغولة ونظيفة',
        occupied_dirty: 'مشغولة وتحتاج تنظيف',
        out_of_order: 'خارج الخدمة (صيانة)',
        out_of_service: 'خارج الخدمة (إداري)',
      }),
    },
  ],
  columns: [
    { key: 'room_number', label: 'رقم الغرفة', format: 'code' },
    { key: 'roomType.name', label: 'النوع' },
    { key: 'floor', label: 'الدور' },
    { key: 'status', label: 'الحالة', format: 'badge' },
    { key: 'is_active', label: 'نشط', format: 'bool' },
  ],
  fields: [
    { name: 'hotel_room_type_id', label: 'نوع الغرفة', kind: 'ref', ref: 'hotelRoomTypes', required: true, only: 'create' },
    { name: 'room_number', label: 'رقم الغرفة', required: true, only: 'create' },
    { name: 'floor', label: 'الدور' },
    {
      name: 'status',
      label: 'الحالة',
      kind: 'select',
      only: 'edit',
      options: options({
        vacant_clean: 'فاضية ونظيفة',
        vacant_dirty: 'فاضية وتحتاج تنظيف',
        occupied_clean: 'مشغولة ونظيفة',
        occupied_dirty: 'مشغولة وتحتاج تنظيف',
        out_of_order: 'خارج الخدمة (صيانة)',
        out_of_service: 'خارج الخدمة (إداري)',
      }),
    },
    { name: 'is_active', label: 'نشط', kind: 'select', options: options({ '1': 'نشط', '0': 'موقوف' }) },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}

export const guests: Resource = {
  slug: 'hotel-guests',
  title: 'النزلاء',
  endpoint: '/hotel/guests',
  create: true,
  edit: true,
  remove: true,
  searchable: true,
  columns: [
    { key: 'full_name', label: 'الاسم' },
    { key: 'phone', label: 'الهاتف' },
    { key: 'email', label: 'البريد الإلكتروني' },
    { key: 'nationality', label: 'الجنسية' },
    { key: 'id_type', label: 'نوع الهوية', format: 'badge' },
  ],
  fields: [
    { name: 'full_name', label: 'الاسم الكامل', required: true },
    { name: 'phone', label: 'الهاتف' },
    { name: 'email', label: 'البريد الإلكتروني', kind: 'email' },
    { name: 'nationality', label: 'الجنسية' },
    {
      name: 'id_type',
      label: 'نوع الهوية',
      kind: 'select',
      options: options({ national_id: 'هوية وطنية', passport: 'جواز سفر', iqama: 'إقامة' }),
    },
    { name: 'id_number', label: 'رقم الهوية' },
    { name: 'notes', label: 'ملاحظات', kind: 'textarea', span: 2 },
  ],
}
