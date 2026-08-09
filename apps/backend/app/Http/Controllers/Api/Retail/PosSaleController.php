<?php

namespace App\Http\Controllers\Api\Retail;

use App\Http\Controllers\Controller;
use App\Models\Retail\PosSale;
use App\Models\Retail\RegisterSession;
use App\Support\Retail\PosSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosSaleController extends Controller
{
    public function __construct(private readonly PosSaleService $posSales) {}

    public function index(Request $request): JsonResponse
    {
        $sales = PosSale::query()
            ->when($request->query('pos_register_session_id'), fn ($q, $id) => $q->where('pos_register_session_id', $id))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->with('customer:id,name', 'registerSession:id,register_name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($sales);
    }

    /**
     * POST /api/v1/retail/sales
     * Body: {
     *   pos_register_session_id, customer_id?, payment_method?,
     *   items: [{ product_id, quantity, unit_price? }]
     * }
     * عملية checkout كاملة: صرف مخزون + فاتورة مدفوعة + قيد محاسبي مُرحّل — كل ده دفعة واحدة.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pos_register_session_id' => 'required|exists:pos_register_sessions,id',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'sometimes|in:cash,card,mixed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $session = RegisterSession::findOrFail($validated['pos_register_session_id']);

        $sale = $this->posSales->checkout(
            $session,
            $validated['items'],
            $validated['customer_id'] ?? null,
            $validated['payment_method'] ?? 'cash',
            $request->user()?->id
        );

        return response()->json($sale, 201);
    }

    public function show(PosSale $sale): JsonResponse
    {
        return response()->json(
            $sale->load('items.product', 'customer', 'registerSession', 'invoice', 'journalEntry.lines.account')
        );
    }
}
