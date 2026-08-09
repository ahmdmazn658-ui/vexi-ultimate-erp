<?php
namespace App\Http\Middleware;
use Closure;use Illuminate\Http\Request;use Symfony\Component\HttpFoundation\Response;use App\Models\Tenant;
class ResolveTenant {public function handle(Request $request,Closure $next):Response {$user=$request->user();$tenantId=$request->header('X-Tenant-ID')??$user?->tenant_id;if(!$user||!$tenantId){return response()->json(['message'=>'Tenant context is required.'],422);} $tenant=Tenant::find($tenantId);if(!$tenant||($user->tenant_id && (int)$user->tenant_id!==(int)$tenant->id)){return response()->json(['message'=>'Tenant access denied.'],403);} app()->instance(Tenant::class,$tenant);return $next($request);}}
