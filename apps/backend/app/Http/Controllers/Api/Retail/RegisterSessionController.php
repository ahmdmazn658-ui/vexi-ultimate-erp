<?php

namespace App\Http\Controllers\Api\Retail;

use App\Http\Controllers\Controller;
use App\Models\Retail\RegisterSession;
use App\Support\Retail\PosSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterSessionController extends Controller
{
    public function __construct(private readonly PosSaleService $posSales) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = RegisterSession::query()
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->with('warehouse:id,name', 'openedBy:id,name')
            ->latest('opened_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json($sessions);
    }

    /**
     * POST /api/v1/retail/register-sessions
     * بيفتح شيفت جديد بجرد نقدية افتتاحي. لازم يتقفل الشيفت الحالي لنفس
     * الماكينة (لو موجود) قبل ما تفتح واحد جديد.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'register_name' => 'required|string|max:255',
            'warehouse_id' => 'required|exists:warehouses,id',
            'opening_cash' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $hasOpenSession = RegisterSession::query()
            ->where('register_name', $validated['register_name'])
            ->where('status', 'open')
            ->exists();

        if ($hasOpenSession) {
            throw ValidationException::withMessages([
                'register_name' => ['فيه شيفت مفتوح بالفعل على الماكينة دي — لازم يتقفل الأول.'],
            ]);
        }

        $session = RegisterSession::create($validated + [
            'opened_by' => $request->user()?->id,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        return response()->json($session->load('warehouse', 'openedBy'), 201);
    }

    public function show(RegisterSession $registerSession): JsonResponse
    {
        return response()->json(
            $registerSession->load('warehouse', 'openedBy', 'sales.items.product', 'sales.customer')
        );
    }

    /**
     * POST /api/v1/retail/register-sessions/{id}/close
     * Body: { closing_cash }
     * يحسب النقدية المتوقعة من إجمالي مبيعات الكاش ويقارنها بالفعلي (تسوية الدرج).
     */
    public function close(Request $request, RegisterSession $registerSession): JsonResponse
    {
        if ($registerSession->status !== 'open') {
            throw ValidationException::withMessages([
                'status' => ['الشيفت ده مقفول بالفعل.'],
            ]);
        }

        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $session = $this->posSales->closeSession($registerSession, (float) $validated['closing_cash']);

        return response()->json($session->fresh(['warehouse', 'openedBy']));
    }
}
