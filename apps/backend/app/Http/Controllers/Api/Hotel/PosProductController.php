<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\PosProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = PosProduct::query()
            ->when($request->query('hotel_pos_outlet_id'), fn ($q, $id) => $q->where('hotel_pos_outlet_id', $id))
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_pos_outlet_id' => 'required|exists:hotel_pos_outlets,id',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        return response()->json(PosProduct::create($validated), 201);
    }

    public function update(Request $request, PosProduct $posProduct): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $posProduct->update($validated);

        return response()->json($posProduct);
    }

    public function destroy(PosProduct $posProduct): JsonResponse
    {
        $posProduct->delete();

        return response()->json(null, 204);
    }
}
