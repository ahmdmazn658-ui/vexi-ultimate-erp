<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $candidates = Candidate::query()
            ->when($request->query('job_opening_id'), fn ($q, $id) => $q->where('job_opening_id', $id))
            ->when($request->query('stage'), fn ($q, $s) => $q->where('stage', $s))
            ->with('jobOpening:id,title')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($candidates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_opening_id' => 'required|exists:job_openings,id',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'resume_path' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $candidate = Candidate::create([...$validated, 'stage' => 'applied']);

        return response()->json($candidate, 201);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        return response()->json($candidate->load('jobOpening', 'convertedEmployee'));
    }

    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        $validated = $request->validate([
            'stage' => 'sometimes|in:applied,screening,interview,offer,hired,rejected',
            'notes' => 'nullable|string',
        ]);

        $candidate->update($validated);

        return response()->json($candidate);
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        $candidate->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/recruitment/candidates/{id}/hire
     * Body: { employee_code, position, department, hire_date, basic_salary }
     * يحوّل المتقدم لموظف حقيقي في hr/employees ويقفل حالته hired.
     */
    public function hire(Request $request, Candidate $candidate): JsonResponse
    {
        if ($candidate->stage === 'hired') {
            throw ValidationException::withMessages([
                'candidate' => ['المتقدم ده اتوظف بالفعل.'],
            ]);
        }

        $validated = $request->validate([
            'employee_code' => 'nullable|string|unique:employees,employee_code',
            'position' => 'required|string|max:255',
            'department' => 'required|in:engineering,finance,procurement,hr,operations,management,other',
            'hire_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $candidate = DB::transaction(function () use ($validated, $candidate) {
            $employee = Employee::create([
                'employee_code' => $validated['employee_code'] ?? 'EMP-'.Str::upper(Str::random(8)),
                'full_name' => $candidate->full_name,
                'position' => $validated['position'],
                'department' => $validated['department'],
                'basic_salary' => $validated['basic_salary'],
                'hire_date' => $validated['hire_date'],
                'status' => 'active',
            ]);

            $candidate->update([
                'stage' => 'hired',
                'converted_employee_id' => $employee->id,
            ]);

            return $candidate;
        });

        return response()->json($candidate->fresh()->load('convertedEmployee'));
    }
}
