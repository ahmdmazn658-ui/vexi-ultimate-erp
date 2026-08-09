<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->query('customer_type'), fn ($q, $t) => $q->where('customer_type', $t))
            ->when($request->query('is_active'), fn ($q, $a) => $q->where('is_active', filter_var($a, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->query('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('customer_code', 'like', "%{$s}%"))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'customer_type' => 'required|in:individual,company',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer->load('salesOrders'));
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }
}
