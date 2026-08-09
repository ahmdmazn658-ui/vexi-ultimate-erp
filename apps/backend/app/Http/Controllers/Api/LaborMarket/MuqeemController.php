<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\MuqeemRecord;
use App\Models\LaborMarket\MuqeemTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MuqeemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MuqeemRecord::with('employee')->latest()->paginate(50));
    }

    public function show(MuqeemRecord $record): JsonResponse
    {
        return response()->json(['data' => $record->load(['employee', 'transactions'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'iqama_number' => 'required|string|max:20',
            'nationality' => 'required|string|max:5',
            'sponsor_id' => 'required|string',
            'iqama_issue_date' => 'required|date',
            'iqama_expiry_date' => 'required|date',
            'occupation_code' => 'nullable|string',
            'occupation_name' => 'nullable|string',
        ]);

        $record = MuqeemRecord::create($data);
        return response()->json(['data' => $record], 201);
    }

    /**
     * الإقامات المنتهية أو القريبة من الانتهاء
     */
    public function expiring(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $records = MuqeemRecord::where('iqama_expiry_date', '<=', now()->addDays($days))
            ->where('iqama_status', 'valid')
            ->with('employee')
            ->orderBy('iqama_expiry_date')
            ->get();

        return response()->json(['data' => $records]);
    }

    /**
     * طلب تجديد الإقامة
     */
    public function requestRenewal(MuqeemRecord $record): JsonResponse
    {
        $transaction = MuqeemTransaction::create([
            'muqeem_record_id' => $record->id,
            'transaction_type' => 'renewal',
            'status' => 'pending',
            'request_date' => now(),
            'fees' => 650, // رسوم التجديد
        ]);

        return response()->json(['data' => $transaction], 201);
    }

    /**
     * طلب تأشيرة خروج وعودة
     */
    public function requestExitReentry(Request $request, MuqeemRecord $record): JsonResponse
    {
        $transaction = MuqeemTransaction::create([
            'muqeem_record_id' => $record->id,
            'transaction_type' => 'exit_reentry',
            'status' => 'pending',
            'request_date' => now(),
            'fees' => $request->input('type') === 'multiple' ? 500 : 200,
            'details' => ['type' => $request->input('type', 'single'), 'duration_days' => $request->input('duration', 60)],
        ]);

        return response()->json(['data' => $transaction], 201);
    }

    /**
     * طلب خروج نهائي
     */
    public function requestFinalExit(MuqeemRecord $record): JsonResponse
    {
        $transaction = MuqeemTransaction::create([
            'muqeem_record_id' => $record->id,
            'transaction_type' => 'final_exit',
            'status' => 'pending',
            'request_date' => now(),
        ]);

        $record->update(['final_exit_issued' => true]);

        return response()->json(['data' => $transaction], 201);
    }

    /**
     * نقل كفالة
     */
    public function requestTransfer(Request $request, MuqeemRecord $record): JsonResponse
    {
        $transaction = MuqeemTransaction::create([
            'muqeem_record_id' => $record->id,
            'transaction_type' => 'transfer',
            'status' => 'pending',
            'request_date' => now(),
            'fees' => 2000,
            'details' => ['new_sponsor' => $request->input('new_sponsor')],
        ]);

        return response()->json(['data' => $transaction], 201);
    }
}
