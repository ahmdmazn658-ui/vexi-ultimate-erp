<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $types = RoomType::query()
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->withCount('rooms')
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'base_rate' => 'required|numeric|min:0',
        ]);

        return response()->json(RoomType::create($validated), 201);
    }

    public function show(RoomType $roomType): JsonResponse
    {
        return response()->json($roomType->load('rooms'));
    }

    public function update(Request $request, RoomType $roomType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'max_occupancy' => 'sometimes|integer|min:1|max:20',
            'base_rate' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $roomType->update($validated);

        return response()->json($roomType);
    }

    public function destroy(RoomType $roomType): JsonResponse
    {
        $roomType->delete();

        return response()->json(null, 204);
    }
}
