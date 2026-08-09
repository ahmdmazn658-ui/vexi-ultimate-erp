<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\WorkPermit;
use App\Models\LaborMarket\LaborViolation;
use App\Models\LaborMarket\MolLevy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaborOfficeController extends Controller
{
    // === Work Permits (التأشيرات) ===
    public function workPermits(): JsonResponse
    {
        return response()->json(WorkPermit::latest()->paginate(50));
    }

    public function storeWorkPermit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'permit_type' => 'required|in:new,renewal,transfer,occupation_change',
            'employee_id' => 'nullable|exists:employees,id',
            'occupation_code' => 'nullable|string',
            'nationality' => 'required|string|max:5',
        ]);

        $permit = WorkPermit::create($data);
        return response()->json(['data' => $permit], 201);
    }

    // === Violations (المخالفات) ===
    public function violations(): JsonResponse
    {
        return response()->json(LaborViolation::latest()->paginate(50));
    }

    public function storeViolation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'violation_type' => 'required|string',
            'severity' => 'required|in:minor,moderate,major,critical',
            'description' => 'required|string',
            'violation_date' => 'required|date',
            'fine_amount' => 'numeric|min:0',
        ]);

        $violation = LaborViolation::create($data);
        return response()->json(['data' => $violation], 201);
    }

    public function correctViolation(Request $request, LaborViolation $violation): JsonResponse
    {
        $violation->update([
            'status' => 'corrected',
            'corrective_action' => $request->input('action'),
        ]);

        return response()->json(['data' => $violation]);
    }

    public function appealViolation(Request $request, LaborViolation $violation): JsonResponse
    {
        $violation->update([
            'status' => 'appealed',
            'appeal_status' => 'pending',
            'appeal_notes' => $request->input('notes'),
        ]);

        return response()->json(['data' => $violation]);
    }

    // === MOL Levies (المقابل المالي) ===
    public function levies(): JsonResponse
    {
        return response()->json(MolLevy::latest()->paginate(24));
    }

    public function calculateLevy(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required', 'month' => 'required']);

        $nonSaudiCount = \App\Models\Employee::where('nationality', '!=', 'SA')
            ->where('status', 'active')->count();
        $exemptCount = 0; // TODO: calculate based on rules
        $billable = $nonSaudiCount - $exemptCount;
        $rate = 400; // SAR per worker per month (varies by band)
        $total = $billable * $rate;

        $levy = MolLevy::updateOrCreate(
            ['year' => $request->year, 'month' => $request->month],
            [
                'non_saudi_count' => $nonSaudiCount,
                'exempt_count' => $exemptCount,
                'billable_count' => $billable,
                'rate_per_worker' => $rate,
                'total_amount' => $total,
                'due_date' => now()->setYear($request->year)->setMonth($request->month)->endOfMonth(),
            ]
        );

        return response()->json(['data' => $levy]);
    }
}
