<?php

namespace App\Support\Hotel;

use App\Models\Customer;
use App\Models\Hotel\Folio;
use App\Models\Hotel\PosOrder;
use App\Models\Hotel\Reservation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * منطق الـ Folio: فتحه عند الـ check-in، تسجيل مصاريف الغرفة/الـ POS عليه،
 * وقفله عند الـ checkout بتوليد Invoice حقيقي في نظام المحاسبة (بنفس آلية
 * فواتير المبيعات العادية) عشان دفتر الأستاذ يفضل هو مصدر الحقيقة المالية
 * الوحيد زي ما موضح في مبدأ النظام المعماري.
 */
class FolioService
{
    /** بيفتح folio للحجز وبيسجّل مصاريف الغرفة عن كل ليلة إقامة دفعة واحدة. */
    public function openForReservation(Reservation $reservation): Folio
    {
        $folio = Folio::firstOrCreate(
            ['hotel_reservation_id' => $reservation->id],
            ['status' => 'open']
        );

        foreach ($reservation->reservationRooms as $resRoom) {
            $date = $reservation->check_in_date->copy();
            while ($date->lt($reservation->check_out_date)) {
                $folio->charges()->create([
                    'type' => 'room',
                    'description' => "إيجار غرفة {$resRoom->room->room_number} - ".$date->toDateString(),
                    'amount' => $resRoom->rate_per_night,
                    'charge_date' => $date->toDateString(),
                ]);
                $date->addDay();
            }
        }

        return $folio->fresh('charges');
    }

    /** بيحوّل أوردر POS (لو room_charge=true) لبند في الـ folio بدل الدفع الفوري. */
    public function postPosOrder(PosOrder $order): void
    {
        if (! $order->room_charge || ! $order->hotel_reservation_id) {
            return;
        }

        $folio = Folio::firstOrCreate(
            ['hotel_reservation_id' => $order->hotel_reservation_id],
            ['status' => 'open']
        );

        $folio->charges()->create([
            'type' => 'pos',
            'description' => 'طلب '.$order->outlet->name.' #'.$order->id,
            'amount' => $order->total_amount,
            'charge_date' => now()->toDateString(),
            'source_type' => PosOrder::class,
            'source_id' => $order->id,
        ]);

        $order->update(['status' => 'charged_to_room']);
    }

    /** بيقفل الـ folio عند الـ checkout ويولّد فاتورة محاسبية من كل البنود المجمّعة. */
    public function closeAndInvoice(Folio $folio, ?Customer $customer = null): Invoice
    {
        return DB::transaction(function () use ($folio, $customer) {
            $folio->loadMissing('charges', 'reservation.guest');

            $invoice = Invoice::create([
                'invoice_number' => 'HTL-'.Str::upper(Str::random(8)),
                'customer_id' => $customer?->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'subtotal' => 0,
                'vat_rate' => 15,
                'vat_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'status' => 'draft',
                'notes' => 'فاتورة إقامة فندقية — حجز رقم '.$folio->reservation->confirmation_number,
            ]);

            foreach ($folio->charges as $charge) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => $charge->description,
                    'quantity' => 1,
                    'unit_price' => $charge->amount,
                    'line_total' => $charge->amount,
                ]);
            }

            $invoice->recalculateTotals();

            $folio->update([
                'status' => 'closed',
                'invoice_id' => $invoice->id,
                'closed_at' => now(),
            ]);

            return $invoice->fresh('items');
        });
    }
}
