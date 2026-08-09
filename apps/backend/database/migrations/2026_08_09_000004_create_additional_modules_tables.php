<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('module_records', function(Blueprint $table){
   $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('module',50); $table->string('record_type',50); $table->string('reference')->nullable(); $table->string('status',30)->default('draft'); $table->json('data')->nullable(); $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
   $table->index(['tenant_id','module','record_type','status']);
  });
  Schema::create('module_kpis', function(Blueprint $table){
   $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('module',50); $table->string('metric_key'); $table->string('label'); $table->decimal('value',18,4)->default(0); $table->string('unit',20)->nullable(); $table->date('period_start')->nullable(); $table->date('period_end')->nullable(); $table->json('breakdown')->nullable(); $table->timestamps();
   $table->index(['tenant_id','module','metric_key']);
  });
 } public function down(): void { Schema::dropIfExists('module_kpis'); Schema::dropIfExists('module_records'); }
};
