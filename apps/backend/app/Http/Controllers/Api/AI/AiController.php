<?php
namespace App\Http\Controllers\Api\AI;
use App\Http\Controllers\Controller;
use App\Models\AI\{AiInsight,AiFeedback};
use App\Services\AI\AiEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class AiController extends Controller {
 public function __construct(private AiEngine $engine){}
 public function capabilities(): JsonResponse {return response()->json(['provider'=>config('module_settings.ai.general.provider','local'),'capabilities'=>['assistant','forecast','anomaly_detection','recommendation','ocr','scoring'],'modules'=>array_keys(config('module_settings',[]))]);}
 public function insights(Request $r): JsonResponse {$q=AiInsight::query()->when($r->module,fn($q,$v)=>$q->where('module',$v))->when($r->type,fn($q,$v)=>$q->where('type',$v))->latest();return response()->json($q->paginate($r->integer('per_page',25)));}
 public function analyze(Request $r,string $module): JsonResponse {$d=$r->validate(['capability'=>'required|in:assistant,forecast,anomaly_detection,recommendation,ocr,scoring','rows'=>'array','prompt'=>'nullable|string','context'=>'array']);$result=$this->engine->run($module,$d['capability'],$d);$insight=$this->engine->createInsight($module,$d['capability'],ucwords(str_replace('_',' ',$d['capability'])),'AI analysis completed',$result['result']);return response()->json(['data'=>$result['result'],'run'=>$result['run'],'insight'=>$insight]);}
 public function markRead(AiInsight $insight): JsonResponse {$insight->update(['is_read'=>true]);return response()->json(['data'=>$insight]);}
 public function feedback(Request $r,AiInsight $insight): JsonResponse {$d=$r->validate(['rating'=>'required|integer|min:1|max:5','comment'=>'nullable|string']);$d['insight_id']=$insight->id;$d['user_id']=auth()->id();return response()->json(['data'=>AiFeedback::create($d)],201);}
}
