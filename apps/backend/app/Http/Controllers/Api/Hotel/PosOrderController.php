<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\PosOrder;
use App\Models\Hotel\PosProduct;
use App\Support\Hotel\FolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosOrderController extends Controller
{
    public function __construct(private readonly FolioService $folioService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = PosOrder::query()
            ->when($request->query('hotel_pos_outlet_id'), fn ($q, $id) => $q->where('hotel_pos_outlet_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with(['outlet:id,name', 'room:id,room_number', 'items.product:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_pos_outlet_id' => 'required|exists:hotel_pos_outlets,id',
            'hotel_reservation_id' => 'nullable|exists:hotel_reservations,id',
            'hotel_room_id' => 'nullable|exists:hotel_rooms,id',
            'room_charge' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.hotel_pos_product_id' => 'required|exists:hotel_pos_products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if (($validated['room_charge'] ?? false) && empty($validated['hotel_reservation_id'])) {
            return response()->json(['message' => 'التحصيل على الغرفة محتاج رقم حجز.'], 422);
        }

        $order = DB::transaction(function () use ($validated, $request) {
            $order = PosOrder::create([
                'hotel_pos_outlet_id' => $validated['hotel_pos_outlet_id'],
                'hotel_reservation_id' => $validated['hotel_reservation_id'] ?? null,
                'hotel_room_id' => $validated['hotel_room_id'] ?? null,
                'room_charge' => $validated['room_charge'] ?? false,
                'status' => 'open',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $item) {
                $product = PosProduct::findOrFail($item['hotel_pos_product_id']);
                $order->items()->create([
                    'hotel_pos_product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $product->price * $item['quantity'],
                ]);
            }

            $order->recalculateTotal();

            if ($order->room_charge) {
                $this->folioService->postPosOrder($order->fresh());
            }

            return $order->fresh(['items.product', 'outlet']);
        });

        return response()->json($order, 201);
    }

    public function show(PosOrder $posOrder): JsonResponse
    {
        return response()->json($posOrder->load(['outlet', 'room', 'reservation', 'items.product']));
    }

    /** لو مش room_charge، بتتقفل كدفعة كاش/بطاقة مباشرة عند نقطة البيع. */
    public function markPaid(PosOrder $posOrder): JsonResponse
    {
        $posOrder->update(['status' => 'paid']);

        return response()->json($posOrder);
    }

    public function destroy(PosOrder $posOrder): JsonResponse
    {
        $posOrder->update(['status' => 'cancelled']);

        return response()->json(null, 204);
    }
}
