import type { ResourceConfig } from './types';

export const settingsResources: ResourceConfig[] = [
  {
    key: 'module-settings',
    label: 'Module Settings',
    labelAr: 'إعدادات الموديولات',
    endpoint: '/v1/settings/modules',
    icon: '⚙️',
    columns: [
      { key: 'module', label: 'Module', labelAr: 'الموديول' },
      { key: 'is_active', label: 'Active', labelAr: 'مفعّل', type: 'boolean' },
      { key: 'is_installed', label: 'Installed', labelAr: 'مثبّت', type: 'boolean' },
      { key: 'version', label: 'Version', labelAr: 'الإصدار' },
    ],
  },
];

// All module setting groups with their labels
export const moduleSettingGroups = {
  accounting: {
    label: 'Accounting',
    labelAr: 'المحاسبة',
    icon: '💰',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'journal_entries', label: 'Journal Entries', labelAr: 'القيود اليومية' },
      { key: 'period_closing', label: 'Period Closing', labelAr: 'إقفال الفترات' },
      { key: 'cost_centers', label: 'Cost Centers', labelAr: 'مراكز التكلفة' },
    ],
  },
  e_invoicing: {
    label: 'E-Invoicing (ZATCA)',
    labelAr: 'الفوترة الإلكترونية',
    icon: '🧾',
    groups: [
      { key: 'zatca', label: 'ZATCA', labelAr: 'هيئة الزكاة' },
      { key: 'invoice', label: 'Invoice', labelAr: 'الفواتير' },
      { key: 'tax', label: 'Tax', labelAr: 'الضرائب' },
    ],
  },
  finance: {
    label: 'Finance',
    labelAr: 'المالية',
    icon: '💵',
    groups: [
      { key: 'payments', label: 'Payments', labelAr: 'المدفوعات' },
      { key: 'treasury', label: 'Treasury', labelAr: 'الخزينة' },
      { key: 'intercompany', label: 'Intercompany', labelAr: 'بين الشركات' },
    ],
  },
  banking: {
    label: 'Banking',
    labelAr: 'البنوك',
    icon: '🏦',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'cheques', label: 'Cheques', labelAr: 'الشيكات' },
    ],
  },
  budgeting: {
    label: 'Budgeting',
    labelAr: 'الميزانيات',
    icon: '📊',
    groups: [{ key: 'general', label: 'General', labelAr: 'عام' }],
  },
  fixed_assets: {
    label: 'Fixed Assets',
    labelAr: 'الأصول الثابتة',
    icon: '🏗️',
    groups: [{ key: 'general', label: 'General', labelAr: 'عام' }],
  },
  crm: {
    label: 'CRM',
    labelAr: 'إدارة العملاء',
    icon: '📈',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'pipeline', label: 'Pipeline', labelAr: 'خط الأنابيب' },
      { key: 'communication', label: 'Communication', labelAr: 'التواصل' },
    ],
  },
  sales: {
    label: 'Sales',
    labelAr: 'المبيعات',
    icon: '🛒',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'pricing', label: 'Pricing', labelAr: 'التسعير' },
      { key: 'commission', label: 'Commission', labelAr: 'العمولات' },
    ],
  },
  purchase: {
    label: 'Purchase',
    labelAr: 'المشتريات',
    icon: '📦',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'approval', label: 'Approval', labelAr: 'الموافقات' },
      { key: 'vendor', label: 'Vendors', labelAr: 'الموردين' },
    ],
  },
  inventory: {
    label: 'Inventory',
    labelAr: 'المخزون',
    icon: '📦',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'warehouse', label: 'Warehouse', labelAr: 'المستودعات' },
      { key: 'counting', label: 'Counting', labelAr: 'الجرد' },
    ],
  },
  hr: {
    label: 'HR',
    labelAr: 'الموارد البشرية',
    icon: '👥',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'leave', label: 'Leave', labelAr: 'الإجازات' },
      { key: 'compliance', label: 'Saudi Compliance', labelAr: 'الامتثال السعودي' },
    ],
  },
  payroll: {
    label: 'Payroll',
    labelAr: 'الرواتب',
    icon: '💵',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'salary_structure', label: 'Salary Structure', labelAr: 'هيكل الراتب' },
      { key: 'deductions', label: 'Deductions', labelAr: 'الخصومات' },
    ],
  },
  attendance: {
    label: 'Attendance',
    labelAr: 'الحضور والانصراف',
    icon: '⏰',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'overtime', label: 'Overtime', labelAr: 'الإضافي' },
      { key: 'devices', label: 'Devices', labelAr: 'الأجهزة' },
    ],
  },
  manufacturing: {
    label: 'Manufacturing',
    labelAr: 'التصنيع',
    icon: '🏭',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'production', label: 'Production', labelAr: 'الإنتاج' },
      { key: 'mrp', label: 'MRP', labelAr: 'تخطيط المواد' },
    ],
  },
  projects: {
    label: 'Projects',
    labelAr: 'المشاريع',
    icon: '📋',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'construction', label: 'Construction', labelAr: 'المقاولات' },
    ],
  },
  fleet: {
    label: 'Fleet',
    labelAr: 'الأسطول',
    icon: '🚗',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'maintenance', label: 'Maintenance', labelAr: 'الصيانة' },
      { key: 'compliance', label: 'Compliance', labelAr: 'الامتثال' },
    ],
  },
  pos: {
    label: 'POS',
    labelAr: 'نقاط البيع',
    icon: '🏪',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'payment', label: 'Payment', labelAr: 'طرق الدفع' },
      { key: 'receipt', label: 'Receipt', labelAr: 'الإيصال' },
      { key: 'shift', label: 'Shift', labelAr: 'الوردية' },
    ],
  },
  hotel: {
    label: 'Hotel',
    labelAr: 'الفنادق',
    icon: '🏨',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'reservation', label: 'Reservations', labelAr: 'الحجوزات' },
      { key: 'channels', label: 'Channels', labelAr: 'القنوات' },
      { key: 'housekeeping', label: 'Housekeeping', labelAr: 'النظافة' },
    ],
  },
  real_estate: {
    label: 'Real Estate',
    labelAr: 'العقارات',
    icon: '🏢',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'maintenance', label: 'Maintenance', labelAr: 'الصيانة' },
    ],
  },
  restaurant: {
    label: 'Restaurant',
    labelAr: 'المطاعم',
    icon: '🍽️',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'delivery_apps', label: 'Delivery Apps', labelAr: 'تطبيقات التوصيل' },
      { key: 'inventory', label: 'Inventory', labelAr: 'المخزون' },
    ],
  },
  recruitment: {
    label: 'Recruitment',
    labelAr: 'التوظيف',
    icon: '🎓',
    groups: [{ key: 'general', label: 'General', labelAr: 'عام' }],
  },
  helpdesk: {
    label: 'Helpdesk',
    labelAr: 'الدعم الفني',
    icon: '🎫',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'channels', label: 'Channels', labelAr: 'القنوات' },
    ],
  },
  document_management: {
    label: 'Document Management',
    labelAr: 'إدارة المستندات',
    icon: '📄',
    groups: [{ key: 'general', label: 'General', labelAr: 'عام' }],
  },
  quality: {
    label: 'Quality',
    labelAr: 'الجودة',
    icon: '✅',
    groups: [{ key: 'general', label: 'General', labelAr: 'عام' }],
  },
  ecommerce: {
    label: 'E-Commerce',
    labelAr: 'التجارة الإلكترونية',
    icon: '🌐',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'platforms', label: 'Platforms', labelAr: 'المنصات' },
      { key: 'shipping', label: 'Shipping', labelAr: 'الشحن' },
    ],
  },
  ai: {
    label: 'AI',
    labelAr: 'الذكاء الاصطناعي',
    icon: '🤖',
    groups: [
      { key: 'general', label: 'General', labelAr: 'عام' },
      { key: 'agents', label: 'AI Agents', labelAr: 'الوكلاء الأذكياء' },
      { key: 'features', label: 'Features', labelAr: 'الميزات' },
    ],
  },
  system: {
    label: 'System',
    labelAr: 'النظام',
    icon: '⚙️',
    groups: [
      { key: 'company', label: 'Company', labelAr: 'الشركة' },
      { key: 'localization', label: 'Localization', labelAr: 'التوطين' },
      { key: 'notifications', label: 'Notifications', labelAr: 'الإشعارات' },
      { key: 'security', label: 'Security', labelAr: 'الأمان' },
      { key: 'backup', label: 'Backup', labelAr: 'النسخ الاحتياطي' },
    ],
  },
};
