<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('vehicle_type'), fn ($q, $t) => $q->where('vehicle_type', $t))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('search'), fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('plate_number', 'like', "%{$s}%")
                    ->orWhere('make', 'like', "%{$s}%")
                    ->orWhere('model', 'like', "%{$s}%");
            }))
            ->with(['assignedDriver:id,full_name', 'project:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($vehicles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plate_number' => 'required|string|max:30|unique:fleet_vehicles,plate_number',
            'vehicle_type' => 'sometimes|in:car,truck,van,bus,heavy_equipment,motorcycle,other',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1980|max:'.(now()->year + 1),
            'fuel_type' => 'sometimes|in:petrol,diesel,electric,hybrid',
            'ownership' => 'sometimes|in:owned,leased,rented',
            'odometer_km' => 'sometimes|integer|min:0',
            'fixed_asset_id' => 'nullable|exists:fixed_assets,id',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_driver_id' => 'nullable|exists:fleet_drivers,id',
            'notes' => 'nullable|string',
        ]);

        return response()->json(Vehicle::create($validated)->load('assignedDriver'), 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->load([
            'assignedDriver', 'project', 'fixedAsset',
            'trips' => fn ($q) => $q->latest('start_at')->limit(10)->with('driver:id,full_name'),
            'maintenanceRecords' => fn ($q) => $q->latest('service_date')->limit(10),
            'fuelLogs' => fn ($q) => $q->latest('log_date')->limit(10),
        ]));
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_type' => 'sometimes|in:car,truck,van,bus,heavy_equipment,motorcycle,other',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1980|max:'.(now()->year + 1),
            'fuel_type' => 'sometimes|in:petrol,diesel,electric,hybrid',
            'ownership' => 'sometimes|in:owned,leased,rented',
            'status' => 'sometimes|in:active,under_maintenance,out_of_service,sold,disposed',
            'odometer_km' => 'sometimes|integer|min:0',
            'fixed_asset_id' => 'nullable|exists:fixed_assets,id',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_driver_id' => 'nullable|exists:fleet_drivers,id',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        return response()->json($vehicle->fresh(['assignedDriver', 'project']));
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json(null, 204);
    }
}
