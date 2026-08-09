<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AI\AiController as C;
Route::middleware('auth:sanctum')->prefix('v1/ai')->group(function(){Route::get('capabilities',[C::class,'capabilities']);Route::get('insights',[C::class,'insights']);Route::post('{module}/analyze',[C::class,'analyze']);Route::post('insights/{insight}/read',[C::class,'markRead']);Route::post('insights/{insight}/feedback',[C::class,'feedback']);});
