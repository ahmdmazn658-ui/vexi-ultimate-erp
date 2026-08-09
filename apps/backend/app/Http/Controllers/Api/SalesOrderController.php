<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = SalesOrder::query()
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('customer:id,name,customer_code', 'project:id,name')
            ->latest('order_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($orders);
    }

    /**
     * POST /api/v1/sales/orders
     * Body: { order_number, customer_id, project_id?, order_date, items: [{product_id?, item_name, quantity, unit_price}] }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:sales_orders,order_number',
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $order = SalesOrder::create([
                'order_number' => $validated['order_number'],
                'customer_id' => $validated['customer_id'],
                'project_id' => $validated['project_id'] ?? null,
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        return response()->json($order->load('items', 'customer'), 201);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        return response()->json($salesOrder->load('items.product', 'customer', 'project'));
    }

    public function update(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $validated = $request->validate([
            'delivery_date' => 'nullable|date',
            'status' => 'sometimes|in:draft,confirmed,delivered,invoiced,cancelled',
            'notes' => 'nullable|string',
        ]);

        $salesOrder->update($validated);

        return response()->json($salesOrder);
    }

    /**
     * POST /api/v1/sales/orders/{id}/confirm
     * يحول الطلب من draft إلى confirmed، وينشئ حركة صرف مخزون (stock_movement type=out)
     * لكل بند مرتبط بمنتج، مع التحقق من كفاية الكمية المتاحة.
     * Body: { warehouse_id }
     */
    public function confirm(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->status !== 'draft') {
            abort(422, 'لا يمكن تأكيد طلب ليس في حالة draft.');
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        DB::transaction(function () use ($validated, $salesOrder, $request) {
            foreach ($salesOrder->items()->whereNotNull('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ((float) $product->quantity_on_hand < (float) $item->quantity) {
                    abort(422, "الكمية المتاحة من المنتج {$product->sku} غير كافية.");
                }

                $product->decrement('quantity_on_hand', $item->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference_type' => 'sales_order',
                    'reference_id' => $salesOrder->id,
                    'created_by' => $request->user()?->id,
                ]);
            }

            $salesOrder->update(['status' => 'confirmed']);
        });

        return response()->json($salesOrder->fresh()->load('items.product', 'customer'));
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->status !== 'draft') {
            abort(422, 'لا يمكن حذف طلب تم تأكيده بالفعل.');
        }

        $salesOrder->delete();

        return response()->json(null, 204);
    }
}
