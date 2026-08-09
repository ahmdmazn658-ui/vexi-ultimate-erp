<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $budgets = Budget::query()
            ->when($request->query('period'), fn ($q, $p) => $q->where('period', $p))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('account_id'), fn ($q, $id) => $q->where('account_id', $id))
            ->with(['account:id,account_code,account_name', 'project:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        $budgets->getCollection()->transform(function (Budget $b) {
            $actual = $b->actualAmount();

            return [
                ...$b->toArray(),
                'actual_amount' => $actual,
                'variance_amount' => (float) $b->budgeted_amount - $actual,
            ];
        });

        return response()->json($budgets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'period' => 'required|string|max:20',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'project_id' => 'nullable|exists:projects,id',
            'budgeted_amount' => 'required|numeric|min:0',
        ]);

        $budget = Budget::create([
            ...$validated,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json($budget, 201);
    }

    /**
     * GET /api/v1/budgeting/budgets/{id} — يشمل actual_amount و variance_amount محسوبين من دفتر الأستاذ
     */
    public function show(Budget $budget): JsonResponse
    {
        $actual = $budget->actualAmount();

        return response()->json([
            ...$budget->load('account', 'project')->toArray(),
            'actual_amount' => $actual,
            'variance_amount' => (float) $budget->budgeted_amount - $actual,
        ]);
    }

    public function update(Request $request, Budget $budget): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'budgeted_amount' => 'sometimes|numeric|min:0',
            'period_start' => 'sometimes|date',
            'period_end' => 'sometimes|date|after_or_equal:period_start',
        ]);

        $budget->update($validated);

        return response()->json($budget);
    }

    public function destroy(Budget $budget): JsonResponse
    {
        $budget->delete();

        return response()->json(null, 204);
    }
}
