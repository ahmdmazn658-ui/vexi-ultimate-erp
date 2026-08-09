<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entries = JournalEntry::query()
            ->when($request->query('project_id'), fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('lines.account:id,account_code,account_name', 'project:id,name')
            ->latest('entry_date')
            ->paginate($request->integer('per_page', 25));

        return response()->json($entries);
    }

    /**
     * POST /api/v1/accounting/journal-entries
     * Body: { entry_number, entry_date, project_id?, description?, lines: [{account_id, debit, credit, memo}] }
     * القيد لازم يكون متوازن: مجموع المدين = مجموع الدائن
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_number' => 'required|string|unique:journal_entries,entry_number',
            'entry_date' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.memo' => 'nullable|string',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        // مقارنة بهامش بدل `!==` — المقارنة الصارمة بين float ممكن ترفض قيد متوازن
        // فعلاً بسبب التمثيل الثنائي (نفس السبب اللي في JournalEntry::isBalanced)
        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw ValidationException::withMessages([
                'lines' => ["القيد غير متوازن: مجموع المدين ({$totalDebit}) لا يساوي مجموع الدائن ({$totalCredit})"],
            ]);
        }

        $entry = DB::transaction(function () use ($validated, $request) {
            $entry = JournalEntry::create([
                'entry_number' => $validated['entry_number'],
                'entry_date' => $validated['entry_date'],
                'project_id' => $validated['project_id'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()?->id,
            ]);

            $entry->lines()->createMany($validated['lines']);

            return $entry;
        });

        return response()->json($entry->load('lines.account'), 201);
    }

    public function show(JournalEntry $journalEntry): JsonResponse
    {
        return response()->json($journalEntry->load('lines.account', 'project', 'creator:id,name'));
    }

    /**
     * POST /api/v1/accounting/journal-entries/{id}/post
     */
    public function post(JournalEntry $journalEntry): JsonResponse
    {
        if (! $journalEntry->isBalanced()) {
            throw ValidationException::withMessages([
                'entry' => ['لا يمكن ترحيل قيد غير متوازن.'],
            ]);
        }

        $journalEntry->update(['status' => 'posted']);

        return response()->json($journalEntry);
    }

    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->status === 'posted') {
            throw ValidationException::withMessages([
                'entry' => ['لا يمكن حذف قيد مُرحّل بالفعل.'],
            ]);
        }

        $journalEntry->delete();

        return response()->json(null, 204);
    }
}
