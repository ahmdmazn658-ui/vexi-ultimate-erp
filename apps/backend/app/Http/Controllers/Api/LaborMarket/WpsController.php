<?php

namespace App\Http\Controllers\Api\LaborMarket;

use App\Http\Controllers\Controller;
use App\Models\LaborMarket\WpsFile;
use App\Models\LaborMarket\WpsFileRecord;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WpsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WpsFile::latest()->paginate(24));
    }

    public function show(WpsFile $wpsFile): JsonResponse
    {
        return response()->json(['data' => $wpsFile->load('records')]);
    }

    /**
     * توليد ملف WPS/SIF
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'bank_code' => 'required|string',
        ]);

        $file = WpsFile::create([
            'year' => $request->year,
            'month' => $request->month,
            'bank_code' => $request->bank_code,
            'employer_mol_id' => config('company.mol_id'),
            'file_type' => 'SIF',
            'status' => 'draft',
        ]);

        // Populate with employee records
        $employees = Employee::where('status', 'active')->get();
        $totalAmount = 0;

        foreach ($employees as $emp) {
            $netSalary = ($emp->basic_salary ?? 0) + ($emp->housing_allowance ?? 0)
                + ($emp->other_allowances ?? 0) - ($emp->deductions ?? 0);

            WpsFileRecord::create([
                'wps_file_id' => $file->id,
                'employee_id' => $emp->id,
                'employee_id_number' => $emp->id_number ?? '',
                'employee_name' => $emp->name,
                'bank_code' => $emp->bank_code ?? $request->bank_code,
                'iban' => $emp->iban ?? '',
                'basic_salary' => $emp->basic_salary ?? 0,
                'housing_allowance' => $emp->housing_allowance ?? 0,
                'other_allowances' => $emp->other_allowances ?? 0,
                'deductions' => $emp->deductions ?? 0,
                'net_salary' => $netSalary,
            ]);

            $totalAmount += $netSalary;
        }

        $file->update([
            'total_records' => $employees->count(),
            'total_amount' => $totalAmount,
        ]);

        return response()->json(['data' => $file->load('records')], 201);
    }

    /**
     * إرسال ملف WPS لمدد
     */
    public function submit(WpsFile $wpsFile): JsonResponse
    {
        $wpsFile->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        return response()->json(['message' => 'تم إرسال ملف حماية الأجور', 'data' => $wpsFile]);
    }

    /**
     * تحميل ملف SIF بصيغة البنك
     */
    public function downloadSif(WpsFile $wpsFile): JsonResponse
    {
        // Generate SIF format file content
        $records = $wpsFile->records;
        $sifContent = [];

        foreach ($records as $record) {
            $sifContent[] = [
                'id_number' => $record->employee_id_number,
                'name' => $record->employee_name,
                'bank' => $record->bank_code,
                'iban' => $record->iban,
                'amount' => $record->net_salary,
                'basic' => $record->basic_salary,
                'housing' => $record->housing_allowance,
                'other' => $record->other_allowances,
                'deductions' => $record->deductions,
            ];
        }

        return response()->json(['sif_data' => $sifContent, 'file' => $wpsFile]);
    }
}
