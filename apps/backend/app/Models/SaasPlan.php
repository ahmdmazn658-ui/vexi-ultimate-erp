<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SaasPlan extends Model {protected $guarded=['id'];protected $casts=['included_modules'=>'array','limits'=>'array','is_active'=>'boolean'];}
