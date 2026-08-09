<?php

namespace App\Support\Accounting;

use App\Models\Account;

/**
 * حسابات دفتر الأستاذ الافتراضية اللي بتستخدمها القيود المُولَّدة تلقائياً
 * (زي قيد فاتورة المبيعات عند الإصدار). لو الحساب مش موجود في شجرة الحسابات
 * بيتم إنشاؤه تلقائياً بالكود القياسي في أول استخدام (firstOrCreate).
 */
class DefaultAccounts
{
    public static function accountsReceivable(): Account
    {
        return self::resolve('1130', 'Accounts Receivable', 'الذمم المدينة - عملاء', 'asset');
    }

    public static function salesRevenue(): Account
    {
        return self::resolve('4100', 'Sales Revenue', 'إيرادات المبيعات', 'revenue');
    }

    public static function vatPayable(): Account
    {
        return self::resolve('2160', 'VAT Payable (Output Tax)', 'ضريبة القيمة المضافة المستحقة (مخرجات)', 'liability');
    }

    public static function payrollExpense(): Account
    {
        return self::resolve('5200', 'Payroll Expenses', 'مصروفات الرواتب', 'expense');
    }

    public static function salariesPayable(): Account
    {
        return self::resolve('2200', 'Salaries Payable', 'رواتب مستحقة الدفع', 'liability');
    }

    /** الذمم الدائنة — موردون (الطرف الدائن في قيد فاتورة المشتريات) */
    public static function accountsPayable(): Account
    {
        return self::resolve('2100', 'Accounts Payable', 'الذمم الدائنة - موردون', 'liability');
    }

    /** ضريبة القيمة المضافة القابلة للاسترداد (مدخلات) */
    public static function vatReceivable(): Account
    {
        return self::resolve('1170', 'VAT Receivable (Input Tax)', 'ضريبة القيمة المضافة القابلة للاسترداد (مدخلات)', 'asset');
    }

    /** حساب النقدية الافتراضي — بيُستخدم لو البنك مش مربوط بحساب في الشجرة أو الدفع كاش */
    public static function cashAndBank(): Account
    {
        return self::resolve('1110', 'Cash on Hand', 'النقدية بالصندوق', 'asset');
    }

    public static function inventory(): Account
    {
        return self::resolve('1200', 'Inventory', 'المخزون', 'asset');
    }

    public static function costOfGoodsSold(): Account
    {
        return self::resolve('5100', 'Cost of Goods Sold', 'تكلفة البضاعة المباعة', 'expense');
    }

    /** حساب المصروفات العام — الافتراضي لفواتير الموردين لو محددش حساب مصروف */
    public static function generalExpense(): Account
    {
        return self::resolve('5900', 'General & Administrative Expenses', 'مصروفات عمومية وإدارية', 'expense');
    }

    /** الأرباح المحتجزة — وعاء صافي نتيجة السنوات المقفلة */
    public static function retainedEarnings(): Account
    {
        return self::resolve('3200', 'Retained Earnings', 'الأرباح المحتجزة', 'equity');
    }

    /** مصروف صيانة الأسطول — بيتقيّد تلقائيًا عند تسجيل أي سجل صيانة لمركبة */
    public static function vehicleMaintenanceExpense(): Account
    {
        return self::resolve('5410', 'Vehicle Maintenance Expense', 'مصروف صيانة المركبات', 'expense');
    }

    /** مصروف وقود الأسطول — بيتقيّد تلقائيًا عند تسجيل سجل وقود لمركبة */
    public static function fuelExpense(): Account
    {
        return self::resolve('5420', 'Fuel Expense', 'مصروف الوقود', 'expense');
    }

    /** مصروف المخالفات المرورية للشركة (liability = company) */
    public static function trafficViolationsExpense(): Account
    {
        return self::resolve('5430', 'Traffic Violations Expense', 'مصروف المخالفات المرورية', 'expense');
    }

    /** ذمم مستحقة على السائقين — لما تحمّل المخالفة على السائق مش الشركة */
    public static function dueFromDrivers(): Account
    {
        return self::resolve('1180', 'Due from Drivers', 'ذمم مدينة - سائقين', 'asset');
    }

    private static function resolve(string $code, string $name, string $nameAr, string $type): Account
    {
        return Account::firstOrCreate(
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
