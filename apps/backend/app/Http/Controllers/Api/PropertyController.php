<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $properties = Property::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('property_type'), fn ($q, $t) => $q->where('property_type', $t))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('min_price'), fn ($q, $p) => $q->where('price', '>=', $p))
            ->when($request->query('max_price'), fn ($q, $p) => $q->where('price', '<=', $p))
            ->with('project:id,name,project_code')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($properties);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'property_code' => 'required|string|unique:properties,property_code',
            'name' => 'required|string|max:255',
            'property_type' => 'required|in:residential,commercial,land,industrial,mixed_use',
            'location' => 'nullable|string|max:255',
            'area_sqm' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit_number' => 'nullable|string|max:50',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $property = Property::create($validated);

        return response()->json($property, 201);
    }

    public function show(Property $property): JsonResponse
    {
        return response()->json($property->load('project'));
    }

    public function update(Request $request, Property $property): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:available,reserved,sold,rented,under_construction',
            'description' => 'nullable|string',
        ]);

        $property->update($validated);

        return response()->json($property);
    }

    public function destroy(Property $property): JsonResponse
    {
        $property->delete();

        return response()->json(null, 204);
    }
}
