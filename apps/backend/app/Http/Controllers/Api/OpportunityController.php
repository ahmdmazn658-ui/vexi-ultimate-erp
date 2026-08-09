<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $opportunities = Opportunity::query()
            ->when($request->query('stage'), fn ($q, $s) => $q->where('stage', $s))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->query('owner_id'), fn ($q, $id) => $q->where('owner_id', $id))
            ->with(['customer:id,name', 'owner:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($opportunities);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'expected_amount' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'stage' => 'nullable|in:prospecting,qualification,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $opportunity = Opportunity::create($validated);

        return response()->json($opportunity, 201);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        return response()->json($opportunity->load('customer', 'lead', 'owner'));
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'expected_amount' => 'sometimes|numeric|min:0',
            'probability' => 'sometimes|integer|min:0|max:100',
            'stage' => 'sometimes|in:prospecting,qualification,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $opportunity->update($validated);

        return response()->json($opportunity);
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $opportunity->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/crm/opportunities/{id}/move-stage
     * Body: { stage }
     * ينقل الفرصة لمرحلة جديدة في الـ pipeline (won/lost بتقفلها فعليًا).
     */
    public function moveStage(Request $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'stage' => 'required|in:prospecting,qualification,proposal,negotiation,won,lost',
        ]);

        $opportunity->update([
            'stage' => $validated['stage'],
            'probability' => match ($validated['stage']) {
                'won' => 100,
                'lost' => 0,
                default => $opportunity->probability,
            },
        ]);

        return response()->json($opportunity);
    }
}
