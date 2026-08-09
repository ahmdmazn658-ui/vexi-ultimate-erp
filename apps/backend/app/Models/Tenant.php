<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tenant extends Model {protected $guarded=['id'];protected $casts=['settings'=>'array'];public function users(){return $this->belongsToMany(User::class,'tenant_users')->withPivot(['role','is_owner']);}public function modules(){return $this->hasMany(TenantModule::class);}public function requirements(){return $this->hasMany(TenantRequirement::class);}}
