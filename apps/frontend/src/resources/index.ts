import { accounts, journalEntries } from './accounting'
import {
  bankAccounts,
  bankTransactions,
  budgets,
  documents,
  users,
} from './finance'
import {
  boms,
  productionOrders,
  products,
  stockMovements,
  warehouses,
} from './operations'
import {
  candidates,
  employees,
  jobOpenings,
  leads,
  opportunities,
  payrollRuns,
  tickets,
} from './people'
import {
  contracts,
  fixedAssets,
  projects,
  properties,
  qualityInspections,
} from './projects'
import { guests, roomTypes, rooms } from './hotel'
import { drivers, fuelLogs, maintenanceRecords, trips, vehicles, violations } from './fleet'
import { posSales, registerSessions } from './retail'
import {
  customers,
  purchaseOrders,
  salesOrders,
  suppliers,
  supplierBills,
} from './trade'
import type { Resource } from './types'

/** كل الشاشات اللي بتتولّد من تعريف. الشاشات الخاصة (اللوحة، التقارير، السندات) منفصلة. */
export const resources: Resource[] = [
  accounts,
  journalEntries,
  customers,
  salesOrders,
  suppliers,
  purchaseOrders,
  supplierBills,
  warehouses,
  products,
  stockMovements,
  boms,
  productionOrders,
  projects,
  contracts,
  properties,
  qualityInspections,
  fixedAssets,
  employees,
  payrollRuns,
  jobOpenings,
  candidates,
  leads,
  opportunities,
  tickets,
  bankAccounts,
  bankTransactions,
  budgets,
  documents,
  users,
  roomTypes,
  rooms,
  guests,
  drivers,
  vehicles,
  trips,
  maintenanceRecords,
  fuelLogs,
  violations,
  registerSessions,
  posSales,
]

export const resourceBySlug = new Map(resources.map((resource) => [resource.slug, resource]))

/** ترتيب القائمة الجانبية. `custom: true` يعني الشاشة مكتوبة بإيد مش متولّدة. */
export interface NavItem {
  to: string
  label: string
  custom?: boolean
}

export interface NavGroup {
  label: string
  items: NavItem[]
}

export const navGroups: NavGroup[] = [
  {
    label: 'المالية',
    items: [
      { to: '/invoices', label: 'فواتير المبيعات', custom: true },
      { to: '/supplier-bills', label: 'فواتير الموردين' },
      { to: '/payments', label: 'سندات القبض والصرف', custom: true },
      { to: '/reports', label: 'التقارير المالية', custom: true },
    ],
  },
  {
    label: 'المحاسبة',
    items: [
      { to: '/accounts', label: 'شجرة الحسابات' },
      { to: '/journal-entries', label: 'القيود اليومية' },
      { to: '/periods', label: 'الفترات المحاسبية', custom: true },
      { to: '/budgets', label: 'الموازنات' },
      { to: '/bank-accounts', label: 'الحسابات البنكية' },
      { to: '/bank-transactions', label: 'الحركات البنكية' },
    ],
  },
  {
    label: 'المبيعات والعملاء',
    items: [
      { to: '/customers', label: 'العملاء' },
      { to: '/sales-orders', label: 'طلبات البيع' },
      { to: '/leads', label: 'العملاء المحتملين' },
      { to: '/opportunities', label: 'الفرص البيعية' },
      { to: '/tickets', label: 'تذاكر الدعم' },
    ],
  },
  {
    label: 'المشتريات والمخزون',
    items: [
      { to: '/suppliers', label: 'الموردين' },
      { to: '/purchase-orders', label: 'أوامر الشراء' },
      { to: '/warehouses', label: 'المستودعات' },
      { to: '/products', label: 'المنتجات والأصناف' },
      { to: '/stock-movements', label: 'حركات المخزون' },
      { to: '/bom', label: 'قوائم المواد' },
      { to: '/production-orders', label: 'أوامر الإنتاج' },
    ],
  },
  {
    label: 'المشاريع والأصول',
    items: [
      { to: '/projects', label: 'المشاريع' },
      { to: '/contracts', label: 'العقود' },
      { to: '/properties', label: 'العقارات والوحدات' },
      { to: '/quality-inspections', label: 'تفتيشات الجودة' },
      { to: '/fixed-assets', label: 'الأصول الثابتة' },
    ],
  },
  {
    label: 'الموارد البشرية',
    items: [
      { to: '/employees', label: 'الموظفين' },
      { to: '/payroll-runs', label: 'دورات الرواتب' },
      { to: '/job-openings', label: 'الوظائف الشاغرة' },
      { to: '/candidates', label: 'المتقدمين' },
    ],
  },
  {
    label: 'الفندقة',
    items: [
      { to: '/hotel-room-types', label: 'أنواع الغرف' },
      { to: '/hotel-rooms', label: 'الغرف' },
      { to: '/hotel-guests', label: 'النزلاء' },
    ],
  },
  {
    label: 'الأسطول والنقل',
    items: [
      { to: '/fleet-vehicles', label: 'المركبات' },
      { to: '/fleet-drivers', label: 'السائقين' },
      { to: '/fleet-trips', label: 'الرحلات' },
      { to: '/fleet-maintenance-records', label: 'الصيانة' },
      { to: '/fleet-fuel-logs', label: 'سجلات الوقود' },
      { to: '/fleet-violations', label: 'المخالفات المرورية' },
    ],
  },
  {
    label: 'نقطة البيع',
    items: [
      { to: '/retail-register-sessions', label: 'شيفتات الكاشير' },
      { to: '/retail-pos-sales', label: 'المبيعات' },
    ],
  },
  {
    label: 'النظام',
    items: [
      { to: '/documents', label: 'المستندات' },
      { to: '/users', label: 'المستخدمين' },
    ],
  },
]
