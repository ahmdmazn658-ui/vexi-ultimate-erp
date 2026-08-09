<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TenantModule extends Model {protected $guarded=['id'];protected $casts=['features'=>'array','overrides'=>'array','is_enabled'=>'boolean'];}
