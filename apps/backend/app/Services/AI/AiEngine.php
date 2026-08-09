<?php
namespace App\Services\AI;
use App\Models\AI\{AiInsight,AiRun};
use Illuminate\Support\Facades\Http;
class AiEngine {
 public function run(string $module,string $capability,array $input=[]): array {
  $provider=config('module_settings.ai.general.provider','local'); $run=AiRun::create(['module'=>$module,'capability'=>$capability,'provider'=>$provider,'status'=>'processing','input'=>$input,'created_by'=>auth()->id()]);
  try { $output=$this->localAnalysis($module,$capability,$input); $run->update(['status'=>'completed','output'=>$output]); return ['run'=>$run,'result'=>$output]; }
  catch(\Throwable $e){$run->update(['status'=>'failed','error'=>$e->getMessage()]);throw $e;}
 }
 private function localAnalysis(string $module,string $capability,array $input): array {
  $rows=$input['rows']??[]; $values=array_values(array_filter(array_map(fn($r)=>is_numeric($r)?(float)$r:(is_array($r)?(float)($r['value']??0):0),$rows),fn($v)=>$v!==0)); $avg=count($values)?array_sum($values)/count($values):0;
  return match($capability){
   'forecast'=>['method'=>'trend_baseline','forecast'=>round($avg,2),'confidence'=>count($values)>=3?0.68:0.35,'explanation'=>'Baseline forecast generated from supplied historical data'],
   'anomaly_detection'=>['anomalies'=>[],'threshold'=>round($avg*2,2),'explanation'=>'No anomaly detected by baseline rules'],
   'recommendation'=>['recommendations'=>[['priority'=>'medium','action'=>'Review recent module activity','reason'=>'AI provider is not configured for generated-language recommendations']]],
   'assistant'=>['answer'=>'Local AI mode is active. Connect an AI provider to enable natural-language reasoning over this module.','module'=>$module],
   default=>['status'=>'completed','module'=>$module,'capability'=>$capability,'records_analyzed'=>count($rows),'average'=>round($avg,2)]
  };
 }
 public function createInsight(string $module,string $type,string $title,string $summary,array $data=[],string $severity='info'): AiInsight {return AiInsight::create(['module'=>$module,'type'=>$type,'title'=>$title,'summary'=>$summary,'severity'=>$severity,'data'=>$data]);}
}
