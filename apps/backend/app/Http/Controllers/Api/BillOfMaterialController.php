<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillOfMaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $boms = BillOfMaterial::query()
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->query('is_active'), fn ($q, $a) => $q->where('is_active', filter_var($a, FILTER_VALIDATE_BOOLEAN)))
            ->with('product:id,sku,name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($boms);
    }

    /**
     * POST /api/v1/manufacturing/bom
     * Body: { product_id, name, version?, notes?, items: [{component_product_id, quantity}] }
     * items[].quantity = الكمية المطلوبة من الخامة لإنتاج وحدة واحدة من المنتج النهائي.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id|different:product_id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $bom = DB::transaction(function () use ($validated) {
            $bom = BillOfMaterial::create([
                'product_id' => $validated['product_id'],
                'name' => $validated['name'],
                'version' => $validated['version'] ?? '1.0',
                'notes' => $validated['notes'] ?? null,
            ]);

            $bom->items()->createMany($validated['items']);

            return $bom;
        });

        return response()->json($bom->load('items.component', 'product'), 201);
    }

    public function show(BillOfMaterial $bom): JsonResponse
    {
        return response()->json($bom->load('items.component', 'product'));
    }

    public function update(Request $request, BillOfMaterial $bom): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'version' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        $bom->update($validated);

        return response()->json($bom);
    }

    public function destroy(BillOfMaterial $bom): JsonResponse
    {
        $bom->delete();

        return response()->json(null, 204);
    }
}
