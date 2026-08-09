<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Support\Accounting\DefaultAccounts;
use App\Support\Reports\FinancialReports;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * accounting/period-closing — إدارة الفترات المحاسبية والإقفال السنوي.
 */
class AccountingPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $periods = AccountingPeriod::query()
            ->when($request->query('fiscal_year'), fn ($q, $y) => $q->where('fiscal_year', $y))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('closedBy:id,name')
            ->orderBy('fiscal_year')
            ->orderBy('period_number')
            ->get();

        return response()->json(['data' => $periods]);
    }

    /**
     * POST /api/v1/accounting/periods/generate
     * Body: { fiscal_year }
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
        ]);

        $created = AccountingPeriod::generateForYear((int) $validated['fiscal_year']);

        return response()->json([
            'message' => "تم إنشاء {$created} فترة جديدة للسنة المالية {$validated['fiscal_year']}.",
            'created' => $created,
            'data' => AccountingPeriod::where('fiscal_year', $validated['fiscal_year'])
                ->orderBy('period_number')
                ->get(),
        ], 201);
    }

    /**
     * POST /api/v1/accounting/periods/{period}/close
     * بيرفض الإقفال لو لسه في قيود مسودة داخل الفترة — عشان ما تضيعش.
     */
    public function close(Request $request, AccountingPeriod $period): JsonResponse
    {
        if ($period->isClosed()) {
            throw ValidationException::withMessages([
                'period' => ['الفترة دي مقفلة بالفعل.'],
            ]);
        }

        $draftCount = JournalEntry::where('status', 'draft')
            ->whereDate('entry_date', '>=', $period->start_date)
            ->whereDate('entry_date', '<=', $period->end_date)
            ->count();

        if ($draftCount > 0) {
            throw ValidationException::withMessages([
                'period' => [
                    "في {$draftCount} قيد مسودة داخل الفترة — رحّلهم أو احذفهم الأول قبل الإقفال.",
                ],
            ]);
        }

        $period->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $request->user()?->id,
            'notes' => $request->input('notes'),
        ]);

        return response()->json($period->fresh()->load('closedBy:id,name'));
    }

    /**
     * POST /api/v1/accounting/periods/{period}/reopen
     * إعادة فتح فترة — إجراء حساس، محصور على admin في الراوت.
     */
    public function reopen(Request $request, AccountingPeriod $period): JsonResponse
    {
        if (! $period->isClosed()) {
            throw ValidationException::withMessages([
                'period' => ['الفترة دي مفتوحة أصلاً.'],
            ]);
        }

        $period->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
            'notes' => $request->input('notes'),
        ]);

        return response()->json($period->fresh());
    }

    /**
     * POST /api/v1/accounting/periods/year-end-closing
     * Body: { fiscal_year }
     *
     * قيد الإقفال السنوي: بيصفّر حسابات الإيرادات والمصروفات وينقل صافي
     * النتيجة للأرباح المحتجزة (3200).
     *   مدين: كل حساب إيراد برصيده
     *   دائن: كل حساب مصروف برصيده
     *   الفرق: الأرباح المحتجزة (دائن لو ربح، مدين لو خسارة)
     *
     * بيتنفّذ داخل withoutGuard لأن تاريخه آخر يوم في السنة، واللي غالباً
     * بيبقى في فترة اتقفلت بالفعل.
     */
    public function yearEndClosing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100',
        ]);

        $fiscalYear = (int) $validated['fiscal_year'];
        $from = Carbon::create($fiscalYear, 1, 1)->toDateString();
        $to = Carbon::create($fiscalYear, 12, 31)->toDateString();
        $entryNumber = "JE-CLOSING-{$fiscalYear}";

        if (JournalEntry::where('entry_number', $entryNumber)->exists()) {
            throw ValidationException::withMessages([
                'fiscal_year' => ["السنة المالية {$fiscalYear} متقفلة بالفعل — قيد الإقفال موجود."],
            ]);
        }

        $statement = FinancialReports::incomeStatement($from, $to);

        if (count($statement['revenue']['lines']) === 0 && count($statement['expenses']['lines']) === 0) {
            throw ValidationException::withMessages([
                'fiscal_year' => ["مفيش أي إيرادات أو مصروفات مُرحّلة في {$fiscalYear} عشان تتقفل."],
            ]);
        }

        $entry = AccountingPeriod::withoutGuard(function () use (
            $statement, $to, $entryNumber, $fiscalYear, $request
        ) {
            return DB::transaction(function () use (
                $statement, $to, $entryNumber, $fiscalYear, $request
            ) {
                $entry = JournalEntry::create([
                    'entry_number' => $entryNumber,
                    'entry_date' => $to,
                    'reference' => (string) $fiscalYear,
                    'description' => "قيد إقفال السنة المالية {$fiscalYear}",
                    'status' => 'posted',
                    'created_by' => $request->user()?->id,
                ]);

                // تصفير الإيرادات: رصيدها دائن، فبنجعلها مدينة
                foreach ($statement['revenue']['lines'] as $line) {
                    $entry->lines()->create([
                        'account_id' => $line['account_id'],
                        'debit' => $line['amount'],
                        'credit' => 0,
                        'memo' => "إقفال إيرادات {$fiscalYear}",
                    ]);
                }

                // تصفير المصروفات: رصيدها مدين، فبنجعلها دائنة
                foreach ($statement['expenses']['lines'] as $line) {
                    $entry->lines()->create([
                        'account_id' => $line['account_id'],
                        'debit' => 0,
                        'credit' => $line['amount'],
                        'memo' => "إقفال مصروفات {$fiscalYear}",
                    ]);
                }

                // صافي النتيجة للأرباح المحتجزة
                $netIncome = round((float) $statement['net_income'], 2);
                $retainedEarnings = DefaultAccounts::retainedEarnings();

                if (abs($netIncome) > 0.001) {
                    $entry->lines()->create([
                        'account_id' => $retainedEarnings->id,
                        'debit' => $netIncome < 0 ? abs($netIncome) : 0,
                        'credit' => $netIncome > 0 ? $netIncome : 0,
                        'memo' => $netIncome >= 0
                            ? "صافي ربح {$fiscalYear}"
                            : "صافي خسارة {$fiscalYear}",
                    ]);
                }

                if (! $entry->fresh()->isBalanced()) {
                    throw ValidationException::withMessages([
                        'fiscal_year' => ['قيد الإقفال طلع غير متوازن — راجع أرصدة الحسابات.'],
                    ]);
                }

                return $entry;
            });
        });

        return response()->json([
            'message' => "تم إقفال السنة المالية {$fiscalYear}.",
            'net_income' => $statement['net_income'],
            'entry' => $entry->fresh()->load('lines.account:id,account_code,account_name,account_name_ar'),
        ], 201);
    }

    /**
     * GET /api/v1/accounting/periods/status?date=YYYY-MM-DD
     * فحص سريع تستخدمه الواجهة قبل ما تسمح بإجراء ليه أثر محاسبي.
     */
    public function status(Request $request): JsonResponse
    {
        $date = $request->query('date') ?? now()->toDateString();
        $period = AccountingPeriod::forDate($date);

        return response()->json([
            'date' => $date,
            'period' => $period,
            'is_closed' => $period?->isClosed() ?? false,
            'is_defined' => $period !== null,
        ]);
    }
}
