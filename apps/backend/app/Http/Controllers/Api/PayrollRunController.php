<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use App\Support\Accounting\DefaultAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollRunController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $runs = PayrollRun::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->withCount('payslips')
            ->latest('run_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($runs);
    }

    /**
     * POST /api/v1/payroll/runs
     * Body: { period: "2026-08", run_date, employee_ids?: [] }
     * بيولّد payslip لكل موظف active (أو اللي في employee_ids لو اتبعتت) بناءً على basic_salary.
     * الحالة draft لحد ما يتم /post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|string|max:20|unique:payroll_runs,period',
            'run_date' => 'required|date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $run = DB::transaction(function () use ($validated, $request) {
            $run = PayrollRun::create([
                'period' => $validated['period'],
                'run_date' => $validated['run_date'],
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            $employees = Employee::query()
                ->where('status', 'active')
                ->when(! empty($validated['employee_ids']), fn ($q) => $q->whereIn('id', $validated['employee_ids']))
                ->get();

            $total = 0;

            foreach ($employees as $employee) {
                $netPay = (float) $employee->basic_salary;

                $run->payslips()->create([
                    'employee_id' => $employee->id,
                    'basic_salary' => $employee->basic_salary,
                    'allowances' => 0,
                    'deductions' => 0,
                    'net_pay' => $netPay,
                ]);

                $total += $netPay;
            }

            $run->update(['total_amount' => $total]);

            return $run;
        });

        return response()->json($run->load('payslips.employee'), 201);
    }

    public function show(PayrollRun $run): JsonResponse
    {
        return response()->json($run->load('payslips.employee', 'journalEntry.lines.account'));
    }

    /**
     * POST /api/v1/payroll/runs/{id}/post
     * يرحّل الرواتب: يقفل الـ run كـ posted وينشئ قيد محاسبي واحد مُرحّل:
     *   مدين: مصروفات الرواتب (5200) = إجمالي الرواتب
     *   دائن: رواتب مستحقة الدفع (2200) = إجمالي الرواتب
     */
    public function post(Request $request, PayrollRun $run): JsonResponse
    {
        if ($run->status !== 'draft') {
            throw ValidationException::withMessages([
                'run' => ['دورة الرواتب دي اترحّلت بالفعل.'],
            ]);
        }

        if ($run->payslips()->count() === 0) {
            throw ValidationException::withMessages([
                'run' => ['مفيش payslips في الدورة دي، مينفعش تترحّل.'],
            ]);
        }

        $run = DB::transaction(function () use ($request, $run) {
            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-PAYROLL-'.$run->period,
                'entry_date' => $run->run_date,
                'reference' => 'PAYROLL-'.$run->period,
                'description' => "قيد رواتب فترة {$run->period}",
                'status' => 'posted',
                'created_by' => $request->user()?->id,
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::payrollExpense()->id,
                'debit' => $run->total_amount,
                'credit' => 0,
                'memo' => "مصروف رواتب - {$run->period}",
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::salariesPayable()->id,
                'debit' => 0,
                'credit' => $run->total_amount,
                'memo' => "رواتب مستحقة - {$run->period}",
            ]);

            $run->update([
                'status' => 'posted',
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $run;
        });

        return response()->json($run->fresh()->load('payslips.employee', 'journalEntry.lines.account'));
    }

    public function destroy(PayrollRun $run): JsonResponse
    {
        if ($run->status !== 'draft') {
            throw ValidationException::withMessages([
                'run' => ['مينفعش تحذف دورة رواتب اترحّلت بالفعل.'],
            ]);
        }

        $run->delete();

        return response()->json(null, 204);
    }
}
