<?php
namespace App\Models\AI;
use Illuminate\Database\Eloquent\Model;
class AiRun extends Model {protected $table='ai_runs';protected $guarded=['id'];protected $casts=['input'=>'array','output'=>'array'];}
