<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = ProductionOrder::query()
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('product:id,sku,name', 'warehouse:id,code,name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($orders);
    }

    /**
     * POST /api/v1/manufacturing/production-orders
     * Body: { order_number, product_id, bill_of_material_id?, warehouse_id, quantity_planned, planned_start_date?, planned_end_date?, notes? }
     * لو bill_of_material_id متبعتش، بيدور تلقائي على أحدث BOM نشط للمنتج.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|unique:production_orders,order_number',
            'product_id' => 'required|exists:products,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity_planned' => 'required|numeric|min:0.01',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['bill_of_material_id'])) {
            $validated['bill_of_material_id'] = BillOfMaterial::where('product_id', $validated['product_id'])
                ->where('is_active', true)
                ->latest()
                ->value('id');
        }

        $order = ProductionOrder::create([
            ...$validated,
            'status' => 'draft',
            'created_by' => $request->user()?->id,
        ]);

        return response()->json($order->load('product', 'billOfMaterial.items.component', 'warehouse'), 201);
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        return response()->json($productionOrder->load('product', 'billOfMaterial.items.component', 'warehouse'));
    }

    public function update(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'quantity_planned' => 'sometimes|numeric|min:0.01',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'status' => 'sometimes|in:cancelled',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $productionOrder->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => ['الإلغاء متاح فقط لأمر إنتاج لسه draft ومحدش استهلك خامات منه.'],
            ]);
        }

        $productionOrder->update($validated);

        return response()->json($productionOrder);
    }

    /**
     * POST /api/v1/manufacturing/production-orders/{id}/start
     * يستهلك الخامات من الـ BOM (quantity × quantity_planned) وينشئ stock_movements type=out لكل خامة،
     * بعد التحقق من كفاية الكمية المتاحة لكل خامة. يحوّل الحالة لـ in_progress.
     */
    public function start(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status !== 'draft') {
            throw ValidationException::withMessages([
                'order' => ['أمر الإنتاج مش draft، مينفعش يتبدأ.'],
            ]);
        }

        if (! $productionOrder->bill_of_material_id) {
            throw ValidationException::withMessages([
                'bill_of_material_id' => ['لازم يتحدد Bill of Materials قبل بدء الإنتاج.'],
            ]);
        }

        DB::transaction(function () use ($request, $productionOrder) {
            $bom = $productionOrder->billOfMaterial()->with('items')->firstOrFail();

            foreach ($bom->items as $bomItem) {
                $requiredQty = (float) $bomItem->quantity * (float) $productionOrder->quantity_planned;
                $component = Product::lockForUpdate()->findOrFail($bomItem->component_product_id);

                if ((float) $component->quantity_on_hand < $requiredQty) {
                    throw ValidationException::withMessages([
                        'stock' => ["الكمية المتاحة من الخامة {$component->sku} غير كافية لبدء الإنتاج."],
                    ]);
                }

                $component->decrement('quantity_on_hand', $requiredQty);

                StockMovement::create([
                    'product_id' => $component->id,
                    'warehouse_id' => $productionOrder->warehouse_id,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'reference_type' => 'production_order',
                    'reference_id' => $productionOrder->id,
                    'notes' => "استهلاك إنتاج - أمر {$productionOrder->order_number}",
                    'created_by' => $request->user()?->id,
                ]);
            }

            $productionOrder->update([
                'status' => 'in_progress',
                'actual_start_date' => now()->toDateString(),
            ]);
        });

        return response()->json($productionOrder->fresh()->load('product', 'billOfMaterial.items.component', 'warehouse'));
    }

    /**
     * POST /api/v1/manufacturing/production-orders/{id}/complete
     * Body: { quantity_produced? } (افتراضي = quantity_planned)
     * يضيف الكمية المنتَجة لمخزون المنتج النهائي (stock_movement type=in)، ويقفل الأمر كـ completed.
     */
    public function complete(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'order' => ['أمر الإنتاج لازم يكون in_progress قبل ما يتقفل.'],
            ]);
        }

        $validated = $request->validate([
            'quantity_produced' => 'nullable|numeric|min:0.01',
        ]);

        $quantityProduced = $validated['quantity_produced'] ?? (float) $productionOrder->quantity_planned;

        DB::transaction(function () use ($request, $productionOrder, $quantityProduced) {
            $product = Product::lockForUpdate()->findOrFail($productionOrder->product_id);
            $product->increment('quantity_on_hand', $quantityProduced);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $productionOrder->warehouse_id,
                'type' => 'in',
                'quantity' => $quantityProduced,
                'reference_type' => 'production_order',
                'reference_id' => $productionOrder->id,
                'notes' => "ناتج إنتاج - أمر {$productionOrder->order_number}",
                'created_by' => $request->user()?->id,
            ]);

            $productionOrder->update([
                'status' => 'completed',
                'quantity_produced' => $quantityProduced,
                'actual_end_date' => now()->toDateString(),
            ]);
        });

        return response()->json($productionOrder->fresh()->load('product', 'warehouse'));
    }

    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status !== 'draft') {
            throw ValidationException::withMessages([
                'order' => ['لا يمكن حذف أمر إنتاج بدأ تنفيذه بالفعل.'],
            ]);
        }

        $productionOrder->delete();

        return response()->json(null, 204);
    }
}
