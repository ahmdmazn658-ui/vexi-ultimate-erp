<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * شجرة حسابات افتراضية بسيطة (SAAB - Standard Arabic Accounting Base) تكفي
 * لبدء التشغيل مباشرة. الأكواد 1130/4100/2160/2100/1170/1110/5900 مطابقة لـ App\Support\Accounting\DefaultAccounts
 * المستخدمة في القيود التلقائية (فواتير البيع).
 */
class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // الأصول
            ['1000', 'Assets', 'الأصول', 'asset'],
            ['1100', 'Cash & Bank', 'النقدية والبنوك', 'asset'],
            ['1110', 'Cash on Hand', 'النقدية بالصندوق', 'asset'],
            ['1130', 'Accounts Receivable', 'الذمم المدينة - عملاء', 'asset'],
            ['1170', 'VAT Receivable (Input Tax)', 'ضريبة القيمة المضافة القابلة للاسترداد (مدخلات)', 'asset'],
            ['1180', 'Due from Drivers', 'ذمم مدينة - سائقين', 'asset'],
            ['1200', 'Inventory', 'المخزون', 'asset'],
            ['1500', 'Fixed Assets', 'الأصول الثابتة', 'asset'],

            // الالتزامات
            ['2000', 'Liabilities', 'الالتزامات', 'liability'],
            ['2100', 'Accounts Payable', 'الذمم الدائنة - موردون', 'liability'],
            ['2160', 'VAT Payable (Output Tax)', 'ضريبة القيمة المضافة المستحقة (مخرجات)', 'liability'],
            ['2200', 'Salaries Payable', 'رواتب مستحقة الدفع', 'liability'],

            // حقوق الملكية
            ['3000', 'Equity', 'حقوق الملكية', 'equity'],
            ['3200', 'Retained Earnings', 'الأرباح المحتجزة', 'equity'],

            // الإيرادات
            ['4000', 'Revenue', 'الإيرادات', 'revenue'],
            ['4100', 'Sales Revenue', 'إيرادات المبيعات', 'revenue'],

            // المصروفات
            ['5000', 'Expenses', 'المصروفات', 'expense'],
            ['5100', 'Cost of Goods Sold', 'تكلفة البضاعة المباعة', 'expense'],
            ['5200', 'Payroll Expenses', 'مصروفات الرواتب', 'expense'],
            ['5300', 'Depreciation Expense', 'مصروف الإهلاك', 'expense'],
            ['5410', 'Vehicle Maintenance Expense', 'مصروف صيانة المركبات', 'expense'],
            ['5420', 'Fuel Expense', 'مصروف الوقود', 'expense'],
            ['5430', 'Traffic Violations Expense', 'مصروف المخالفات المرورية', 'expense'],
            ['5900', 'General & Administrative Expenses', 'مصروفات عمومية وإدارية', 'expense'],
        ];

        foreach ($accounts as [$code, $name, $nameAr, $type]) {
            Account::firstOrCreate(
                ['account_code' => $code],
                [
                    'account_name' => $name,
                    'account_name_ar' => $nameAr,
                    'account_type' => $type,
                    'is_active' => true,
                ]
            );
        }
    }
}
