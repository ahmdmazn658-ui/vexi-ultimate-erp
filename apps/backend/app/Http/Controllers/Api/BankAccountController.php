<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = BankAccount::query()
            ->when($request->query('is_active'), fn ($q, $v) => $q->where('is_active', (bool) $v))
            ->with('account:id,account_name,account_code')
            ->get()
            ->map(fn (BankAccount $a) => [...$a->toArray(), 'current_balance' => $a->currentBalance()]);

        return response()->json(['data' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|unique:bank_accounts,account_number',
            'iban' => 'nullable|string|max:34',
            'currency' => 'nullable|string|size:3',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
        ]);

        $bankAccount = BankAccount::create($validated);

        return response()->json($bankAccount, 201);
    }

    public function show(BankAccount $bankAccount): JsonResponse
    {
        return response()->json([
            ...$bankAccount->load('account')->toArray(),
            'current_balance' => $bankAccount->currentBalance(),
        ]);
    }

    public function update(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $validated = $request->validate([
            'bank_name' => 'sometimes|string|max:255',
            'account_name' => 'sometimes|string|max:255',
            'iban' => 'nullable|string|max:34',
            'is_active' => 'sometimes|boolean',
        ]);

        $bankAccount->update($validated);

        return response()->json($bankAccount);
    }

    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->delete();

        return response()->json(null, 204);
    }
}
