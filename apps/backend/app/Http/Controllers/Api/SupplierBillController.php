<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use App\Models\SupplierBill;
use App\Support\Accounting\DefaultAccounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * فواتير الموردين — الطرف المقابل لـ InvoiceController في دورة المشتريات.
 * الفاتورة بتتنشئ draft، وعند /approve بيتولد قيد مُرحّل:
 *   مدين: حساب المصروف/الأصل   = subtotal
 *   مدين: ضريبة مدخلات (1170)  = vat_amount
 *   دائن: الذمم الدائنة (2100) = total_amount
 */
class SupplierBillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bills = SupplierBill::query()
            ->when($request->query('supplier_id'), fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->boolean('overdue'), fn ($q) => $q
                ->whereIn('status', ['approved'])
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereColumn('paid_amount', '<', 'total_amount'))
            ->with('supplier:id,name,supplier_code')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($bills);
    }

    /**
     * POST /api/v1/procurement/supplier-bills
     * Body: { supplier_id, purchase_order_id?, project_id?, bill_date, due_date?,
     *         supplier_invoice_no?, vat_rate?, expense_account_id?,
     *         items: [{product_id?, item_name, unit?, quantity, unit_price}] }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'project_id' => 'nullable|exists:projects,id',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $bill = DB::transaction(function () use ($validated, $request) {
            $bill = SupplierBill::create([
                'bill_number' => 'DRAFT-'.Str::upper(Str::random(10)),
                'supplier_id' => $validated['supplier_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'] ?? null,
                'vat_rate' => $validated['vat_rate'] ?? 15.00,
                'expense_account_id' => $validated['expense_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $item) {
                $bill->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $bill->recalculateTotals();

            return $bill;
        });

        return response()->json($bill->load('items', 'supplier'), 201);
    }

    /**
     * POST /api/v1/procurement/supplier-bills/from-purchase-order/{purchaseOrder}
     * ينشئ فاتورة مورد draft من أمر شراء (approved أو received) بنفس البنود.
     */
    public function fromPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (! in_array($purchaseOrder->status, ['approved', 'received'], true)) {
            throw ValidationException::withMessages([
                'purchase_order' => ['لازم يكون أمر الشراء approved أو received عشان تتولد منه فاتورة مورد.'],
            ]);
        }

        if (SupplierBill::where('purchase_order_id', $purchaseOrder->id)->whereNot('status', 'cancelled')->exists()) {
            throw ValidationException::withMessages([
                'purchase_order' => ['أمر الشراء ده متولّد منه فاتورة بالفعل.'],
            ]);
        }

        $bill = DB::transaction(function () use ($purchaseOrder, $request) {
            $bill = SupplierBill::create([
                'bill_number' => 'DRAFT-'.Str::upper(Str::random(10)),
                'supplier_id' => $purchaseOrder->supplier_id,
                'purchase_order_id' => $purchaseOrder->id,
                'project_id' => $purchaseOrder->project_id,
                'bill_date' => now()->toDateString(),
                'vat_rate' => 15.00,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($purchaseOrder->items as $item) {
                $bill->items()->create([
                    'item_name' => $item->item_name,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            $bill->recalculateTotals();

            return $bill;
        });

        return response()->json($bill->load('items', 'supplier'), 201);
    }

    public function show(SupplierBill $supplierBill): JsonResponse
    {
        return response()->json($supplierBill->load(
            'items.product', 'supplier', 'purchaseOrder', 'project:id,name',
            'expenseAccount:id,account_code,account_name', 'journalEntry.lines.account',
            'allocations.payment:id,payment_number,payment_date,amount,status'
        ));
    }

    /**
     * POST /api/v1/procurement/supplier-bills/{id}/approve
     * يعتمد الفاتورة ويولّد قيد الشراء المُرحّل في دفتر الأستاذ.
     */
    public function approve(Request $request, SupplierBill $supplierBill): JsonResponse
    {
        if ($supplierBill->status !== 'draft') {
            throw ValidationException::withMessages([
                'bill' => ['الفاتورة دي مش draft، مينفعش تتعتمد تاني.'],
            ]);
        }

        $supplierBill = DB::transaction(function () use ($request, $supplierBill) {
            $billDate = $supplierBill->bill_date ?? now();
            $billNumber = 'BILL-'.$billDate->format('Y').'-'.str_pad((string) $supplierBill->id, 6, '0', STR_PAD_LEFT);

            $expenseAccount = $supplierBill->expense_account_id
                ? $supplierBill->expenseAccount
                : DefaultAccounts::generalExpense();

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-'.$billNumber,
                'entry_date' => $billDate->toDateString(),
                'project_id' => $supplierBill->project_id,
                'reference' => $billNumber,
                'description' => "قيد فاتورة مشتريات رقم {$billNumber}",
                'status' => 'posted',
                'created_by' => $request->user()?->id,
            ]);

            $journalEntry->lines()->create([
                'account_id' => $expenseAccount->id,
                'debit' => $supplierBill->subtotal,
                'credit' => 0,
                'memo' => "مشتريات - فاتورة {$billNumber}",
            ]);

            if ((float) $supplierBill->vat_amount > 0) {
                $journalEntry->lines()->create([
                    'account_id' => DefaultAccounts::vatReceivable()->id,
                    'debit' => $supplierBill->vat_amount,
                    'credit' => 0,
                    'memo' => "ضريبة مدخلات - فاتورة {$billNumber}",
                ]);
            }

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::accountsPayable()->id,
                'debit' => 0,
                'credit' => $supplierBill->total_amount,
                'memo' => "مستحق للمورد - فاتورة {$billNumber}",
            ]);

            $supplierBill->update([
                'bill_number' => $billNumber,
                'status' => 'approved',
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $supplierBill;
        });

        return response()->json($supplierBill->fresh()->load('items', 'supplier', 'journalEntry.lines.account'));
    }

    public function update(Request $request, SupplierBill $supplierBill): JsonResponse
    {
        if ($supplierBill->status !== 'draft') {
            throw ValidationException::withMessages([
                'bill' => ['مينفعش تعدّل فاتورة معتمدة — اعمل إشعار دائن أو ألغِها.'],
            ]);
        }

        $validated = $request->validate([
            'supplier_invoice_no' => 'nullable|string|max:100',
            'due_date' => 'nullable|date',
            'vat_rate' => 'sometimes|numeric|min:0|max:100',
            'expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
        ]);

        $supplierBill->update($validated);

        if (array_key_exists('vat_rate', $validated)) {
            $supplierBill->recalculateTotals();
        }

        return response()->json($supplierBill->fresh());
    }

    public function destroy(SupplierBill $supplierBill): JsonResponse
    {
        if ($supplierBill->status !== 'draft') {
            throw ValidationException::withMessages([
                'bill' => ['لا يمكن حذف فاتورة معتمدة.'],
            ]);
        }

        $supplierBill->delete();

        return response()->json(null, 204);
    }
}
