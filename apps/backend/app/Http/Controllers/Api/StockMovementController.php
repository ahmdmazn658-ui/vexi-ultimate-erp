<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $movements = StockMovement::query()
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->with('product:id,sku,name', 'warehouse:id,code,name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($movements);
    }

    /**
     * POST /api/v1/inventory/stock-movements
     * Body: { product_id, warehouse_id, type: in|out|adjustment, quantity, notes? }
     * "in" و "adjustment" (موجب) بيزودوا الكمية، "out" بينقصها — برفض لو الكمية غير كافية.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $movement = DB::transaction(function () use ($validated, $request) {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            if ($validated['type'] === 'out' && (float) $product->quantity_on_hand < (float) $validated['quantity']) {
                abort(422, 'الكمية المتاحة غير كافية لإتمام عملية الصرف.');
            }

            $delta = $validated['type'] === 'out' ? -$validated['quantity'] : $validated['quantity'];
            $product->increment('quantity_on_hand', $delta);

            return StockMovement::create([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);
        });

        return response()->json($movement->load('product:id,sku,name', 'warehouse:id,code,name'), 201);
    }
}
