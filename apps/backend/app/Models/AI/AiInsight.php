<?php
namespace App\Models\AI;
use Illuminate\Database\Eloquent\Model;
class AiInsight extends Model {protected $table='ai_insights';protected $guarded=['id'];protected $casts=['data'=>'array','actions'=>'array','is_read'=>'boolean'];}
