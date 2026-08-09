<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $guests = Guest::query()
            ->when($request->query('search'), fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('full_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($guests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:100',
            'id_type' => 'nullable|in:national_id,passport,iqama',
            'id_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        return response()->json(Guest::create($validated), 201);
    }

    public function show(Guest $guest): JsonResponse
    {
        return response()->json($guest->load(['reservations' => fn ($q) => $q->latest()->limit(10)]));
    }

    public function update(Request $request, Guest $guest): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'nationality' => 'nullable|string|max:100',
            'id_type' => 'nullable|in:national_id,passport,iqama',
            'id_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $guest->update($validated);

        return response()->json($guest);
    }

    public function destroy(Guest $guest): JsonResponse
    {
        $guest->delete();

        return response()->json(null, 204);
    }
}
