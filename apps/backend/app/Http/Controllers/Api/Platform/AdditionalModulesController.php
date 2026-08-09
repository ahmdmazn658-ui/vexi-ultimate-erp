<?php
namespace App\Http\Controllers\Api\Platform;
use App\Http\Controllers\Controller;
use App\Models\Platform\ModuleRecord;
use App\Models\Platform\ModuleKpi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class AdditionalModulesController extends Controller {
 public function catalog(): JsonResponse { return response()->json(['data'=>config('additional_modules.catalog',[])]); }
 public function index(Request $r,string $module): JsonResponse { $q=ModuleRecord::where('module',$module)->when($r->record_type,fn($q,$v)=>$q->where('record_type',$v))->when($r->status,fn($q,$v)=>$q->where('status',$v)); return response()->json($q->latest()->paginate($r->integer('per_page',25))); }
 public function store(Request $r,string $module): JsonResponse { $data=$r->validate(['record_type'=>'required|string','reference'=>'nullable|string','status'=>'nullable|string','data'=>'nullable|array']); $data['module']=$module; $data['created_by']=auth()->id(); $record=ModuleRecord::create($data); return response()->json(['data'=>$record],201); }
 public function show(ModuleRecord $record): JsonResponse { return response()->json(['data'=>$record]); }
 public function update(Request $r,ModuleRecord $record): JsonResponse { $record->update($r->validate(['reference'=>'nullable|string','status'=>'nullable|string','data'=>'nullable|array'])); return response()->json(['data'=>$record]); }
 public function destroy(ModuleRecord $record): JsonResponse { $record->delete(); return response()->json(['message'=>'Deleted']); }
 public function kpis(Request $r,string $module): JsonResponse { return response()->json(['data'=>ModuleKpi::where('module',$module)->latest()->get()]); }
}
