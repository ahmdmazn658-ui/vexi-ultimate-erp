<?php
/**
 * Harness يتحقق من صحة تصميم القيود والتقارير الجديدة.
 * بيعيد تنفيذ نفس سطور القيد اللي بتولّدها الـ controllers على SQLite،
 * وبعدين بيشغّل نفس منطق ميزان المراجعة / قائمة الدخل / المركز المالي.
 */

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE chart_of_accounts (id INTEGER PRIMARY KEY, account_code TEXT, account_name TEXT, account_type TEXT)");
$db->exec("CREATE TABLE journal_entries (id INTEGER PRIMARY KEY, entry_number TEXT, entry_date TEXT, status TEXT, project_id INTEGER)");
$db->exec("CREATE TABLE journal_entry_lines (id INTEGER PRIMARY KEY, journal_entry_id INTEGER, account_id INTEGER, debit REAL DEFAULT 0, credit REAL DEFAULT 0)");

$accounts = [
    ['1110', 'Cash on Hand', 'asset'],
    ['1130', 'Accounts Receivable', 'asset'],
    ['1170', 'VAT Receivable', 'asset'],
    ['2100', 'Accounts Payable', 'liability'],
    ['2160', 'VAT Payable', 'liability'],
    ['3000', 'Equity', 'equity'],
    ['4100', 'Sales Revenue', 'revenue'],
    ['5900', 'G&A Expenses', 'expense'],
];
foreach ($accounts as $i => [$code, $name, $type]) {
    $db->prepare("INSERT INTO chart_of_accounts (id, account_code, account_name, account_type) VALUES (?,?,?,?)")
       ->execute([$i + 1, $code, $name, $type]);
}
$acc = fn (string $code) => $db->query("SELECT id FROM chart_of_accounts WHERE account_code='$code'")->fetchColumn();

$entryId = 0;
$lineId = 0;
$post = function (string $number, string $date, array $lines) use ($db, &$entryId, &$lineId) {
    $entryId++;
    $db->prepare("INSERT INTO journal_entries (id, entry_number, entry_date, status) VALUES (?,?,?,'posted')")
       ->execute([$entryId, $number, $date]);
    foreach ($lines as [$accountId, $debit, $credit]) {
        $lineId++;
        $db->prepare("INSERT INTO journal_entry_lines (id, journal_entry_id, account_id, debit, credit) VALUES (?,?,?,?,?)")
           ->execute([$lineId, $entryId, $accountId, $debit, $credit]);
    }
    // كل قيد لازم يكون متوازن في ذاته
    $d = array_sum(array_column($lines, 1));
    $c = array_sum(array_column($lines, 2));
    if (abs($d - $c) > 0.001) {
        throw new RuntimeException("قيد غير متوازن: $number (مدين $d / دائن $c)");
    }
};

// ── 1. رأس المال الافتتاحي: 100,000 نقدية مقابل حقوق ملكية
$post('JE-OPENING', '2026-01-01', [
    [$acc('1110'), 100000, 0],
    [$acc('3000'), 0, 100000],
]);

// ── 2. InvoiceController@issue — فاتورة مبيعات 10,000 + 15% ضريبة
$subtotal = 10000.00; $vat = 1500.00; $total = 11500.00;
$post('JE-INV-2026-000001', '2026-02-10', [
    [$acc('1130'), $total, 0],
    [$acc('4100'), 0, $subtotal],
    [$acc('2160'), 0, $vat],
]);

// ── 3. SupplierBillController@approve — فاتورة مورد 4,000 + 600 ضريبة
$billSub = 4000.00; $billVat = 600.00; $billTotal = 4600.00;
$post('JE-BILL-2026-000001', '2026-02-15', [
    [$acc('5900'), $billSub, 0],
    [$acc('1170'), $billVat, 0],
    [$acc('2100'), 0, $billTotal],
]);

// ── 4. PaymentController@store (receipt) — تحصيل جزئي 5,000 من العميل
$post('JE-RCV-2026-000001', '2026-03-01', [
    [$acc('1110'), 5000, 0],
    [$acc('1130'), 0, 5000],
]);

// ── 5. PaymentController@store (payment) — سداد كامل للمورد
$post('JE-PAY-2026-000001', '2026-03-05', [
    [$acc('2100'), $billTotal, 0],
    [$acc('1110'), 0, $billTotal],
]);

// ═══ التقارير ═══
$DEBIT_NATURE = ['asset', 'expense'];
$asOf = '2026-12-31';

$totals = [];
$rows = $db->query("
    SELECT l.account_id, SUM(l.debit) td, SUM(l.credit) tc
    FROM journal_entry_lines l JOIN journal_entries e ON e.id = l.journal_entry_id
    WHERE e.status = 'posted' AND e.entry_date <= '$asOf'
    GROUP BY l.account_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) { $totals[$r['account_id']] = $r; }

// ميزان المراجعة
$totalDebit = 0; $totalCredit = 0;
echo "── ميزان المراجعة ─────────────────────────────\n";
foreach ($db->query("SELECT * FROM chart_of_accounts ORDER BY account_code") as $a) {
    $t = $totals[$a['id']] ?? null;
    if (! $t) { continue; }
    $net = (float) $t['td'] - (float) $t['tc'];
    $bd = $net > 0 ? $net : 0;
    $bc = $net < 0 ? abs($net) : 0;
    $totalDebit += $bd; $totalCredit += $bc;
    printf("  %-6s %-22s مدين %10s   دائن %10s\n", $a['account_code'], $a['account_name'],
        number_format($bd, 2), number_format($bc, 2));
}
printf("  الإجمالي:%29s %10s        %10s\n", '', number_format($totalDebit, 2), number_format($totalCredit, 2));

$section = function (string $type) use ($db, $totals, $DEBIT_NATURE) {
    $sign = in_array($type, $DEBIT_NATURE, true) ? 1 : -1;
    $out = [];
    foreach ($db->query("SELECT * FROM chart_of_accounts WHERE account_type='$type' ORDER BY account_code") as $a) {
        $t = $totals[$a['id']] ?? null;
        $amount = round((((float) ($t['td'] ?? 0)) - ((float) ($t['tc'] ?? 0))) * $sign, 2);
        if (abs($amount) > 0.001) { $out[$a['account_name']] = $amount; }
    }
    return $out;
};

$revenue = $section('revenue'); $expense = $section('expense');
$totalRevenue = array_sum($revenue); $totalExpense = array_sum($expense);
$netIncome = round($totalRevenue - $totalExpense, 2);

echo "\n── قائمة الدخل ───────────────────────────────\n";
printf("  الإيرادات %31s\n", number_format($totalRevenue, 2));
printf("  المصروفات %31s\n", number_format($totalExpense, 2));
printf("  صافي الربح %30s\n", number_format($netIncome, 2));

$assets = $section('asset'); $liabilities = $section('liability'); $equity = $section('equity');
$totalAssets = round(array_sum($assets), 2);
$totalLiabilities = round(array_sum($liabilities), 2);
$totalEquity = round(array_sum($equity) + $netIncome, 2);

echo "\n── المركز المالي ─────────────────────────────\n";
foreach ($assets as $n => $v) { printf("  أصل   %-22s %12s\n", $n, number_format($v, 2)); }
foreach ($liabilities as $n => $v) { printf("  التزام %-21s %12s\n", $n, number_format($v, 2)); }
printf("  حقوق ملكية (شامل أرباح الفترة) %13s\n", number_format($totalEquity, 2));

echo "\n── الفحوصات ──────────────────────────────────\n";
$checks = [
    'ميزان المراجعة متوازن' => abs($totalDebit - $totalCredit) < 0.01,
    'الأصول = الالتزامات + حقوق الملكية' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
    'الذمم المدينة = 11,500 - 5,000 محصّل' => abs(($assets['Accounts Receivable'] ?? 0) - 6500) < 0.01,
    'الذمم الدائنة اتسددت بالكامل (صفر)' => ! isset($liabilities['Accounts Payable']),
    'النقدية = 100,000 + 5,000 - 4,600' => abs(($assets['Cash on Hand'] ?? 0) - 100400) < 0.01,
    'ضريبة المخرجات المستحقة = 1,500' => abs(($liabilities['VAT Payable'] ?? 0) - 1500) < 0.01,
    'ضريبة المدخلات القابلة للاسترداد = 600' => abs(($assets['VAT Receivable'] ?? 0) - 600) < 0.01,
    'صافي الربح = 10,000 - 4,000' => abs($netIncome - 6000) < 0.01,
];
$failed = 0;
foreach ($checks as $label => $ok) {
    echo ($ok ? "  ✅ " : "  ❌ ") . $label . "\n";
    if (! $ok) { $failed++; }
}

// ── أعمار الديون (نفس منطق bucketAging) ──
echo "\n── أعمار الذمم المدينة (as_of = 2026-04-15) ──\n";
$agingAsOf = new DateTimeImmutable('2026-04-15');
$invoices = [
    ['number' => 'INV-1', 'due_date' => '2026-05-01', 'balance' => 6500],  // لسه مستحقة
    ['number' => 'INV-2', 'due_date' => '2026-04-01', 'balance' => 1000],  // 14 يوم
    ['number' => 'INV-3', 'due_date' => '2026-03-01', 'balance' => 2000],  // 45 يوم
    ['number' => 'INV-4', 'due_date' => '2025-11-01', 'balance' => 500],   // > 90 يوم
];
$buckets = ['current' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'over_90' => 0];
foreach ($invoices as $inv) {
    $due = new DateTimeImmutable($inv['due_date']);
    $daysOverdue = (int) $due->diff($agingAsOf)->format('%r%a');
    $bucket = match (true) {
        $daysOverdue <= 0 => 'current',
        $daysOverdue <= 30 => '1_30',
        $daysOverdue <= 60 => '31_60',
        $daysOverdue <= 90 => '61_90',
        default => 'over_90',
    };
    $buckets[$bucket] += $inv['balance'];
    printf("  %-7s استحقاق %s → متأخر %4d يوم → %s\n", $inv['number'], $inv['due_date'], max(0, $daysOverdue), $bucket);
}
$expected = ['current' => 6500, '1_30' => 1000, '31_60' => 2000, '61_90' => 0, 'over_90' => 500];
$agingOk = $buckets == $expected;
echo ($agingOk ? "  ✅ " : "  ❌ ") . "توزيع الشرائح صحيح\n";
if (! $agingOk) { $failed++; }

echo "\n" . ($failed === 0 ? "🎉 كل الفحوصات نجحت." : "⚠️  فشل {$failed} فحص.") . "\n";
exit($failed === 0 ? 0 : 1);
