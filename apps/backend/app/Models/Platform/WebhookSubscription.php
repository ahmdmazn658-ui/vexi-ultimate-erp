<?php
namespace App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
class WebhookSubscription extends Model { protected \webhook_subscriptions = 'webhook_subscriptions'; protected \ = ['id']; protected \ = ['mapping'=>'array','errors'=>'array','options'=>'array','columns'=>'array','filters'=>'array','trigger'=>'array','conditions'=>'array','actions'=>'array','steps'=>'array','credentials'=>'array','settings'=>'array','events'=>'array','recipients'=>'array','old_values'=>'array','new_values'=>'array','is_active'=>'boolean']; }
