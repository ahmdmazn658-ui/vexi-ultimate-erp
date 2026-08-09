<?php
namespace App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
class WorkflowDefinition extends Model { protected \workflow_definitions = 'workflow_definitions'; protected \ = ['id']; protected \ = ['mapping'=>'array','errors'=>'array','options'=>'array','columns'=>'array','filters'=>'array','trigger'=>'array','conditions'=>'array','actions'=>'array','steps'=>'array','credentials'=>'array','settings'=>'array','events'=>'array','recipients'=>'array','old_values'=>'array','new_values'=>'array','is_active'=>'boolean']; }
