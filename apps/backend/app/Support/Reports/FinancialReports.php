<?php

namespace App\Support\Reports;

use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * محرّك التقارير المالية — reports/financial
 *
 * كل الأرقام هنا بتتحسب من دفتر الأستاذ (journal_entries + journal_entry_lines)
 * والقيود المُرحّلة فقط (status = posted)، فمفيش أي مصدر بيانات موازي.
 * ده بيخلي ميزان المراجعة وقائمة الدخل والمركز المالي متسقين دايماً مع القيود.
 */
class FinancialReports
{
    /** الحسابات اللي رصيدها الطبيعي مدين */
    private const DEBIT_NATURE = ['asset', 'expense'];

    /**
     * إجمالي المدين/الدائن لكل حساب خلال فترة (أو حتى تاريخ لو $from = null).
     *
     * @return Collection<int, object{account_id:int, total_debit:float, total_credit:float}>
     */
    public static function accountTotals(?string $from, ?string $to, ?int $projectId = null): Collection
    {
        return DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('e.entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('e.entry_date', '<=', $to))
            ->when($projectId, fn ($q) => $q->where('e.project_id', $projectId))
            ->groupBy('l.account_id')
            ->select(
                'l.account_id',
                DB::raw('SUM(l.debit) as total_debit'),
                DB::raw('SUM(l.credit) as total_credit')
            )
            ->get()
            ->keyBy('account_id');
    }

    /**
     * ميزان المراجعة — كل حساب برصيده المدين أو الدائن، مع إجماليات لازم تتساوى.
     */
    public static function trialBalance(?string $from, ?string $to, ?int $projectId = null): array
    {
        $totals = self::accountTotals($from, $to, $projectId);
        $accounts = Account::orderBy('account_code')->get();

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            $row = $totals->get($account->id);
            $debit = (float) ($row->total_debit ?? 0);
            $credit = (float) ($row->total_credit ?? 0);

            if ($debit == 0.0 && $credit == 0.0) {
                continue; // حسابات بدون حركة مش بتظهر في الميزان
            }

            $net = $debit - $credit;

            $rows[] = [
                'account_id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_name_ar' => $account->account_name_ar,
                'account_type' => $account->account_type,
                'total_debit' => round($debit, 2),
                'total_credit' => round($credit, 2),
                'balance_debit' => $net > 0 ? round($net, 2) : 0.0,
                'balance_credit' => $net < 0 ? round(abs($net), 2) : 0.0,
            ];

            $totalDebit += $net > 0 ? $net : 0;
            $totalCredit += $net < 0 ? abs($net) : 0;
        }

        return [
            'period' => ['from' => $from, 'to' => $to],
            'project_id' => $projectId,
            'rows' => $rows,
            'totals' => [
                'debit' => round($totalDebit, 2),
                'credit' => round($totalCredit, 2),
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
        ];
    }

    /**
     * قائمة الدخل — الإيرادات ناقص المصروفات خلال الفترة.
     */
    public static function incomeStatement(string $from, string $to, ?int $projectId = null): array
    {
        $totals = self::accountTotals($from, $to, $projectId);

        $revenue = self::sectionFor('revenue', $totals);
        $expenses = self::sectionFor('expense', $totals);

        $totalRevenue = array_sum(array_column($revenue, 'amount'));
        $totalExpenses = array_sum(array_column($expenses, 'amount'));
        $netIncome = round($totalRevenue - $totalExpenses, 2);

        return [
            'period' => ['from' => $from, 'to' => $to],
            'project_id' => $projectId,
            'revenue' => [
                'lines' => $revenue,
                'total' => round($totalRevenue, 2),
            ],
            'expenses' => [
                'lines' => $expenses,
                'total' => round($totalExpenses, 2),
            ],
            'net_income' => $netIncome,
            'margin_percent' => $totalRevenue > 0 ? round($netIncome / $totalRevenue * 100, 2) : null,
        ];
    }

    /**
     * قائمة المركز المالي كما في تاريخ محدد.
     * صافي ربح الفترة بيتضاف لحقوق الملكية كأرباح محتجزة عشان المعادلة تتزن.
     */
    public static function balanceSheet(string $asOf, ?int $projectId = null): array
    {
        $totals = self::accountTotals(null, $asOf, $projectId);

        $assets = self::sectionFor('asset', $totals);
        $liabilities = self::sectionFor('liability', $totals);
        $equity = self::sectionFor('equity', $totals);

        $revenueTotal = array_sum(array_column(self::sectionFor('revenue', $totals), 'amount'));
        $expenseTotal = array_sum(array_column(self::sectionFor('expense', $totals), 'amount'));
        $retainedEarnings = round($revenueTotal - $expenseTotal, 2);

        $totalAssets = round(array_sum(array_column($assets, 'amount')), 2);
        $totalLiabilities = round(array_sum(array_column($liabilities, 'amount')), 2);
        $totalEquity = round(array_sum(array_column($equity, 'amount')) + $retainedEarnings, 2);

        return [
            'as_of' => $asOf,
            'project_id' => $projectId,
            'assets' => ['lines' => $assets, 'total' => $totalAssets],
            'liabilities' => ['lines' => $liabilities, 'total' => $totalLiabilities],
            'equity' => [
                'lines' => $equity,
                'retained_earnings' => $retainedEarnings,
                'total' => $totalEquity,
            ],
            'check' => [
                'assets' => $totalAssets,
                'liabilities_plus_equity' => round($totalLiabilities + $totalEquity, 2),
                'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            ],
        ];
    }

    /**
     * دفتر أستاذ حساب واحد: رصيد افتتاحي + الحركات + رصيد جاري تراكمي.
     */
    public static function generalLedger(Account $account, string $from, string $to, ?int $projectId = null): array
    {
        $sign = in_array($account->account_type, self::DEBIT_NATURE, true) ? 1 : -1;

        $opening = DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', 'posted')
            ->where('l.account_id', $account->id)
            ->whereDate('e.entry_date', '<', $from)
            ->when($projectId, fn ($q) => $q->where('e.project_id', $projectId))
            ->selectRaw('COALESCE(SUM(l.debit), 0) - COALESCE(SUM(l.credit), 0) as net')
            ->value('net');

        $openingBalance = round((float) $opening * $sign, 2);

        $lines = DB::table('journal_entry_lines as l')
            ->join('journal_entries as e', 'e.id', '=', 'l.journal_entry_id')
            ->where('e.status', 'posted')
            ->where('l.account_id', $account->id)
            ->whereDate('e.entry_date', '>=', $from)
            ->whereDate('e.entry_date', '<=', $to)
            ->when($projectId, fn ($q) => $q->where('e.project_id', $projectId))
            ->orderBy('e.entry_date')
            ->orderBy('l.id')
            ->select(
                'e.id as journal_entry_id',
                'e.entry_number',
                'e.entry_date',
                'e.reference',
                'e.description',
                'l.debit',
                'l.credit',
                'l.memo'
            )
            ->get();

        $running = $openingBalance;
        $rows = [];

        foreach ($lines as $line) {
            $running = round($running + ((float) $line->debit - (float) $line->credit) * $sign, 2);

            $rows[] = [
                'journal_entry_id' => $line->journal_entry_id,
                'entry_number' => $line->entry_number,
                'entry_date' => $line->entry_date,
                'reference' => $line->reference,
                'description' => $line->memo ?: $line->description,
                'debit' => round((float) $line->debit, 2),
                'credit' => round((float) $line->credit, 2),
                'running_balance' => $running,
            ];
        }

        return [
            'account' => [
                'id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_name_ar' => $account->account_name_ar,
                'account_type' => $account->account_type,
                'normal_balance' => $sign === 1 ? 'debit' : 'credit',
            ],
            'period' => ['from' => $from, 'to' => $to],
            'opening_balance' => $openingBalance,
            'lines' => $rows,
            'closing_balance' => $running,
        ];
    }

    /**
     * بنود نوع حساب معيّن بالرصيد الموجب حسب طبيعته.
     */
    private static function sectionFor(string $type, Collection $totals): array
    {
        $sign = in_array($type, self::DEBIT_NATURE, true) ? 1 : -1;

        return Account::where('account_type', $type)
            ->orderBy('account_code')
            ->get()
            ->map(function (Account $account) use ($totals, $sign) {
                $row = $totals->get($account->id);
                $amount = round((((float) ($row->total_debit ?? 0)) - ((float) ($row->total_credit ?? 0))) * $sign, 2);

                return [
                    'account_id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_name_ar' => $account->account_name_ar,
                    'amount' => $amount,
                ];
            })
            ->filter(fn ($line) => abs($line['amount']) > 0.001)
            ->values()
            ->all();
    }

    /**
     * أعمار الديون — buckets قياسية بالأيام من تاريخ الاستحقاق.
     * $rows لازم تحتوي: id, number, party, due_date, balance.
     */
    public static function bucketAging(Collection $rows, string $asOf): array
    {
        $buckets = ['current' => 0.0, '1_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, 'over_90' => 0.0];
        $asOfDate = \Illuminate\Support\Carbon::parse($asOf)->startOfDay();
        $detail = [];

        foreach ($rows as $row) {
            $balance = round((float) $row['balance'], 2);

            if ($balance <= 0.009) {
                continue;
            }

            // موجب = متأخر. محسوبة بالفرق بين التواريخ مباشرة عشان ما نعتمدش
            // على سلوك diffInDays اللي اختلف بين Carbon 2 و 3.
            $daysOverdue = $row['due_date']
                ? (int) round(
                    ($asOfDate->getTimestamp() - \Illuminate\Support\Carbon::parse($row['due_date'])->startOfDay()->getTimestamp())
                    / 86400
                )
                : 0;

            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1_30',
                $daysOverdue <= 60 => '31_60',
                $daysOverdue <= 90 => '61_90',
                default => 'over_90',
            };

            $buckets[$bucket] += $balance;

            $detail[] = [
                ...$row,
                'balance' => $balance,
                'days_overdue' => max(0, (int) $daysOverdue),
                'bucket' => $bucket,
            ];
        }

        return [
            'as_of' => $asOf,
            'buckets' => array_map(fn ($v) => round($v, 2), $buckets),
            'total' => round(array_sum($buckets), 2),
            'rows' => $detail,
        ];
    }
}
