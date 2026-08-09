<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * core/permissions — يعرّف كل الصلاحيات المتاحة في النظام ويربطها بالأدوار
 * الأربعة الأساسية (admin/manager/employee/accountant) بنفس التوزيع اللي
 * كان متبني في middleware('role:...') القديم عبر الـ routes، عشان الترقية
 * للنظام الجديد متغيرش أي صلاحيات فعلية موجودة حاليًا.
 *
 * تشغيل: php artisan db:seed --class=PermissionSeeder
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // accounting
            ['accounting.journal-entries.post', 'ترحيل قيد محاسبي', 'accounting'],
            ['accounting.periods.manage', 'إدارة الفترات المحاسبية والإقفال', 'accounting'],
            ['accounting.periods.reopen', 'إعادة فتح فترة مقفلة', 'accounting'],
            // procurement
            ['procurement.supplier-bills.approve', 'اعتماد فاتورة مورد', 'procurement'],
            // finance
            ['finance.payments.void', 'إلغاء سند قبض/صرف', 'finance'],
            // hr / payroll
            ['hr.payroll.run', 'تشغيل دورة رواتب', 'hr'],
            ['hr.employees.manage', 'إدارة بيانات الموظفين', 'hr'],
            // core
            ['core.users.manage', 'إدارة المستخدمين', 'core'],
            ['core.roles.manage', 'إدارة الأدوار والصلاحيات', 'core'],
            ['core.settings.manage', 'إدارة إعدادات النظام', 'core'],
            // sales / crm
            ['sales.orders.manage', 'إدارة أوامر البيع', 'sales'],
            ['crm.leads.manage', 'إدارة العملاء المحتملين', 'crm'],
            // inventory
            ['inventory.stock-movements.manage', 'إدارة حركة المخزون', 'inventory'],
            // hotel
            ['hotel.reservations.manage', 'إدارة الحجوزات', 'hotel'],
            ['hotel.checkout.manage', 'تسجيل مغادرة وإصدار فاتورة', 'hotel'],
            ['hotel.channels.manage', 'إدارة قنوات الحجز', 'hotel'],
            ['hotel.housekeeping.manage', 'إدارة مهام التنظيف', 'hotel'],
            ['hotel.pos.manage', 'إدارة نقاط البيع', 'hotel'],
            // reports
            ['reports.financial.view', 'الاطلاع على التقارير المالية', 'reports'],
        ];

        foreach ($permissions as [$slug, $name, $group]) {
            Permission::firstOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
        }

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'مدير النظام', 'is_system' => true]
        );
        $admin->permissions()->sync(Permission::pluck('id')); // admin دايمًا كل الصلاحيات

        $manager = Role::firstOrCreate(
            ['slug' => 'manager'],
            ['name' => 'مدير', 'is_system' => true]
        );
        $manager->permissions()->sync(Permission::whereIn('slug', [
            'procurement.supplier-bills.approve',
            'hr.employees.manage',
            'sales.orders.manage',
            'crm.leads.manage',
            'inventory.stock-movements.manage',
            'hotel.reservations.manage',
            'hotel.checkout.manage',
            'hotel.housekeeping.manage',
            'hotel.pos.manage',
            'reports.financial.view',
        ])->pluck('id'));

        $accountant = Role::firstOrCreate(
            ['slug' => 'accountant'],
            ['name' => 'محاسب', 'is_system' => true]
        );
        $accountant->permissions()->sync(Permission::whereIn('slug', [
            'accounting.journal-entries.post',
            'accounting.periods.manage',
            'procurement.supplier-bills.approve',
            'finance.payments.void',
            'reports.financial.view',
        ])->pluck('id'));

        $employee = Role::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'موظف', 'is_system' => true]
        );
        $employee->permissions()->sync(Permission::whereIn('slug', [
            'hotel.reservations.manage',
            'hotel.housekeeping.manage',
            'hotel.pos.manage',
        ])->pluck('id'));
    }
}
