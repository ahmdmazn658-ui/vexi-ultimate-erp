<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Platform\AdditionalModulesController as C;
Route::middleware('auth:sanctum')->prefix('v1/additional-modules')->group(function(){
 Route::get('catalog',[C::class,'catalog']);
 Route::get('{module}/kpis',[C::class,'kpis']);
 Route::get('{module}',[C::class,'index']); Route::post('{module}',[C::class,'store']);
 Route::get('records/{record}',[C::class,'show']); Route::put('records/{record}',[C::class,'update']); Route::delete('records/{record}',[C::class,'destroy']);
});
