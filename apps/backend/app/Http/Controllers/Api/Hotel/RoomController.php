<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rooms = Room::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('hotel_room_type_id'), fn ($q, $id) => $q->where('hotel_room_type_id', $id))
            ->when($request->query('floor'), fn ($q, $f) => $q->where('floor', $f))
            ->with('roomType:id,name,base_rate')
            ->orderBy('room_number')
            ->paginate($request->integer('per_page', 50));

        return response()->json($rooms);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_room_type_id' => 'required|exists:hotel_room_types,id',
            'room_number' => 'required|string|max:20|unique:hotel_rooms,room_number',
            'floor' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        return response()->json(Room::create($validated), 201);
    }

    public function show(Room $room): JsonResponse
    {
        return response()->json($room->load('roomType'));
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'hotel_room_type_id' => 'sometimes|exists:hotel_room_types,id',
            'floor' => 'nullable|string|max:20',
            'status' => 'sometimes|in:vacant_clean,vacant_dirty,occupied_clean,occupied_dirty,out_of_order,out_of_service',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        $room->update($validated);

        return response()->json($room);
    }

    /** إتاحة الغرف الفاضية فعليًا في فترة معينة — بيُستخدم في شاشة الحجز الجديد. */
    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'hotel_room_type_id' => 'nullable|exists:hotel_room_types,id',
        ]);

        $rooms = Room::query()
            ->where('is_active', true)
            ->when($validated['hotel_room_type_id'] ?? null, fn ($q, $id) => $q->where('hotel_room_type_id', $id))
            ->with('roomType:id,name,base_rate')
            ->get()
            ->filter(fn (Room $room) => $room->isAvailableBetween($validated['check_in'], $validated['check_out']))
            ->values();

        return response()->json($rooms);
    }

    public function destroy(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(null, 204);
    }
}
