<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\PosOutlet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosOutletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $outlets = PosOutlet::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json($outlets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:restaurant,bar,minibar,spa,other',
        ]);

        return response()->json(PosOutlet::create($validated), 201);
    }

    public function show(PosOutlet $posOutlet): JsonResponse
    {
        return response()->json($posOutlet->load('products'));
    }

    public function update(Request $request, PosOutlet $posOutlet): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:restaurant,bar,minibar,spa,other',
            'is_active' => 'sometimes|boolean',
        ]);

        $posOutlet->update($validated);

        return response()->json($posOutlet);
    }

    public function destroy(PosOutlet $posOutlet): JsonResponse
    {
        $posOutlet->delete();

        return response()->json(null, 204);
    }
}
