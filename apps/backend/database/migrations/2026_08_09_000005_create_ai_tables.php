<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('ai_insights', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id')->nullable();$t->string('module',60);$t->string('type',30);$t->string('severity',20)->default('info');$t->string('title');$t->text('summary');$t->json('data')->nullable();$t->json('actions')->nullable();$t->boolean('is_read')->default(false);$t->timestamps();$t->index(['tenant_id','module','type','is_read']);});
  Schema::create('ai_runs', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id')->nullable();$t->string('module',60);$t->string('capability',40);$t->string('provider',30)->default('local');$t->string('status',20)->default('queued');$t->integer('input_tokens')->default(0);$t->integer('output_tokens')->default(0);$t->decimal('cost',12,6)->default(0);$t->json('input')->nullable();$t->json('output')->nullable();$t->text('error')->nullable();$t->unsignedBigInteger('created_by')->nullable();$t->timestamps();$t->index(['tenant_id','module','capability','status']);});
  Schema::create('ai_feedback', function(Blueprint $t){$t->id();$t->unsignedBigInteger('insight_id')->nullable();$t->unsignedBigInteger('user_id')->nullable();$t->tinyInteger('rating')->nullable();$t->text('comment')->nullable();$t->timestamps();});
 }
 public function down(): void {foreach(['ai_feedback','ai_runs','ai_insights'] as $t)Schema::dropIfExists($t);}
};
