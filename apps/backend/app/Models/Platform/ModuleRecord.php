<?php
namespace App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
class ModuleRecord extends Model { protected $guarded=['id']; protected $casts=['data'=>'array']; }
