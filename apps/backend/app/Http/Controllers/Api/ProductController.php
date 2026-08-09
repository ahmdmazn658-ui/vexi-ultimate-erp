<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('is_active'), fn ($q, $a) => $q->where('is_active', filter_var($a, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->query('low_stock'), fn ($q) => $q->whereColumn('quantity_on_hand', '<=', 'reorder_level'))
            ->when($request->query('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'sometimes|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'nullable|numeric|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            ...$product->toArray(),
            'below_reorder_level' => $product->isBelowReorderLevel(),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'sometimes|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
