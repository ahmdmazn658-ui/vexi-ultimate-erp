<?php

namespace App\Http\Controllers\Api\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Violation;
use App\Support\Fleet\FleetAccountingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * سجل المخالفات المرورية للأسطول. المخالفة بتتسجّل أول ما توصل من الجهة
 * المرورية (status=unpaid) من غير قيد محاسبي — القيد بيتولّد بس لحظة السداد
 * الفعلي (pay) عشان دفتر الأستاذ يعكس تدفق نقدي حقيقي مش مجرد إشعار.
 * المخالفة المتنازع عليها أو المُلغاة (disputed/waived) ما بتتقيّدش خالص.
 */
class ViolationController extends Controller
{
    public function __construct(private readonly FleetAccountingService $accounting) {}

    public function index(Request $request): JsonResponse
    {
        $violations = Violation::query()
            ->when($request->query('fleet_vehicle_id'), fn ($q, $id) => $q->where('fleet_vehicle_id', $id))
            ->when($request->query('fleet_driver_id'), fn ($q, $id) => $q->where('fleet_driver_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with(['vehicle:id,plate_number', 'driver:id,full_name'])
            ->latest('violation_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($violations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fleet_vehicle_id' => 'required|exists:fleet_vehicles,id',
            'fleet_driver_id' => 'nullable|exists:fleet_drivers,id',
            'violation_number' => 'nullable|string|max:255',
            'violation_type' => 'sometimes|in:speeding,parking,red_light,no_permit,lane_violation,seatbelt,phone_use,other',
            'violation_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'liability' => 'sometimes|in:company,driver',
            'notes' => 'nullable|string',
        ]);

        $violation = Violation::create($validated + ['status' => 'unpaid']);

        return response()->json($violation->load('vehicle', 'driver'), 201);
    }

    public function update(Request $request, Violation $violation): JsonResponse
    {
        $validated = $request->validate([
            'fleet_driver_id' => 'nullable|exists:fleet_drivers,id',
            'violation_number' => 'nullable|string|max:255',
            'violation_type' => 'sometimes|in:speeding,parking,red_light,no_permit,lane_violation,seatbelt,phone_use,other',
            'violation_date' => 'sometimes|date',
            'location' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'liability' => 'sometimes|in:company,driver',
            'status' => 'sometimes|in:unpaid,disputed,waived',
            'notes' => 'nullable|string',
        ]);

        if ($violation->status === 'paid') {
            throw ValidationException::withMessages([
                'status' => ['المخالفة دي متسددة وليها قيد مُرحّل بالفعل — مينفعش تتعدّل.'],
            ]);
        }

        $violation->update($validated);

        return response()->json($violation->fresh('vehicle', 'driver'));
    }

    /**
     * POST /api/v1/fleet/violations/{id}/pay
     * يسجّل السداد ويولّد القيد المحاسبي: مصروف على الشركة أو ذمة على السائق.
     */
    public function pay(Request $request, Violation $violation): JsonResponse
    {
        if ($violation->status === 'paid') {
            throw ValidationException::withMessages([
                'status' => ['المخالفة دي متسددة بالفعل.'],
            ]);
        }

        $validated = $request->validate([
            'paid_date' => 'nullable|date',
        ]);

        $violation = DB::transaction(function () use ($violation, $validated, $request) {
            $violation->update([
                'status' => 'paid',
                'paid_date' => $validated['paid_date'] ?? now()->toDateString(),
            ]);

            $this->accounting->postViolation($violation, $request->user()?->id);

            return $violation;
        });

        return response()->json($violation->fresh(['vehicle', 'driver', 'journalEntry.lines.account']));
    }

    public function destroy(Violation $violation): JsonResponse
    {
        if ($violation->status === 'paid') {
            throw ValidationException::withMessages([
                'status' => ['المخالفة دي متسددة وليها قيد مُرحّل — مينفعش تتحذف.'],
            ]);
        }

        $violation->delete();

        return response()->json(null, 204);
    }
}
