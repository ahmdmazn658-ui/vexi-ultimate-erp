<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('tenants', function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->string('legal_name')->nullable();$t->string('vat_number')->nullable();$t->string('cr_number')->nullable();$t->string('country',2)->default('SA');$t->string('timezone')->default('Asia/Riyadh');$t->string('currency',3)->default('SAR');$t->string('status',20)->default('trial');$t->string('plan_key')->default('starter');$t->json('settings')->nullable();$t->timestamps();$t->index(['status','plan_key']);});
  Schema::create('saas_plans', function(Blueprint $t){$t->id();$t->string('key')->unique();$t->string('name');$t->string('name_ar');$t->decimal('monthly_price',12,2)->default(0);$t->decimal('annual_price',12,2)->default(0);$t->integer('max_users')->nullable();$t->integer('max_storage_gb')->nullable();$t->json('included_modules');$t->json('limits')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('tenant_subscriptions', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id');$t->unsignedBigInteger('plan_id');$t->string('status',20)->default('trialing');$t->string('billing_cycle',20)->default('monthly');$t->date('starts_at');$t->date('ends_at')->nullable();$t->date('trial_ends_at')->nullable();$t->string('external_id')->nullable();$t->timestamps();$t->index(['tenant_id','status']);});
  Schema::create('tenant_modules', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id');$t->string('module',80);$t->boolean('is_enabled')->default(true);$t->string('edition',30)->default('standard');$t->json('features')->nullable();$t->json('overrides')->nullable();$t->timestamps();$t->unique(['tenant_id','module']);});
  Schema::create('tenant_requirements', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id');$t->string('category',40);$t->string('key',80);$t->text('requirement');$t->string('priority',20)->default('medium');$t->string('status',20)->default('requested');$t->json('acceptance_criteria')->nullable();$t->unsignedBigInteger('requested_by')->nullable();$t->timestamps();$t->index(['tenant_id','status']);});
  Schema::create('tenant_users', function(Blueprint $t){$t->id();$t->unsignedBigInteger('tenant_id');$t->unsignedBigInteger('user_id');$t->string('role')->default('member');$t->boolean('is_owner')->default(false);$t->timestamps();$t->unique(['tenant_id','user_id']);});
 }
 public function down(): void {foreach(['tenant_users','tenant_requirements','tenant_modules','tenant_subscriptions','saas_plans','tenants'] as $t)Schema::dropIfExists($t);}
};
