<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\NitaqatRecord;
use App\Models\LaborMarket\NitaqatSimulation;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NitaqatController extends Controller
{
    public function index(): JsonResponse
    {
        $records = NitaqatRecord::latest()->get();
        return response()->json(['data' => $records]);
    }

    public function show(NitaqatRecord $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    /**
     * حساب نسبة السعودة الحالية
     */
    public function currentStatus(): JsonResponse
    {
        $totalEmployees = Employee::count();
        $saudiEmployees = Employee::where('nationality', 'SA')->count();
        $nonSaudiEmployees = $totalEmployees - $saudiEmployees;
        $percentage = $totalEmployees > 0 ? round(($saudiEmployees / $totalEmployees) * 100, 2) : 0;

        return response()->json([
            'total_employees' => $totalEmployees,
            'saudi_employees' => $saudiEmployees,
            'non_saudi_employees' => $nonSaudiEmployees,
            'saudization_percentage' => $percentage,
            'band' => $this->calculateBand($percentage),
        ]);
    }

    /**
     * محاكاة سيناريوهات التوظيف
     */
    public function simulate(Request $request): JsonResponse
    {
        $request->validate([
            'hire_saudis' => 'integer|min:0',
            'terminate_non_saudis' => 'integer|min:0',
            'hire_non_saudis' => 'integer|min:0',
            'terminate_saudis' => 'integer|min:0',
        ]);

        $totalEmployees = Employee::count();
        $saudiEmployees = Employee::where('nationality', 'SA')->count();

        $newSaudis = $saudiEmployees + ($request->hire_saudis ?? 0) - ($request->terminate_saudis ?? 0);
        $newTotal = $totalEmployees + ($request->hire_saudis ?? 0) + ($request->hire_non_saudis ?? 0)
            - ($request->terminate_non_saudis ?? 0) - ($request->terminate_saudis ?? 0);

        $projectedPercentage = $newTotal > 0 ? round(($newSaudis / $newTotal) * 100, 2) : 0;

        $simulation = NitaqatSimulation::create([
            'scenario_name' => $request->input('scenario_name', 'Simulation ' . now()->format('Y-m-d H:i')),
            'hire_saudis' => $request->hire_saudis ?? 0,
            'terminate_non_saudis' => $request->terminate_non_saudis ?? 0,
            'projected_percentage' => $projectedPercentage,
            'projected_band' => $this->calculateBand($projectedPercentage),
            'details' => $request->all(),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'current' => [
                'total' => $totalEmployees,
                'saudis' => $saudiEmployees,
                'percentage' => round(($saudiEmployees / max($totalEmployees, 1)) * 100, 2),
            ],
            'projected' => [
                'total' => $newTotal,
                'saudis' => $newSaudis,
                'percentage' => $projectedPercentage,
                'band' => $this->calculateBand($projectedPercentage),
            ],
            'simulation' => $simulation,
        ]);
    }

    /**
     * المزامنة مع بيانات نطاقات (عبر API)
     */
    public function sync(Request $request): JsonResponse
    {
        // TODO: Integration with actual Nitaqat API
        return response()->json(['message' => 'Sync initiated', 'status' => 'pending']);
    }

    private function calculateBand(float $percentage): string
    {
        if ($percentage >= 40) return 'platinum';
        if ($percentage >= 30) return 'green_high';
        if ($percentage >= 23) return 'green_mid';
        if ($percentage >= 17) return 'green_low';
        if ($percentage >= 10) return 'yellow';
        return 'red';
    }
}
