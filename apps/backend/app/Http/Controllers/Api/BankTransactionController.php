<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = BankTransaction::query()
            ->when($request->query('bank_account_id'), fn ($q, $id) => $q->where('bank_account_id', $id))
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->has('is_reconciled'), fn ($q) => $q->where('is_reconciled', $request->boolean('is_reconciled')))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('transaction_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('transaction_date', '<=', $d))
            ->latest('transaction_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($transactions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $transaction = BankTransaction::create([
            ...$validated,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json($transaction, 201);
    }

    public function show(BankTransaction $bankTransaction): JsonResponse
    {
        return response()->json($bankTransaction->load('bankAccount'));
    }

    public function destroy(BankTransaction $bankTransaction): JsonResponse
    {
        if ($bankTransaction->is_reconciled) {
            return response()->json([
                'message' => 'مينفعش تحذف حركة اتسوّت بنكيًا بالفعل.',
            ], 422);
        }

        $bankTransaction->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/banking/transactions/{id}/reconcile
     * يعلّم الحركة كمسوّاة بنكيًا (matched مع كشف الحساب البنكي).
     */
    public function reconcile(BankTransaction $bankTransaction): JsonResponse
    {
        $bankTransaction->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
        ]);

        return response()->json($bankTransaction);
    }
}
