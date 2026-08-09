<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TenantRequirement extends Model {protected $guarded=['id'];protected $casts=['acceptance_criteria'=>'array'];}
