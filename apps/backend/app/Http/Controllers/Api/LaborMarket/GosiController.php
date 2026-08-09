<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\GosiSubscription;
use App\Models\LaborMarket\GosiMonthlySubmission;
use App\Models\LaborMarket\GosiInjuryReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GosiController extends Controller
{
    // === Subscriptions ===
    public function subscriptions(): JsonResponse
    {
        $subs = GosiSubscription::with('employee')->latest()->paginate(50);
        return response()->json($subs);
    }

    public function showSubscription(GosiSubscription $subscription): JsonResponse
    {
        return response()->json(['data' => $subscription->load('employee')]);
    }

    public function storeSubscription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'subscriber_type' => 'required|in:saudi,non_saudi',
            'subscription_start_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'numeric|min:0',
        ]);

        $totalSalary = $data['basic_salary'] + ($data['housing_allowance'] ?? 0);
        $data['total_subscribable_salary'] = $totalSalary;

        // حساب الحصص
        if ($data['subscriber_type'] === 'saudi') {
            $data['employee_share'] = $totalSalary * 0.0975;
            $data['employer_share'] = $totalSalary * 0.1175;
            $data['occupational_hazards'] = $totalSalary * 0.02;
            $data['saned_contribution'] = $totalSalary * 0.015; // 1.5% combined
            $data['is_saned_eligible'] = true;
        } else {
            $data['employee_share'] = 0;
            $data['employer_share'] = 0;
            $data['occupational_hazards'] = $totalSalary * 0.02;
            $data['saned_contribution'] = 0;
            $data['is_saned_eligible'] = false;
        }

        $data['status'] = 'active';
        $subscription = GosiSubscription::create($data);

        return response()->json(['data' => $subscription], 201);
    }

    // === Monthly Submissions ===
    public function monthlySubmissions(): JsonResponse
    {
        $submissions = GosiMonthlySubmission::latest()->paginate(24);
        return response()->json($submissions);
    }

    /**
     * توليد ملف الإرسال الشهري
     */
    public function generateMonthlySubmission(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $year = $request->year;
        $month = $request->month;

        $subscriptions = GosiSubscription::where('status', 'active')->get();

        $totalEmployee = 0;
        $totalEmployer = 0;
        $totalHazards = 0;
        $totalSaned = 0;
        $saudiCount = 0;
        $nonSaudiCount = 0;

        foreach ($subscriptions as $sub) {
            $totalEmployee += $sub->employee_share;
            $totalEmployer += $sub->employer_share;
            $totalHazards += $sub->occupational_hazards;
            $totalSaned += $sub->saned_contribution;

            if ($sub->subscriber_type === 'saudi') $saudiCount++;
            else $nonSaudiCount++;
        }

        $submission = GosiMonthlySubmission::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'total_subscribers' => $subscriptions->count(),
                'saudi_subscribers' => $saudiCount,
                'non_saudi_subscribers' => $nonSaudiCount,
                'total_employee_share' => $totalEmployee,
                'total_employer_share' => $totalEmployer,
                'total_occupational_hazards' => $totalHazards,
                'total_saned' => $totalSaned,
                'grand_total' => $totalEmployee + $totalEmployer + $totalHazards + $totalSaned,
                'due_date' => now()->setYear($year)->setMonth($month)->endOfMonth()->addDays(15),
                'status' => 'draft',
            ]
        );

        return response()->json(['data' => $submission]);
    }

    public function submitMonthly(GosiMonthlySubmission $submission): JsonResponse
    {
        $submission->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return response()->json(['message' => 'تم الإرسال بنجاح', 'data' => $submission]);
    }

    // === Injury Reports ===
    public function injuries(): JsonResponse
    {
        return response()->json(GosiInjuryReport::with('employee')->latest()->paginate(20));
    }

    public function storeInjury(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'injury_date' => 'required|date',
            'injury_type' => 'required|in:work_injury,occupational_disease,commute_injury',
            'description' => 'required|string',
            'severity' => 'required|in:minor,moderate,severe,fatal',
        ]);

        $report = GosiInjuryReport::create($data);
        return response()->json(['data' => $report], 201);
    }
}
