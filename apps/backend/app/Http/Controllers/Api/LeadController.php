<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leads = Lead::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('assigned_to'), fn ($q, $id) => $q->where('assigned_to', $id))
            ->when($request->query('search'), fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%");
            }))
            ->with('assignedTo:id,name')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($leads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,unqualified,converted',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create($validated);

        return response()->json($lead, 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        return response()->json($lead->load('assignedTo', 'opportunities', 'convertedCustomer'));
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'sometimes|in:new,contacted,qualified,unqualified,converted',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return response()->json($lead);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/crm/leads/{id}/convert
     * يحوّل الـ lead لعميل فعلي في وحدة المبيعات (customers) ويقفل حالته converted.
     */
    public function convert(Lead $lead): JsonResponse
    {
        if ($lead->status === 'converted') {
            throw ValidationException::withMessages([
                'lead' => ['الـ lead ده اتحول بالفعل لعميل.'],
            ]);
        }

        $lead = DB::transaction(function () use ($lead) {
            $customer = Customer::create([
                'customer_code' => 'CUST-'.Str::upper(Str::random(8)),
                'name' => $lead->company_name ?: $lead->name,
                'contact_person' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'customer_type' => 'company',
                'is_active' => true,
            ]);

            $lead->update([
                'status' => 'converted',
                'converted_customer_id' => $customer->id,
            ]);

            return $lead;
        });

        return response()->json($lead->fresh()->load('convertedCustomer'));
    }
}
