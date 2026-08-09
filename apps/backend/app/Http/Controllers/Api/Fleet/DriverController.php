<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $drivers = Driver::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('search'), fn ($q, $s) => $q->where(function ($qq) use ($s) {
                $qq->where('full_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('license_number', 'like', "%{$s}%");
            }))
            ->with('employee:id,employee_code,full_name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($drivers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'license_number' => 'nullable|string|max:50|unique:fleet_drivers,license_number',
            'license_type' => 'sometimes|in:private,heavy,public_transport,motorcycle',
            'license_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        return response()->json(Driver::create($validated), 201);
    }

    public function show(Driver $driver): JsonResponse
    {
        return response()->json($driver->load(['employee', 'vehicles:id,plate_number,assigned_driver_id']));
    }

    public function update(Request $request, Driver $driver): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:30',
            'license_number' => 'nullable|string|max:50|unique:fleet_drivers,license_number,'.$driver->id,
            'license_type' => 'sometimes|in:private,heavy,public_transport,motorcycle',
            'license_expiry_date' => 'nullable|date',
            'status' => 'sometimes|in:active,suspended,inactive',
            'notes' => 'nullable|string',
        ]);

        $driver->update($validated);

        return response()->json($driver);
    }

    public function destroy(Driver $driver): JsonResponse
    {
        $driver->delete();

        return response()->json(null, 204);
    }
}
