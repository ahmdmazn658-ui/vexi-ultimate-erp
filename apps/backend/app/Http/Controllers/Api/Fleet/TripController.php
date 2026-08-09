<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Trip;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $trips = Trip::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('fleet_vehicle_id'), fn ($q, $id) => $q->where('fleet_vehicle_id', $id))
            ->when($request->query('fleet_driver_id'), fn ($q, $id) => $q->where('fleet_driver_id', $id))
            ->with(['vehicle:id,plate_number', 'driver:id,full_name', 'project:id,name'])
            ->latest('start_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($trips);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fleet_vehicle_id' => 'required|exists:fleet_vehicles,id',
            'fleet_driver_id' => 'required|exists:fleet_drivers,id',
            'project_id' => 'nullable|exists:projects,id',
            'purpose' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'scheduled';
        $validated['created_by'] = $request->user()?->id;

        return response()->json(Trip::create($validated)->load(['vehicle', 'driver']), 201);
    }

    public function show(Trip $trip): JsonResponse
    {
        return response()->json($trip->load(['vehicle', 'driver', 'project', 'createdBy:id,name']));
    }

    public function update(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'start_at' => 'sometimes|date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:scheduled,cancelled',
        ]);

        $trip->update($validated);

        return response()->json($trip->fresh(['vehicle', 'driver']));
    }

    /** بداية الرحلة فعليًا — بتاخد عداد الانطلاق (افتراضيًا عداد العربية الحالي). */
    public function start(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'start_odometer_km' => 'nullable|integer|min:0',
        ]);

        $trip->update([
            'status' => 'in_progress',
            'start_at' => now(),
            'start_odometer_km' => $validated['start_odometer_km'] ?? $trip->vehicle->odometer_km,
        ]);

        return response()->json($trip->fresh(['vehicle', 'driver']));
    }

    /** إقفال الرحلة — بيحدّث عداد العربية تلقائيًا لعداد النهاية. */
    public function complete(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'end_odometer_km' => 'required|integer|min:'.($trip->start_odometer_km ?? 0),
        ]);

        return response()->json(
            DB::transaction(function () use ($trip, $validated) {
                $trip->update([
                    'status' => 'completed',
                    'end_at' => now(),
                    'end_odometer_km' => $validated['end_odometer_km'],
                ]);

                Vehicle::where('id', $trip->fleet_vehicle_id)
                    ->where('odometer_km', '<', $validated['end_odometer_km'])
                    ->update(['odometer_km' => $validated['end_odometer_km']]);

                return $trip->fresh(['vehicle', 'driver']);
            })
        );
    }

    public function destroy(Trip $trip): JsonResponse
    {
        $trip->delete();

        return response()->json(null, 204);
    }
}
