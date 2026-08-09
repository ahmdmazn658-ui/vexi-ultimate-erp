<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('priority'), fn ($q, $p) => $q->where('priority', $p))
            ->when($request->query('assigned_to'), fn ($q, $id) => $q->where('assigned_to', $id))
            ->when($request->query('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->with(['customer:id,name', 'assignedTo:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            ...$validated,
            'ticket_number' => 'TKT-'.now()->format('Ymd').'-'.Str::upper(Str::random(5)),
            'status' => 'open',
            'created_by' => $request->user()?->id,
        ]);

        return response()->json($ticket, 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json($ticket->load('customer', 'assignedTo', 'creator'));
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update($validated);

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(null, 204);
    }

    /**
     * POST /api/v1/helpdesk/tickets/{id}/resolve
     */
    public function resolve(Ticket $ticket): JsonResponse
    {
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json($ticket);
    }

    /**
     * POST /api/v1/helpdesk/tickets/{id}/close
     */
    public function close(Ticket $ticket): JsonResponse
    {
        $ticket->update(['status' => 'closed']);

        return response()->json($ticket);
    }
}
