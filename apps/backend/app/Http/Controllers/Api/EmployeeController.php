<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = Employee::query()
            ->when($request->query('department'), fn ($q, $d) => $q->where('department', $d))
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('search'), fn ($q, $s) => $q->where('full_name', 'like', "%{$s}%"))
            ->with('project:id,name')
            ->latest('hire_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|unique:employees,employee_code',
            'full_name' => 'required|string|max:255',
            'national_id' => 'nullable|string|unique:employees,national_id',
            'position' => 'required|string|max:255',
            'department' => 'required|in:engineering,finance,procurement,hr,operations,management,other',
            'project_id' => 'nullable|exists:projects,id',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contractor,daily_wage',
            'phone' => 'nullable|string|max:50',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee, 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee->load('project', 'user:id,name,email'));
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'position' => 'sometimes|string|max:255',
            'department' => 'sometimes|in:engineering,finance,procurement,hr,operations,management,other',
            'project_id' => 'nullable|exists:projects,id',
            'basic_salary' => 'sometimes|numeric|min:0',
            'termination_date' => 'nullable|date',
            'status' => 'sometimes|in:active,on_leave,terminated',
            'phone' => 'nullable|string|max:50',
        ]);

        $employee->update($validated);

        return response()->json($employee);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(null, 204);
    }
}
