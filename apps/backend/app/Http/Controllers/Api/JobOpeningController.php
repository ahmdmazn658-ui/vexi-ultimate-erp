<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $openings = JobOpening::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('department'), fn ($q, $d) => $q->where('department', $d))
            ->withCount('candidates')
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($openings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'employment_type' => 'nullable|in:full_time,part_time,contractor',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $opening = JobOpening::create([
            ...$validated,
            'status' => 'open',
            'created_by' => $request->user()?->id,
        ]);

        return response()->json($opening, 201);
    }

    public function show(JobOpening $jobOpening): JsonResponse
    {
        return response()->json($jobOpening->load('candidates', 'project'));
    }

    public function update(Request $request, JobOpening $jobOpening): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:open,on_hold,closed',
        ]);

        $jobOpening->update($validated);

        return response()->json($jobOpening);
    }

    public function destroy(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening->delete();

        return response()->json(null, 204);
    }
}
