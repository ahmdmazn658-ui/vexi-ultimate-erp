<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Support\Accounting\DefaultAccounts;
use App\Support\Zatca\QrCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('customer:id,name,customer_code')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($invoices);
    }

    /**
     * POST /api/v1/e-invoicing/invoices
     * Body: { customer_id, sales_order_id?, due_date?, vat_rate?, items: [{product_id?, item_name, quantity, unit_price}] }
     * الفاتورة بتتنشئ كـ draft، لسه محتاجة /issue عشان يتولد ليها رقم رسمي وQR.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'due_date' => 'nullable|date',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated, $request) {
            $invoice = Invoice::create([
                'invoice_number' => 'DRAFT-'.Str::upper(Str::random(10)),
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'vat_rate' => $validated['vat_rate'] ?? 15.00,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $invoice->recalculateTotals();

            return $invoice;
        });

        return response()->json($invoice->load('items', 'customer'), 201);
    }

    /**
     * POST /api/v1/e-invoicing/invoices/from-sales-order/{salesOrder}
     * ينشئ فاتورة draft من طلب بيع (confirmed أو delivered) بنفس البنود.
     */
    public function fromSalesOrder(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        if (! in_array($salesOrder->status, ['confirmed', 'delivered'], true)) {
            throw ValidationException::withMessages([
                'sales_order' => ['لازم يكون الطلب confirmed أو delivered عشان تتولد منه فاتورة.'],
            ]);
        }

        $invoice = DB::transaction(function () use ($salesOrder, $request) {
            $invoice = Invoice::create([
                'invoice_number' => 'DRAFT-'.Str::upper(Str::random(10)),
                'customer_id' => $salesOrder->customer_id,
                'sales_order_id' => $salesOrder->id,
                'vat_rate' => 15.00,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($salesOrder->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            $invoice->recalculateTotals();

            return $invoice;
        });

        return response()->json($invoice->load('items', 'customer'), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('items.product', 'customer', 'salesOrder', 'journalEntry.lines.account'));
    }

    /**
     * POST /api/v1/e-invoicing/invoices/{id}/issue
     * يولّد رقم فاتورة رسمي + QR بصيغة ZATCA (TLV/Base64)، ويقفل الفاتورة كـ issued،
     * وينشئ تلقائياً قيد محاسبي مُرحّل (posted) في دفتر الأستاذ:
     *   مدين: الذمم المدينة (1130)   = total_amount
     *   دائن: إيرادات المبيعات (4100) = subtotal
     *   دائن: ضريبة القيمة المضافة المستحقة (2160) = vat_amount  (لو أكبر من صفر)
     */
    public function issue(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages([
                'invoice' => ['الفاتورة دي مش draft، مينفعش تصدر تاني.'],
            ]);
        }

        $invoice = DB::transaction(function () use ($request, $invoice) {
            $invoiceDate = now();

            $qrCode = QrCodeGenerator::generate(
                sellerName: (string) config('company.name'),
                vatNumber: (string) config('company.vat_number'),
                timestamp: $invoiceDate->toIso8601String(),
                totalWithVat: number_format((float) $invoice->total_amount, 2, '.', ''),
                vatTotal: number_format((float) $invoice->vat_amount, 2, '.', '')
            );

            $invoiceNumber = 'INV-'.$invoiceDate->format('Y').'-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-'.$invoiceNumber,
                'entry_date' => $invoiceDate->toDateString(),
                'reference' => $invoiceNumber,
                'description' => "قيد فاتورة مبيعات رقم {$invoiceNumber}",
                'status' => 'posted',
                'created_by' => $request->user()?->id,
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::accountsReceivable()->id,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'memo' => "فاتورة {$invoiceNumber}",
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::salesRevenue()->id,
                'debit' => 0,
                'credit' => $invoice->subtotal,
                'memo' => "إيراد مبيعات - فاتورة {$invoiceNumber}",
            ]);

            if ((float) $invoice->vat_amount > 0) {
                $journalEntry->lines()->create([
                    'account_id' => DefaultAccounts::vatPayable()->id,
                    'debit' => 0,
                    'credit' => $invoice->vat_amount,
                    'memo' => "ضريبة قيمة مضافة - فاتورة {$invoiceNumber}",
                ]);
            }

            $invoice->update([
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate->toDateString(),
                'status' => 'issued',
                'qr_code' => $qrCode,
                'zatca_uuid' => (string) Str::uuid(),
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $invoice;
        });

        return response()->json($invoice->fresh()->load('items', 'customer', 'journalEntry.lines.account'));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages([
                'invoice' => ['لا يمكن حذف فاتورة تم إصدارها بالفعل.'],
            ]);
        }

        $invoice->delete();

        return response()->json(null, 204);
    }
}
