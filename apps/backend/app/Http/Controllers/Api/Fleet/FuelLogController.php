<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\FuelLog;
use App\Models\Fleet\Vehicle;
use App\Support\Fleet\FleetAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelLogController extends Controller
{
    public function __construct(private readonly FleetAccountingService $accounting) {}

    public function index(Request $request): JsonResponse
    {
        $logs = FuelLog::query()
            ->when($request->query('fleet_vehicle_id'), fn ($q, $id) => $q->where('fleet_vehicle_id', $id))
            ->with(['vehicle:id,plate_number', 'driver:id,full_name'])
            ->latest('log_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fleet_vehicle_id' => 'required|exists:fleet_vehicles,id',
            'fleet_driver_id' => 'nullable|exists:fleet_drivers,id',
            'log_date' => 'required|date',
            'odometer_km' => 'nullable|integer|min:0',
            'liters' => 'nullable|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'fuel_station' => 'nullable|string|max:255',
        ]);

        return response()->json(
            DB::transaction(function () use ($validated, $request) {
                $log = FuelLog::create($validated);

                if (! empty($validated['odometer_km'])) {
                    Vehicle::where('id', $validated['fleet_vehicle_id'])
                        ->where('odometer_km', '<', $validated['odometer_km'])
                        ->update(['odometer_km' => $validated['odometer_km']]);
                }

                // قيد محاسبي تلقائي: مدين مصروف وقود / دائن نقدية.
                $this->accounting->postFuel($log, $request->user()?->id);

                return $log->load(['vehicle', 'driver', 'journalEntry.lines.account']);
            }),
            201
        );
    }

    public function update(Request $request, FuelLog $fuelLog): JsonResponse
    {
        $validated = $request->validate([
            'fleet_driver_id' => 'nullable|exists:fleet_drivers,id',
            'log_date' => 'sometimes|date',
            'odometer_km' => 'nullable|integer|min:0',
            'liters' => 'nullable|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'fuel_station' => 'nullable|string|max:255',
        ]);

        $fuelLog->update($validated);

        if (! $fuelLog->journal_entry_id && (float) $fuelLog->cost > 0) {
            $this->accounting->postFuel($fuelLog, $request->user()?->id);
        }

        return response()->json($fuelLog->fresh(['vehicle', 'driver', 'journalEntry.lines.account']));
    }

    public function destroy(FuelLog $fuelLog): JsonResponse
    {
        $fuelLog->delete();

        return response()->json(null, 204);
    }
}
