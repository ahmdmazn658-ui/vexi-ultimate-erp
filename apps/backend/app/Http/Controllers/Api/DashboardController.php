<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SupplierBill;
use App\Models\Ticket;
use App\Support\Reports\FinancialReports;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * modules/dashboards — لوحة المؤشرات التنفيذية.
 * المؤشرات المالية بتيجي من نفس محرّك التقارير (FinancialReports) عشان
 * الرقم اللي في اللوحة يبقى هو نفسه الرقم اللي في القوائم المالية.
 */
class DashboardController extends Controller
{
    /** GET /api/v1/dashboards/summary */
    public function summary(Request $request): JsonResponse
    {
        $today = now();
        $startOfMonth = $today->copy()->startOfMonth()->toDateString();
        $startOfYear = $today->copy()->startOfYear()->toDateString();
        $todayStr = $today->toDateString();

        $mtd = FinancialReports::incomeStatement($startOfMonth, $todayStr);
        $ytd = FinancialReports::incomeStatement($startOfYear, $todayStr);

        return response()->json([
            'generated_at' => $today->toIso8601String(),

            'financial' => [
                'revenue_mtd' => $mtd['revenue']['total'],
                'expenses_mtd' => $mtd['expenses']['total'],
                'net_income_mtd' => $mtd['net_income'],
                'revenue_ytd' => $ytd['revenue']['total'],
                'expenses_ytd' => $ytd['expenses']['total'],
                'net_income_ytd' => $ytd['net_income'],
                'cash_balance' => $this->cashBalance(),
            ],

            'receivables' => [
                'outstanding' => $this->outstandingReceivables(),
                'overdue' => $this->overdueReceivables($todayStr),
                'overdue_count' => Invoice::where('status', 'issued')
                    ->whereDate('due_date', '<', $todayStr)
                    ->whereColumn('paid_amount', '<', 'total_amount')
                    ->count(),
            ],

            'payables' => [
                'outstanding' => $this->outstandingPayables(),
                'overdue_count' => SupplierBill::where('status', 'approved')
                    ->whereDate('due_date', '<', $todayStr)
                    ->whereColumn('paid_amount', '<', 'total_amount')
                    ->count(),
            ],

            'sales_trend' => $this->salesTrend(12),
            'top_customers' => $this->topCustomers($startOfYear, $todayStr),

            'operations' => [
                'active_projects' => Project::whereIn('status', ['planning', 'in_progress'])->count(),
                'open_purchase_orders' => PurchaseOrder::whereIn('status', ['submitted', 'approved'])->count(),
                'open_purchase_orders_value' => round((float) PurchaseOrder::whereIn('status', ['submitted', 'approved'])->sum('total_amount'), 2),
                'draft_invoices' => Invoice::where('status', 'draft')->count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'active_employees' => Employee::where('status', 'active')->count(),
            ],
        ]);
    }

    private function cashBalance(): float
    {
        return round(
            BankAccount::where('is_active', true)->get()
                ->sum(fn (BankAccount $account) => $account->currentBalance()),
            2
        );
    }

    private function outstandingReceivables(): float
    {
        return round((float) Invoice::where('status', 'issued')
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as balance')
            ->value('balance'), 2);
    }

    private function overdueReceivables(string $today): float
    {
        return round((float) Invoice::where('status', 'issued')
            ->whereDate('due_date', '<', $today)
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as balance')
            ->value('balance'), 2);
    }

    private function outstandingPayables(): float
    {
        return round((float) SupplierBill::where('status', 'approved')
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as balance')
            ->value('balance'), 2);
    }

    /**
     * مبيعات آخر N شهر من الفواتير الصادرة (بدون ضريبة).
     */
    private function salesTrend(int $months): array
    {
        $start = now()->copy()->subMonths($months - 1)->startOfMonth();

        // تعبير استخراج الشهر بيختلف حسب محرك قاعدة البيانات
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', invoice_date)"
            : "DATE_FORMAT(invoice_date, '%Y-%m')";

        $aggregates = Invoice::query()
            ->whereIn('status', ['issued', 'paid'])
            ->whereDate('invoice_date', '>=', $start->toDateString())
            ->selectRaw("{$monthExpr} as month, SUM(subtotal) as total, COUNT(*) as invoices")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $trend = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $trend[] = [
                'month' => $key,
                'label' => $month->translatedFormat('M Y'),
                'total' => round((float) ($aggregates[$key]->total ?? 0), 2),
                'invoices' => (int) ($aggregates[$key]->invoices ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * أعلى 5 عملاء بالإيراد خلال الفترة.
     */
    private function topCustomers(string $from, string $to): array
    {
        return Invoice::query()
            ->whereIn('status', ['issued', 'paid'])
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->groupBy('customer_id')
            ->select('customer_id', DB::raw('SUM(subtotal) as revenue'), DB::raw('COUNT(*) as invoices'))
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'customer_id' => $row->customer_id,
                'customer_name' => Customer::find($row->customer_id)?->name,
                'revenue' => round((float) $row->revenue, 2),
                'invoices' => (int) $row->invoices,
            ])
            ->all();
    }
}
