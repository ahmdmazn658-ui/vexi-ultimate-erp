<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\SupplierBill;
use App\Support\Reports\FinancialReports;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * reports/financial — المصدر الوحيد للتقارير المالية.
 * كل التقارير محسوبة من القيود المُرحّلة في دفتر الأستاذ.
 */
class ReportController extends Controller
{
    /** GET /api/v1/reports/trial-balance?from=&to=&project_id= */
    public function trialBalance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        return response()->json(FinancialReports::trialBalance(
            $validated['from'] ?? null,
            $validated['to'] ?? now()->toDateString(),
            isset($validated['project_id']) ? (int) $validated['project_id'] : null
        ));
    }

    /** GET /api/v1/reports/income-statement?from=&to=&project_id= */
    public function incomeStatement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        return response()->json(FinancialReports::incomeStatement(
            $validated['from'] ?? now()->startOfYear()->toDateString(),
            $validated['to'] ?? now()->toDateString(),
            isset($validated['project_id']) ? (int) $validated['project_id'] : null
        ));
    }

    /** GET /api/v1/reports/balance-sheet?as_of=&project_id= */
    public function balanceSheet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'as_of' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        return response()->json(FinancialReports::balanceSheet(
            $validated['as_of'] ?? now()->toDateString(),
            isset($validated['project_id']) ? (int) $validated['project_id'] : null
        ));
    }

    /** GET /api/v1/reports/general-ledger/{account}?from=&to=&project_id= */
    public function generalLedger(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        return response()->json(FinancialReports::generalLedger(
            $account,
            $validated['from'] ?? now()->startOfYear()->toDateString(),
            $validated['to'] ?? now()->toDateString(),
            isset($validated['project_id']) ? (int) $validated['project_id'] : null
        ));
    }

    /** GET /api/v1/reports/ar-aging?as_of= — أعمار الذمم المدينة (عملاء) */
    public function arAging(Request $request): JsonResponse
    {
        $asOf = $request->date('as_of')?->toDateString() ?? now()->toDateString();

        $rows = Invoice::query()
            ->whereIn('status', ['issued', 'paid'])
            ->whereDate('invoice_date', '<=', $asOf)
            ->with('customer:id,name,customer_code')
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'number' => $invoice->invoice_number,
                'party' => $invoice->customer?->name,
                'party_id' => $invoice->customer_id,
                'issue_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'total_amount' => (float) $invoice->total_amount,
                'balance' => $invoice->balance_due,
            ]);

        return response()->json(FinancialReports::bucketAging($rows, $asOf));
    }

    /** GET /api/v1/reports/ap-aging?as_of= — أعمار الذمم الدائنة (موردون) */
    public function apAging(Request $request): JsonResponse
    {
        $asOf = $request->date('as_of')?->toDateString() ?? now()->toDateString();

        $rows = SupplierBill::query()
            ->whereIn('status', ['approved', 'paid'])
            ->whereDate('bill_date', '<=', $asOf)
            ->with('supplier:id,name,supplier_code')
            ->get()
            ->map(fn (SupplierBill $bill) => [
                'id' => $bill->id,
                'number' => $bill->bill_number,
                'party' => $bill->supplier?->name,
                'party_id' => $bill->supplier_id,
                'issue_date' => $bill->bill_date?->toDateString(),
                'due_date' => $bill->due_date?->toDateString(),
                'total_amount' => (float) $bill->total_amount,
                'balance' => $bill->balance_due,
            ]);

        return response()->json(FinancialReports::bucketAging($rows, $asOf));
    }
}
