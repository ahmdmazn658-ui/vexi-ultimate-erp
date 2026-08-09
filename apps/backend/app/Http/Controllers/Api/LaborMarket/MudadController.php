<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\MudadSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MudadController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MudadSubmission::latest()->paginate(24));
    }

    public function show(MudadSubmission $submission): JsonResponse
    {
        return response()->json(['data' => $submission]);
    }

    /**
     * التحقق من الامتثال لمدد
     */
    public function checkCompliance(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required', 'month' => 'required']);

        $totalEmployees = \App\Models\Employee::where('status', 'active')->count();
        // Simulate check
        $paidOnTime = $totalEmployees;
        $paidLate = 0;
        $unpaid = 0;
        $compliancePercentage = $totalEmployees > 0 ? round(($paidOnTime / $totalEmployees) * 100, 2) : 0;

        $submission = MudadSubmission::updateOrCreate(
            ['year' => $request->year, 'month' => $request->month],
            [
                'establishment_number' => config('company.mol_id', ''),
                'total_employees' => $totalEmployees,
                'paid_on_time' => $paidOnTime,
                'paid_late' => $paidLate,
                'unpaid' => $unpaid,
                'compliance_percentage' => $compliancePercentage,
                'compliance_status' => $compliancePercentage >= 80 ? 'green' : ($compliancePercentage >= 50 ? 'yellow' : 'red'),
                'payment_deadline' => now()->setYear($request->year)->setMonth($request->month)->endOfMonth()->addDays(3),
                'status' => 'validated',
            ]
        );

        return response()->json(['data' => $submission]);
    }

    public function submit(MudadSubmission $submission): JsonResponse
    {
        $submission->update(['status' => 'submitted', 'submission_date' => now()]);
        return response()->json(['message' => 'تم الإرسال لمدد', 'data' => $submission]);
    }
}
