<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QualityInspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QualityInspectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $inspections = QualityInspection::query()
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('result'), fn ($q, $r) => $q->where('result', $r))
            ->with(['project:id,name', 'inspector:id,name'])
            ->latest('inspection_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($inspections);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'subject' => 'required|string|max:255',
            'inspection_date' => 'required|date',
            'inspector_id' => 'nullable|exists:users,id',
            'findings' => 'nullable|string',
        ]);

        $inspection = QualityInspection::create([
            ...$validated,
            'inspection_code' => 'QI-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'result' => 'pending',
        ]);

        return response()->json($inspection, 201);
    }

    public function show(QualityInspection $qualityInspection): JsonResponse
    {
        return response()->json($qualityInspection->load('project', 'inspector'));
    }

    /**
     * PUT /api/v1/quality/inspections/{id}
     * بيستخدم غالبًا لتسجيل نتيجة التفتيش (result) والملاحظات والإجراء التصحيحي.
     */
    public function update(Request $request, QualityInspection $qualityInspection): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'result' => 'sometimes|in:pending,passed,failed,needs_rework',
            'findings' => 'nullable|string',
            'corrective_action' => 'nullable|string',
        ]);

        $qualityInspection->update($validated);

        return response()->json($qualityInspection);
    }

    public function destroy(QualityInspection $qualityInspection): JsonResponse
    {
        $qualityInspection->delete();

        return response()->json(null, 204);
    }
}
