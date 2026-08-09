<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\MaintenanceRecord;
use App\Models\Fleet\Vehicle;
use App\Support\Fleet\FleetAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceRecordController extends Controller
{
    public function __construct(private readonly FleetAccountingService $accounting) {}

    public function index(Request $request): JsonResponse
    {
        $records = MaintenanceRecord::query()
            ->when($request->query('fleet_vehicle_id'), fn ($q, $id) => $q->where('fleet_vehicle_id', $id))
            ->when($request->query('maintenance_type'), fn ($q, $t) => $q->where('maintenance_type', $t))
            ->with('vehicle:id,plate_number')
            ->latest('service_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fleet_vehicle_id' => 'required|exists:fleet_vehicles,id',
            'maintenance_type' => 'sometimes|in:scheduled,repair,inspection,tire_change,oil_change,other',
            'service_date' => 'required|date',
            'odometer_km' => 'nullable|integer|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date|after:service_date',
            'next_due_odometer_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        return response()->json(
            DB::transaction(function () use ($validated, $request) {
                $record = MaintenanceRecord::create($validated);

                // تسجيل صيانة بعداد أعلى من عداد العربية الحالي بيحدّث عداد العربية.
                if (! empty($validated['odometer_km'])) {
                    Vehicle::where('id', $validated['fleet_vehicle_id'])
                        ->where('odometer_km', '<', $validated['odometer_km'])
                        ->update(['odometer_km' => $validated['odometer_km']]);
                }

                // قيد محاسبي تلقائي: مدين مصروف صيانة / دائن نقدية.
                $this->accounting->postMaintenance($record, $request->user()?->id);

                return $record->load('vehicle', 'journalEntry.lines.account');
            }),
            201
        );
    }

    public function update(Request $request, MaintenanceRecord $maintenanceRecord): JsonResponse
    {
        $validated = $request->validate([
            'maintenance_type' => 'sometimes|in:scheduled,repair,inspection,tire_change,oil_change,other',
            'service_date' => 'sometimes|date',
            'odometer_km' => 'nullable|integer|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date',
            'next_due_odometer_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $maintenanceRecord->update($validated);

        // لو التكلفة اتضافت/اتعدّلت وما فيش قيد مُرحّل قبل كده، يتقيّد دلوقتي.
        if (! $maintenanceRecord->journal_entry_id && (float) $maintenanceRecord->cost > 0) {
            $this->accounting->postMaintenance($maintenanceRecord, $request->user()?->id);
        }

        return response()->json($maintenanceRecord->fresh(['vehicle', 'journalEntry.lines.account']));
    }

    public function destroy(MaintenanceRecord $maintenanceRecord): JsonResponse
    {
        $maintenanceRecord->delete();

        return response()->json(null, 204);
    }
}
