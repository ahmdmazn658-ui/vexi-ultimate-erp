<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\SupplierBill;
use App\Support\Accounting\DefaultAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * سندات القبض والصرف — الحلقة اللي كانت ناقصة بين الفواتير ودفتر الأستاذ والبنك.
 *
 * سند قبض (receipt):   مدين: البنك/الصندوق        دائن: الذمم المدينة (1130)
 * سند صرف (payment):   مدين: الذمم الدائنة (2100)  دائن: البنك/الصندوق
 *
 * كل سند بيولّد كمان حركة بنكية (لو محدد حساب بنكي) وبيحدّث paid_amount
 * على الفواتير المخصّص عليها.
 */
class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->query('supplier_id'), fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->query('bank_account_id'), fn ($q, $id) => $q->where('bank_account_id', $id))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->with([
                'customer:id,name,customer_code',
                'supplier:id,name,supplier_code',
                'bankAccount:id,bank_name,account_name',
            ])
            ->latest('payment_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($payments);
    }

    /**
     * POST /api/v1/finance/payments
     * Body: {
     *   type: receipt|payment, customer_id?|supplier_id?, bank_account_id?,
     *   payment_date, amount, method?, reference?, notes?,
     *   allocations?: [{ target_id, amount }]   // فواتير مبيعات للقبض، فواتير موردين للصرف
     * }
     * لو مفيش allocations بيتسجّل السند كدفعة على الحساب (unallocated).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment',
            'customer_id' => 'required_if:type,receipt|nullable|exists:customers,id',
            'supplier_id' => 'required_if:type,payment|nullable|exists:suppliers,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|in:bank_transfer,cash,cheque,card,other',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.target_id' => 'required|integer',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $allocations = $validated['allocations'] ?? [];
        $allocatedTotal = round(array_sum(array_column($allocations, 'amount')), 2);

        if ($allocatedTotal > round((float) $validated['amount'], 2)) {
            throw ValidationException::withMessages([
                'allocations' => ['إجمالي المبالغ الموزّعة على الفواتير أكبر من قيمة السند.'],
            ]);
        }

        $isReceipt = $validated['type'] === 'receipt';
        $targets = $this->resolveTargets($isReceipt, $allocations, $validated);

        $payment = DB::transaction(function () use ($validated, $allocations, $allocatedTotal, $isReceipt, $targets, $request) {
            $date = \Illuminate\Support\Carbon::parse($validated['payment_date']);
            $prefix = $isReceipt ? 'RCV' : 'PAY';

            $payment = Payment::create([
                'payment_number' => 'TMP-'.uniqid(),
                'type' => $validated['type'],
                'customer_id' => $isReceipt ? $validated['customer_id'] : null,
                'supplier_id' => $isReceipt ? null : $validated['supplier_id'],
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'payment_date' => $date->toDateString(),
                'amount' => $validated['amount'],
                'allocated_amount' => $allocatedTotal,
                'method' => $validated['method'] ?? 'bank_transfer',
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'posted',
                'created_by' => $request->user()?->id,
            ]);

            $paymentNumber = $prefix.'-'.$date->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);

            // ── القيد المحاسبي ────────────────────────────────────────
            $cashAccountId = $this->cashAccountId($payment);

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-'.$paymentNumber,
                'entry_date' => $date->toDateString(),
                'reference' => $paymentNumber,
                'description' => $isReceipt
                    ? "قيد سند قبض رقم {$paymentNumber}"
                    : "قيد سند صرف رقم {$paymentNumber}",
                'status' => 'posted',
                'created_by' => $request->user()?->id,
            ]);

            $counterAccountId = $isReceipt
                ? DefaultAccounts::accountsReceivable()->id
                : DefaultAccounts::accountsPayable()->id;

            $journalEntry->lines()->create([
                'account_id' => $isReceipt ? $cashAccountId : $counterAccountId,
                'debit' => $payment->amount,
                'credit' => 0,
                'memo' => $paymentNumber,
            ]);

            $journalEntry->lines()->create([
                'account_id' => $isReceipt ? $counterAccountId : $cashAccountId,
                'debit' => 0,
                'credit' => $payment->amount,
                'memo' => $paymentNumber,
            ]);

            // ── الحركة البنكية ────────────────────────────────────────
            $bankTransaction = null;

            if ($payment->bank_account_id) {
                $bankTransaction = BankTransaction::create([
                    'bank_account_id' => $payment->bank_account_id,
                    'transaction_date' => $date->toDateString(),
                    'type' => $isReceipt ? 'deposit' : 'withdrawal',
                    'amount' => $payment->amount,
                    'reference' => $payment->reference ?? $paymentNumber,
                    'description' => $paymentNumber,
                    'created_by' => $request->user()?->id,
                ]);
            }

            $payment->update([
                'payment_number' => $paymentNumber,
                'journal_entry_id' => $journalEntry->id,
                'bank_transaction_id' => $bankTransaction?->id,
            ]);

            // ── التخصيص على الفواتير ──────────────────────────────────
            foreach ($allocations as $allocation) {
                $target = $targets[$allocation['target_id']];

                $payment->allocations()->create([
                    'allocatable_type' => $target::class,
                    'allocatable_id' => $target->id,
                    'amount' => $allocation['amount'],
                ]);
            }

            foreach ($targets as $target) {
                $target->refreshPaidAmount();
            }

            return $payment;
        });

        return response()->json(
            $payment->fresh()->load('allocations.allocatable', 'customer', 'supplier', 'bankAccount', 'journalEntry.lines.account'),
            201
        );
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load(
            'allocations.allocatable', 'customer', 'supplier', 'bankAccount',
            'journalEntry.lines.account', 'bankTransaction', 'creator:id,name'
        ));
    }

    /**
     * POST /api/v1/finance/payments/{payment}/void
     * إلغاء السند: قيد عكسي + حركة بنكية عكسية + إرجاع أرصدة الفواتير.
     * (ما بنحذفش القيد الأصلي حفاظاً على مسار التدقيق.)
     */
    public function void(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->status !== 'posted') {
            throw ValidationException::withMessages([
                'payment' => ['السند ده مش مُرحّل، مفيش حاجة تتلغى.'],
            ]);
        }

        DB::transaction(function () use ($request, $payment) {
            $original = $payment->journalEntry;

            if ($original) {
                $reversal = JournalEntry::create([
                    'entry_number' => 'JE-REV-'.$payment->payment_number,
                    'entry_date' => now()->toDateString(),
                    'reference' => $payment->payment_number,
                    'description' => "قيد عكسي لإلغاء السند {$payment->payment_number}",
                    'status' => 'posted',
                    'created_by' => $request->user()?->id,
                ]);

                foreach ($original->lines as $line) {
                    $reversal->lines()->create([
                        'account_id' => $line->account_id,
                        'debit' => $line->credit,
                        'credit' => $line->debit,
                        'memo' => "عكس: {$line->memo}",
                    ]);
                }

                $original->update(['status' => 'reversed']);
            }

            if ($payment->bank_account_id && $payment->bankTransaction) {
                BankTransaction::create([
                    'bank_account_id' => $payment->bank_account_id,
                    'transaction_date' => now()->toDateString(),
                    'type' => $payment->isReceipt() ? 'withdrawal' : 'deposit',
                    'amount' => $payment->amount,
                    'reference' => $payment->payment_number,
                    'description' => "عكس سند {$payment->payment_number}",
                    'created_by' => $request->user()?->id,
                ]);
            }

            $targets = $payment->allocations()->with('allocatable')->get()
                ->pluck('allocatable')->filter();

            $payment->update(['status' => 'cancelled']);

            foreach ($targets as $target) {
                $target->refreshPaidAmount();
            }
        });

        return response()->json($payment->fresh()->load('allocations.allocatable'));
    }

    /**
     * يتحقق إن كل فاتورة في التخصيصات موجودة، تخص نفس الطرف، قابلة للسداد،
     * والمبلغ المخصّص ما يتجاوزش المتبقي عليها.
     *
     * @return array<int, Invoice|SupplierBill>
     */
    private function resolveTargets(bool $isReceipt, array $allocations, array $validated): array
    {
        $targets = [];

        foreach ($allocations as $allocation) {
            $id = $allocation['target_id'];

            if ($isReceipt) {
                $target = Invoice::find($id);

                if (! $target) {
                    throw ValidationException::withMessages([
                        'allocations' => ["الفاتورة رقم {$id} غير موجودة."],
                    ]);
                }

                if ((int) $target->customer_id !== (int) $validated['customer_id']) {
                    throw ValidationException::withMessages([
                        'allocations' => ["الفاتورة {$target->invoice_number} مش تابعة للعميل المحدد."],
                    ]);
                }

                if (! in_array($target->status, ['issued', 'paid'], true)) {
                    throw ValidationException::withMessages([
                        'allocations' => ["الفاتورة {$target->invoice_number} لازم تكون صادرة (issued) عشان يتم تحصيلها."],
                    ]);
                }
            } else {
                $target = SupplierBill::find($id);

                if (! $target) {
                    throw ValidationException::withMessages([
                        'allocations' => ["فاتورة المورد رقم {$id} غير موجودة."],
                    ]);
                }

                if ((int) $target->supplier_id !== (int) $validated['supplier_id']) {
                    throw ValidationException::withMessages([
                        'allocations' => ["الفاتورة {$target->bill_number} مش تابعة للمورد المحدد."],
                    ]);
                }

                if (! in_array($target->status, ['approved', 'paid'], true)) {
                    throw ValidationException::withMessages([
                        'allocations' => ["الفاتورة {$target->bill_number} لازم تكون معتمدة (approved) عشان تتسدد."],
                    ]);
                }
            }

            if (round((float) $allocation['amount'], 2) > round($target->balance_due, 2) + 0.009) {
                throw ValidationException::withMessages([
                    'allocations' => ['المبلغ المخصّص أكبر من المتبقي على الفاتورة رقم '.$id.'.'],
                ]);
            }

            $targets[$id] = $target;
        }

        return $targets;
    }

    /** حساب النقدية/البنك في الشجرة — من الحساب المربوط بالبنك أو الصندوق الافتراضي. */
    private function cashAccountId(Payment $payment): int
    {
        $linked = $payment->bankAccount?->account_id;

        return $linked ?: DefaultAccounts::cashAndBank()->id;
    }
}
