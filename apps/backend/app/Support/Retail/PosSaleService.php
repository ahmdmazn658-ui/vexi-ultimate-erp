<?php

namespace App\Support\Retail;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Retail\PosSale;
use App\Models\Retail\RegisterSession;
use App\Models\StockMovement;
use App\Support\Accounting\DefaultAccounts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * منطق نقطة البيع (POS checkout) — عملية واحدة ذرية بتعمل كل ده مع بعض:
 *  1) تتأكد إن الشيفت (register session) مفتوح.
 *  2) تصرف المخزون فعليًا (StockMovement out) وترفض لو الكمية مش كافية.
 *  3) تولّد Invoice حقيقي (paid فورًا، زي أي بيع نقطة بيع) بدل إعادة اختراع
 *     مفهوم فاتورة جديد — نفس مصدر الحقيقة المستخدم في e-invoicing وHotel.
 *  4) تولّد قيد محاسبي واحد مُرحّل بأربع/خمس بنود:
 *     مدين نقدية = الإجمالي   |  دائن إيراد مبيعات = الصافي  | دائن ضريبة = الضريبة
 *     مدين تكلفة البضاعة المباعة = التكلفة  |  دائن المخزون = التكلفة
 *     (أول استخدام فعلي لحسابي COGS/Inventory المعرّفين مسبقًا في DefaultAccounts).
 */
class PosSaleService
{
    public function checkout(RegisterSession $session, array $items, ?int $customerId, string $paymentMethod, ?int $userId): PosSale
    {
        if ($session->status !== 'open') {
            throw ValidationException::withMessages([
                'pos_register_session_id' => ['الشيفت ده مقفول — لازم شيفت مفتوح عشان تسجّل بيع.'],
            ]);
        }

        return DB::transaction(function () use ($session, $items, $customerId, $paymentMethod, $userId) {
            $lines = [];
            $subtotal = 0;
            $costTotal = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ((float) $product->quantity_on_hand < (float) $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["الكمية المتاحة من \"{$product->name}\" غير كافية لإتمام البيع."],
                    ]);
                }

                $unitPrice = $item['unit_price'] ?? (float) $product->sale_price;
                $lineTotal = round($unitPrice * $item['quantity'], 2);
                $unitCost = (float) $product->cost_price;

                $product->decrement('quantity_on_hand', $item['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $session->warehouse_id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_type' => PosSale::class,
                    'notes' => 'بيع نقطة بيع - شيفت #'.$session->id,
                    'created_by' => $userId,
                ]);

                $subtotal += $lineTotal;
                $costTotal += round($unitCost * $item['quantity'], 2);

                $lines[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ];
            }

            $vatRate = 15.00;
            $vatAmount = round($subtotal * ($vatRate / 100), 2);
            $totalAmount = $subtotal + $vatAmount;
            $saleNumber = 'POS-'.now()->format('Y').'-'.Str::upper(Str::random(6));

            $invoice = Invoice::create([
                'invoice_number' => $saleNumber,
                'customer_id' => $customerId,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'vat_rate' => $vatRate,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'status' => 'paid',
                'notes' => 'فاتورة نقطة بيع — شيفت #'.$session->id,
                'created_by' => $userId,
            ]);

            foreach ($lines as $line) {
                $invoice->items()->create([
                    'product_id' => $line['product']->id,
                    'item_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }

            $journalEntry = JournalEntry::create([
                'entry_number' => 'JE-'.$saleNumber,
                'entry_date' => now()->toDateString(),
                'reference' => $saleNumber,
                'description' => "قيد بيع نقطة بيع رقم {$saleNumber}",
                'status' => 'posted',
                'created_by' => $userId,
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::cashAndBank()->id,
                'debit' => $totalAmount,
                'credit' => 0,
                'memo' => "تحصيل نقدي - {$saleNumber}",
            ]);

            $journalEntry->lines()->create([
                'account_id' => DefaultAccounts::salesRevenue()->id,
                'debit' => 0,
                'credit' => $subtotal,
                'memo' => "إيراد بيع - {$saleNumber}",
            ]);

            if ($vatAmount > 0) {
                $journalEntry->lines()->create([
                    'account_id' => DefaultAccounts::vatPayable()->id,
                    'debit' => 0,
                    'credit' => $vatAmount,
                    'memo' => "ضريبة قيمة مضافة - {$saleNumber}",
                ]);
            }

            if ($costTotal > 0) {
                $journalEntry->lines()->create([
                    'account_id' => DefaultAccounts::costOfGoodsSold()->id,
                    'debit' => $costTotal,
                    'credit' => 0,
                    'memo' => "تكلفة البضاعة المباعة - {$saleNumber}",
                ]);

                $journalEntry->lines()->create([
                    'account_id' => DefaultAccounts::inventory()->id,
                    'debit' => 0,
                    'credit' => $costTotal,
                    'memo' => "صرف مخزون - {$saleNumber}",
                ]);
            }

            $invoice->update(['journal_entry_id' => $journalEntry->id]);

            $sale = PosSale::create([
                'pos_register_session_id' => $session->id,
                'customer_id' => $customerId,
                'invoice_id' => $invoice->id,
                'journal_entry_id' => $journalEntry->id,
                'sale_number' => $saleNumber,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'cost_amount' => $costTotal,
                'status' => 'completed',
                'created_by' => $userId,
            ]);

            foreach ($lines as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit_cost' => $line['unit_cost'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $sale->load('items.product', 'invoice', 'customer');
        });
    }

    /** بيقفل الشيفت ويحسب الفرق بين النقدية الفعلية والمتوقعة (نقاط بيع كاش فقط). */
    public function closeSession(RegisterSession $session, float $closingCash): RegisterSession
    {
        $cashSalesTotal = (float) $session->sales()
            ->where('payment_method', 'cash')
            ->where('status', 'completed')
            ->sum('total_amount');

        $expectedCash = (float) $session->opening_cash + $cashSalesTotal;

        $session->update([
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => round($closingCash - $expectedCash, 2),
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $session;
    }
}
