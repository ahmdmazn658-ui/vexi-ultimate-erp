<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = FixedAsset::query()
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->with('project:id,name')
            ->latest('purchase_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:heavy_equipment,vehicle,tool,building,furniture,it_equipment,other',
            'project_id' => 'nullable|exists:projects,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:100',
            'salvage_value' => 'nullable|numeric|min:0',
            'depreciation_method' => 'required|in:straight_line,declining_balance',
            'location' => 'nullable|string|max:255',
        ]);

        $asset = FixedAsset::create($validated);

        return response()->json($asset, 201);
    }

    /**
     * GET /api/v1/fixed-assets/{id} — includes computed book value + annual depreciation
     */
    public function show(FixedAsset $fixedAsset): JsonResponse
    {
        return response()->json([
            ...$fixedAsset->load('project')->toArray(),
            'annual_depreciation' => $fixedAsset->annualDepreciation(),
            'book_value' => $fixedAsset->bookValue(),
        ]);
    }

    public function update(Request $request, FixedAsset $fixedAsset): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,under_maintenance,disposed,sold',
            'accumulated_depreciation' => 'sometimes|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $fixedAsset->update($validated);

        return response()->json($fixedAsset);
    }

    /**
     * POST /api/v1/fixed-assets/{id}/run-depreciation
     * يضيف قسط سنة واحدة من الإهلاك على المجمع الحالي
     */
    public function runDepreciation(FixedAsset $fixedAsset): JsonResponse
    {
        $annual = $fixedAsset->annualDepreciation();
        $newAccumulated = min(
            (float) $fixedAsset->accumulated_depreciation + $annual,
            (float) $fixedAsset->purchase_cost - (float) $fixedAsset->salvage_value
        );

        $fixedAsset->update(['accumulated_depreciation' => $newAccumulated]);

        return response()->json([
            'asset' => $fixedAsset,
            'annual_depreciation_applied' => $annual,
            'book_value' => $fixedAsset->bookValue(),
        ]);
    }

    public function destroy(FixedAsset $fixedAsset): JsonResponse
    {
        $fixedAsset->delete();

        return response()->json(null, 204);
    }
}
