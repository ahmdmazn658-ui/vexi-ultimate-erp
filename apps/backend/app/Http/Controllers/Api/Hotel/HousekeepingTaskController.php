<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\HousekeepingTask;
use App\Models\Hotel\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HousekeepingTaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = HousekeepingTask::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('assigned_to'), fn ($q, $id) => $q->where('assigned_to', $id))
            ->when($request->boolean('mine') && $request->user(), fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->with(['room:id,room_number,status', 'assignee:id,name'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_at')
            ->paginate($request->integer('per_page', 50));

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'task_type' => 'required|in:cleaning,inspection,maintenance,deep_clean',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'due_at' => 'nullable|date',
        ]);

        return response()->json(HousekeepingTask::create($validated), 201);
    }

    public function update(Request $request, HousekeepingTask $housekeepingTask): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,in_progress,done,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($housekeepingTask, $validated) {
            if (($validated['status'] ?? null) === 'done') {
                $validated['completed_at'] = now();
            }

            $housekeepingTask->update($validated);

            // لما مهمة تنظيف تخلص، الغرفة ترجع نظيفة تلقائيًا
            if ($housekeepingTask->status === 'done' && $housekeepingTask->task_type === 'cleaning') {
                $room = $housekeepingTask->room;
                $newStatus = str_starts_with($room->status, 'occupied') ? 'occupied_clean' : 'vacant_clean';
                $room->update(['status' => $newStatus]);
            }
        });

        return response()->json($housekeepingTask->fresh('room'));
    }

    public function destroy(HousekeepingTask $housekeepingTask): JsonResponse
    {
        $housekeepingTask->delete();

        return response()->json(null, 204);
    }
}
