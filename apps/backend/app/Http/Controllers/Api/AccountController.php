<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * GET /api/v1/accounting/chart-of-accounts
     */
    public function index(Request $request): JsonResponse
    {
        // `flat=1` بيرجّع كل الحسابات في مستوى واحد بدل الشجرة — الشاشات اللي فيها
        // قائمة اختيار حساب (القيود، الموازنات، الحسابات البنكية) محتاجة كده،
        // لأن الوضع الافتراضي بيرجّع الحسابات الرئيسية بس والفرعية جوّاها.
        $query = Account::query()
            ->when($request->query('type'), fn ($q, $type) => $q->where('account_type', $type))
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('account_code');

        if (! $request->boolean('flat')) {
            $query->with('children')->whereNull('parent_id');
        }

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }

    /**
     * POST /api/v1/accounting/chart-of-accounts
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_code' => 'required|string|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_name_ar' => 'nullable|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        $account = Account::create($validated);

        return response()->json($account, 201);
    }

    /**
     * GET /api/v1/accounting/chart-of-accounts/{account}
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json($account->load('children', 'parent'));
    }

    /**
     * PUT /api/v1/accounting/chart-of-accounts/{account}
     */
    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'account_name' => 'sometimes|string|max:255',
            'account_name_ar' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $account->update($validated);

        return response()->json($account);
    }

    /**
     * DELETE /api/v1/accounting/chart-of-accounts/{account}
     */
    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json(null, 204);
    }
}
