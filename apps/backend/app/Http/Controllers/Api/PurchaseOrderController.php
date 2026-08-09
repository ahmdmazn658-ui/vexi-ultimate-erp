<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::query()
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('supplier_id'), fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('supplier:id,name,supplier_code', 'project:id,name')
            ->latest('order_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($orders);
    }

    /**
     * POST /api/v1/procurement/purchase-orders
     * Body: { po_number, supplier_id, project_id?, order_date, items: [{item_name, unit, quantity, unit_price}] }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po_number' => 'required|string|unique:purchase_orders,po_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $order = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'supplier_id' => $validated['supplier_id'],
                'project_id' => $validated['project_id'] ?? null,
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'item_name' => $item['item_name'],
                    'unit' => $item['unit'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        return response()->json($order->load('items', 'supplier'), 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json($purchaseOrder->load('items', 'supplier', 'project'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:draft,submitted,approved,received,cancelled',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return response()->json($purchaseOrder);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->delete();

        return response()->json(null, 204);
    }
}
