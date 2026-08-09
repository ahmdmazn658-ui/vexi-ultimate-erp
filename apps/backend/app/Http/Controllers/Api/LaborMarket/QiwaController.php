<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\QiwaContract;
use App\Models\LaborMarket\QiwaContractAmendment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QiwaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(QiwaContract::with('employee')->latest()->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contract_type' => 'required|in:definite,indefinite,part_time,seasonal,temporary',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'numeric|min:0',
            'transportation_allowance' => 'numeric|min:0',
            'job_title' => 'required|string',
            'job_title_ar' => 'required|string',
        ]);

        $data['total_salary'] = $data['basic_salary'] + ($data['housing_allowance'] ?? 0) + ($data['transportation_allowance'] ?? 0);
        $data['status'] = 'draft';

        $contract = QiwaContract::create($data);
        return response()->json(['data' => $contract], 201);
    }

    public function show(QiwaContract $contract): JsonResponse
    {
        return response()->json(['data' => $contract->load(['employee', 'amendments'])]);
    }

    /**
     * إرسال العقد للموظف للموافقة
     */
    public function sendToEmployee(QiwaContract $contract): JsonResponse
    {
        $contract->update(['status' => 'pending_employee']);
        return response()->json(['message' => 'تم إرسال العقد للموظف', 'data' => $contract]);
    }

    /**
     * توقيع صاحب العمل
     */
    public function employerSign(QiwaContract $contract): JsonResponse
    {
        $contract->update([
            'employer_signed' => true,
            'employer_signed_date' => now(),
        ]);

        if ($contract->employee_accepted) {
            $contract->update(['status' => 'active']);
        }

        return response()->json(['data' => $contract]);
    }

    /**
     * قبول الموظف
     */
    public function employeeAccept(QiwaContract $contract): JsonResponse
    {
        $contract->update([
            'employee_accepted' => true,
            'employee_accepted_date' => now(),
        ]);

        if ($contract->employer_signed) {
            $contract->update(['status' => 'active']);
        }

        return response()->json(['data' => $contract]);
    }

    /**
     * تعديل العقد
     */
    public function amend(Request $request, QiwaContract $contract): JsonResponse
    {
        $data = $request->validate([
            'amendment_type' => 'required|in:salary_change,title_change,location_change,renewal',
            'new_values' => 'required|array',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $amendment = QiwaContractAmendment::create([
            'qiwa_contract_id' => $contract->id,
            'amendment_type' => $data['amendment_type'],
            'old_values' => $contract->only(['basic_salary', 'housing_allowance', 'job_title', 'work_location']),
            'new_values' => $data['new_values'],
            'effective_date' => $data['effective_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['data' => $amendment], 201);
    }

    /**
     * إنهاء العقد
     */
    public function terminate(Request $request, QiwaContract $contract): JsonResponse
    {
        $contract->update([
            'status' => 'terminated',
            'termination_reason' => $request->input('reason'),
        ]);

        return response()->json(['message' => 'تم إنهاء العقد', 'data' => $contract]);
    }
}
