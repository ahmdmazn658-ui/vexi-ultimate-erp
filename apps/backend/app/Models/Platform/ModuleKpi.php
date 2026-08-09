<?php
namespace App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
class ModuleKpi extends Model { protected $guarded=['id']; protected $casts=['breakdown'=>'array']; }
