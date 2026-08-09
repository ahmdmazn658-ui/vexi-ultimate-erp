<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Tenant,SaasPlan,TenantModule,TenantRequirement,TenantSubscription,User};
use Illuminate\Http\Request;use Illuminate\Http\JsonResponse;use Illuminate\Support\Str;
class TenantController extends Controller {
 public function plans(): JsonResponse {return response()->json(['data'=>SaasPlan::where('is_active',true)->get()]);}
 public function current(Request $r): JsonResponse {$t=app(Tenant::class);return response()->json(['data'=>$t->load(['modules','requirements'])]);}
 public function update(Request $r): JsonResponse {$t=app(Tenant::class);$t->update($r->validate(['name'=>'string','legal_name'=>'nullable|string','vat_number'=>'nullable|string','cr_number'=>'nullable|string','timezone'=>'string','currency'=>'string','settings'=>'array']));return response()->json(['data'=>$t]);}
 public function modules(): JsonResponse {return response()->json(['data'=>app(Tenant::class)->modules()->get()]);}
 public function setModule(Request $r,string $module): JsonResponse {$d=$r->validate(['is_enabled'=>'boolean','edition'=>'nullable|string','features'=>'array','overrides'=>'array']);$m=TenantModule::updateOrCreate(['tenant_id'=>app(Tenant::class)->id,'module'=>$module],$d);return response()->json(['data'=>$m]);}
 public function requirements(): JsonResponse {return response()->json(['data'=>app(Tenant::class)->requirements()->latest()->get()]);}
 public function addRequirement(Request $r): JsonResponse {$d=$r->validate(['category'=>'required|string','key'=>'required|string','requirement'=>'required|string','priority'=>'in:low,medium,high,critical','acceptance_criteria'=>'array']);$d['tenant_id']=app(Tenant::class)->id;$d['requested_by']=auth()->id();return response()->json(['data'=>TenantRequirement::create($d)],201);}
 public function updateRequirement(Request $r,TenantRequirement $requirement): JsonResponse {$this->assertTenant($requirement->tenant_id);$requirement->update($r->validate(['status'=>'in:requested,approved,in_progress,delivered,rejected','priority'=>'in:low,medium,high,critical','acceptance_criteria'=>'array']));return response()->json(['data'=>$requirement]);}
 public function subscription(): JsonResponse {$s=TenantSubscription::where('tenant_id',app(Tenant::class)->id)->with('plan')->latest()->first();return response()->json(['data'=>$s]);}
 private function assertTenant($id): void {abort_unless((int)$id===(int)app(Tenant::class)->id,403);}
}
