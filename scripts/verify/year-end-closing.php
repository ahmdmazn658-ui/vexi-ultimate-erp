<?php
/**
 * يتحقق من قيد الإقفال السنوي ومن حدود حماية الفترات المقفلة.
 */

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE chart_of_accounts (id INTEGER PRIMARY KEY, account_code TEXT, account_name TEXT, account_type TEXT)");
$db->exec("CREATE TABLE journal_entries (id INTEGER PRIMARY KEY, entry_number TEXT, entry_date TEXT, status TEXT)");
$db->exec("CREATE TABLE journal_entry_lines (id INTEGER PRIMARY KEY, journal_entry_id INTEGER, account_id INTEGER, debit REAL DEFAULT 0, credit REAL DEFAULT 0)");

$accounts = [
    ['1110', 'Cash', 'asset'], ['1130', 'AR', 'asset'],
    ['2100', 'AP', 'liability'], ['2160', 'VAT Payable', 'liability'],
    ['3000', 'Equity', 'equity'], ['3200', 'Retained Earnings', 'equity'],
    ['4100', 'Sales Revenue', 'revenue'], ['4200', 'Other Income', 'revenue'],
    ['5900', 'G&A Expenses', 'expense'], ['5200', 'Payroll', 'expense'],
];
foreach ($accounts as $i => [$code, $name, $type]) {
    $db->prepare("INSERT INTO chart_of_accounts VALUES (?,?,?,?)")->execute([$i + 1, $code, $name, $type]);
}
$acc = fn (string $c) => $db->query("SELECT id FROM chart_of_accounts WHERE account_code='$c'")->fetchColumn();

$entryId = 0; $lineId = 0;
$post = function (string $number, string $date, array $lines) use ($db, &$entryId, &$lineId) {
    $entryId++;
    $db->prepare("INSERT INTO journal_entries VALUES (?,?,?,'posted')")->execute([$entryId, $number, $date]);
    foreach ($lines as [$a, $d, $c]) {
        $lineId++;
        $db->prepare("INSERT INTO journal_entry_lines VALUES (?,?,?,?,?)")->execute([$lineId, $entryId, $a, $d, $c]);
    }
    $d = array_sum(array_column($lines, 1)); $c = array_sum(array_column($lines, 2));
    if (abs($d - $c) > 0.001) throw new RuntimeException("قيد غير متوازن: $number");
};

// ── حركة سنة 2026 ──
$post('JE-OPEN', '2026-01-01', [[$acc('1110'), 50000, 0], [$acc('3000'), 0, 50000]]);
$post('JE-SALE-1', '2026-03-10', [[$acc('1130'), 23000, 0], [$acc('4100'), 0, 20000], [$acc('2160'), 0, 3000]]);
$post('JE-SALE-2', '2026-06-20', [[$acc('1110'), 5750, 0], [$acc('4100'), 0, 5000], [$acc('2160'), 0, 750]]);
$post('JE-OTHER', '2026-08-01', [[$acc('1110'), 2000, 0], [$acc('4200'), 0, 2000]]);
$post('JE-EXP-1', '2026-04-15', [[$acc('5900'), 7000, 0], [$acc('2100'), 0, 7000]]);
$post('JE-PAYROLL', '2026-11-30', [[$acc('5200'), 9000, 0], [$acc('1110'), 0, 9000]]);

$DEBIT_NATURE = ['asset', 'expense'];

$totalsUpTo = function (?string $from, string $to) use ($db) {
    $t = [];
    $where = $from ? "AND e.entry_date >= '$from'" : '';
    foreach ($db->query("SELECT l.account_id, SUM(l.debit) td, SUM(l.credit) tc
        FROM journal_entry_lines l JOIN journal_entries e ON e.id=l.journal_entry_id
        WHERE e.status='posted' AND e.entry_date <= '$to' $where GROUP BY l.account_id") as $r) {
        $t[$r['account_id']] = $r;
    }
    return $t;
};

$section = function (string $type, array $totals) use ($db, $DEBIT_NATURE) {
    $sign = in_array($type, $DEBIT_NATURE, true) ? 1 : -1;
    $out = [];
    foreach ($db->query("SELECT * FROM chart_of_accounts WHERE account_type='$type' ORDER BY account_code") as $a) {
        $t = $totals[$a['id']] ?? null;
        $amount = round((((float)($t['td'] ?? 0)) - ((float)($t['tc'] ?? 0))) * $sign, 2);
        if (abs($amount) > 0.001) $out[] = ['id' => $a['id'], 'code' => $a['account_code'], 'name' => $a['account_name'], 'amount' => $amount];
    }
    return $out;
};

$totals2026 = $totalsUpTo('2026-01-01', '2026-12-31');
$revenue = $section('revenue', $totals2026);
$expenses = $section('expense', $totals2026);
$netIncome = round(array_sum(array_column($revenue, 'amount')) - array_sum(array_column($expenses, 'amount')), 2);

echo "── قائمة دخل 2026 (قبل الإقفال) ──\n";
foreach ($revenue as $l) printf("  إيراد   %-16s %10s\n", $l['name'], number_format($l['amount'], 2));
foreach ($expenses as $l) printf("  مصروف   %-16s %10s\n", $l['name'], number_format($l['amount'], 2));
printf("  صافي الربح %23s\n\n", number_format($netIncome, 2));

// ── قيد الإقفال السنوي (نفس منطق AccountingPeriodController@yearEndClosing) ──
$closingLines = [];
foreach ($revenue as $l) $closingLines[] = [$l['id'], $l['amount'], 0];   // تصفير الإيراد
foreach ($expenses as $l) $closingLines[] = [$l['id'], 0, $l['amount']];  // تصفير المصروف
if (abs($netIncome) > 0.001) {
    $closingLines[] = [$acc('3200'), $netIncome < 0 ? abs($netIncome) : 0, $netIncome > 0 ? $netIncome : 0];
}
$post('JE-CLOSING-2026', '2026-12-31', $closingLines);
echo "✅ قيد الإقفال اتسجّل ومتوازن (" . count($closingLines) . " سطر)\n\n";

// ── بعد الإقفال ──
$after = $totalsUpTo(null, '2026-12-31');
$revAfter = $section('revenue', $after);
$expAfter = $section('expense', $after);
$assets = $section('asset', $after);
$liabs = $section('liability', $after);
$equity = $section('equity', $after);

$totalAssets = round(array_sum(array_column($assets, 'amount')), 2);
$totalLiabs = round(array_sum(array_column($liabs, 'amount')), 2);
$totalEquity = round(array_sum(array_column($equity, 'amount')), 2);

echo "── المركز المالي 2026-12-31 (بعد الإقفال) ──\n";
foreach ($assets as $l) printf("  أصل     %-16s %10s\n", $l['name'], number_format($l['amount'], 2));
foreach ($liabs as $l) printf("  التزام  %-16s %10s\n", $l['name'], number_format($l['amount'], 2));
foreach ($equity as $l) printf("  ملكية   %-16s %10s\n", $l['name'], number_format($l['amount'], 2));

$retained = 0.0;
foreach ($equity as $l) if ($l['code'] === '3200') $retained = $l['amount'];

echo "\n── فحوصات الإقفال ──\n";
$checks = [
    'حسابات الإيرادات اتصفّرت' => count($revAfter) === 0,
    'حسابات المصروفات اتصفّرت' => count($expAfter) === 0,
    'الأرباح المحتجزة = صافي الربح' => abs($retained - $netIncome) < 0.01,
    'الأصول = الالتزامات + حقوق الملكية' => abs($totalAssets - ($totalLiabs + $totalEquity)) < 0.01,
    'صافي الربح = 27,000 - 16,000' => abs($netIncome - 11000) < 0.01,
];

// ── حدود حماية الفترات ──
$periods = [];
for ($m = 1; $m <= 12; $m++) {
    $start = sprintf('2026-%02d-01', $m);
    $end = date('Y-m-t', strtotime($start));
    $periods[] = ['start' => $start, 'end' => $end, 'closed' => $m <= 6]; // النص الأول مقفول
}
$isClosed = function (string $date) use ($periods) {
    foreach ($periods as $p) {
        if ($date >= $p['start'] && $date <= $p['end']) return $p['closed'];
    }
    return false; // تاريخ خارج أي فترة معرّفة = مفتوح
};

echo "\n── حماية الفترات (يناير–يونيو مقفولة) ──\n";
$guardCases = [
    ['2026-06-30', true,  'آخر يوم في فترة مقفلة'],
    ['2026-07-01', false, 'أول يوم في فترة مفتوحة'],
    ['2026-01-01', true,  'أول يوم في السنة'],
    ['2026-12-31', false, 'آخر يوم في السنة'],
    ['2025-11-15', false, 'تاريخ خارج أي فترة معرّفة'],
];
foreach ($guardCases as [$date, $expected, $label]) {
    $actual = $isClosed($date);
    $ok = $actual === $expected;
    printf("  %s %s → %s (%s)\n", $ok ? '✅' : '❌', $date, $actual ? 'مقفلة' : 'مفتوحة', $label);
    $checks["حماية: $label"] = $ok;
}

$failed = 0;
echo "\n── النتيجة ──\n";
foreach ($checks as $label => $ok) {
    if (! $ok) { echo "  ❌ $label\n"; $failed++; }
}
echo $failed === 0 ? "🎉 كل الفحوصات نجحت (" . count($checks) . " فحص).\n" : "⚠️  فشل {$failed} فحص.\n";
exit($failed === 0 ? 0 : 1);
