<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\AjeerContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AjeerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AjeerContract::latest()->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contract_type' => 'required|in:lending,temporary,seasonal,event',
            'direction' => 'required|in:inbound,outbound',
            'worker_id_number' => 'required|string',
            'worker_name' => 'nullable|string',
            'occupation' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'agreed_amount' => 'numeric|min:0',
        ]);

        $contract = AjeerContract::create($data);
        return response()->json(['data' => $contract], 201);
    }

    public function show(AjeerContract $contract): JsonResponse
    {
        return response()->json(['data' => $contract]);
    }

    public function activate(AjeerContract $contract): JsonResponse
    {
        $contract->update(['status' => 'active']);
        return response()->json(['data' => $contract]);
    }

    public function complete(AjeerContract $contract): JsonResponse
    {
        $contract->update(['status' => 'completed']);
        return response()->json(['data' => $contract]);
    }
}
