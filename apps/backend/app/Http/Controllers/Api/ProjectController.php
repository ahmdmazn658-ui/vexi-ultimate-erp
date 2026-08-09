<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->query('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('project_code', 'like', "%{$s}%"))
            ->with('manager:id,name,email')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_code' => 'required|string|unique:projects,project_code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:construction,real_estate,infrastructure,service,other',
            'client_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'contract_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('manager:id,name,email', 'contracts', 'properties'));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'contract_value' => 'nullable|numeric|min:0',
            'expected_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'status' => 'sometimes|in:planning,in_progress,on_hold,completed,cancelled',
            'progress_percent' => 'sometimes|integer|min:0|max:100',
            'project_manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
