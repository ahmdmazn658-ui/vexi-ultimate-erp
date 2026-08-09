<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\HrdfProgram;
use App\Models\LaborMarket\HrdfBeneficiary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrdfController extends Controller
{
    public function programs(): JsonResponse
    {
        return response()->json(HrdfProgram::withCount('beneficiaries')->latest()->get());
    }

    public function storeProgram(Request $request): JsonResponse
    {
        $data = $request->validate([
            'program_type' => 'required|in:tamheer,tawteen,support_salary,training,doroob',
            'program_name' => 'required|string',
            'program_name_ar' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'max_beneficiaries' => 'integer|min:1',
            'support_amount_per_month' => 'numeric|min:0',
            'support_duration_months' => 'integer|min:1',
        ]);

        $program = HrdfProgram::create($data);
        return response()->json(['data' => $program], 201);
    }

    public function enrollBeneficiary(Request $request, HrdfProgram $program): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'national_id' => 'required|string',
        ]);

        $beneficiary = HrdfBeneficiary::create([
            'hrdf_program_id' => $program->id,
            'employee_id' => $data['employee_id'],
            'national_id' => $data['national_id'],
            'enrollment_date' => now(),
            'monthly_support' => $program->support_amount_per_month,
            'status' => 'active',
        ]);

        $program->increment('current_beneficiaries');

        return response()->json(['data' => $beneficiary], 201);
    }

    public function beneficiaries(HrdfProgram $program): JsonResponse
    {
        return response()->json($program->beneficiaries()->with('employee')->get());
    }

    /**
     * تقديم مطالبة شهرية
     */
    public function submitClaim(Request $request, HrdfProgram $program): JsonResponse
    {
        $request->validate(['year' => 'required', 'month' => 'required']);

        $activeBeneficiaries = $program->beneficiaries()->where('status', 'active')->get();
        $totalClaim = $activeBeneficiaries->sum('monthly_support');

        $program->increment('total_claimed', $totalClaim);

        return response()->json([
            'beneficiaries_count' => $activeBeneficiaries->count(),
            'total_claim' => $totalClaim,
            'status' => 'submitted',
        ]);
    }
}
