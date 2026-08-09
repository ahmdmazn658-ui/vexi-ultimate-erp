<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contracts = Contract::query()
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('project:id,name,project_code')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($contracts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contract_number' => 'required|string|unique:contracts,contract_number',
            'project_id' => 'nullable|exists:projects,id',
            'contract_type' => 'required|in:main,subcontract,supply,consultancy,lease',
            'party_name' => 'required|string|max:255',
            'contract_value' => 'required|numeric|min:0',
            'retention_percent' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'terms' => 'nullable|string',
        ]);

        $contract = Contract::create($validated);

        return response()->json($contract, 201);
    }

    public function show(Contract $contract): JsonResponse
    {
        return response()->json($contract->load('project'));
    }

    public function update(Request $request, Contract $contract): JsonResponse
    {
        $validated = $request->validate([
            'party_name' => 'sometimes|string|max:255',
            'contract_value' => 'sometimes|numeric|min:0',
            'retention_percent' => 'nullable|numeric|min:0|max:100',
            'end_date' => 'nullable|date',
            'status' => 'sometimes|in:draft,active,completed,terminated',
            'terms' => 'nullable|string',
        ]);

        $contract->update($validated);

        return response()->json($contract);
    }

    public function destroy(Contract $contract): JsonResponse
    {
        $contract->delete();

        return response()->json(null, 204);
    }
}
