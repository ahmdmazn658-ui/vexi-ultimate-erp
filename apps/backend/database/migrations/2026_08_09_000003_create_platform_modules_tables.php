<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('entity');
            $table->string('file_name'); $table->string('file_path')->nullable(); $table->string('format', 20);
            $table->string('status', 20)->default('draft'); $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0); $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0); $table->json('mapping')->nullable();
            $table->json('errors')->nullable(); $table->json('options')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
            $table->index(['tenant_id', 'entity', 'status']);
        });
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('entity');
            $table->string('format', 20); $table->string('status', 20)->default('queued');
            $table->json('columns')->nullable(); $table->json('filters')->nullable(); $table->string('file_path')->nullable();
            $table->integer('total_rows')->default(0); $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
            $table->index(['tenant_id', 'entity', 'status']);
        });
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('name');
            $table->string('entity'); $table->boolean('is_active')->default(true); $table->json('trigger')->nullable();
            $table->json('conditions')->nullable(); $table->json('actions')->nullable(); $table->integer('priority')->default(0);
            $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps(); $table->index(['tenant_id', 'entity', 'is_active']);
        });
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('entity'); $table->unsignedBigInteger('entity_id');
            $table->string('workflow')->nullable(); $table->string('status', 20)->default('pending'); $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); $table->timestamp('approved_at')->nullable(); $table->text('comment')->nullable();
            $table->json('steps')->nullable(); $table->timestamps(); $table->index(['tenant_id', 'entity', 'entity_id', 'status']);
        });
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('name'); $table->string('provider');
            $table->string('category', 30)->default('api'); $table->string('status', 20)->default('inactive');
            $table->json('credentials')->nullable(); $table->json('settings')->nullable(); $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error')->nullable(); $table->unsignedBigInteger('created_by')->nullable(); $table->timestamps();
            $table->index(['tenant_id', 'provider', 'status']);
        });
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('name'); $table->string('endpoint');
            $table->string('secret')->nullable(); $table->json('events'); $table->boolean('is_active')->default(true); $table->timestamp('last_delivered_at')->nullable();
            $table->integer('failure_count')->default(0); $table->timestamps();
        });
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('name'); $table->string('report_key');
            $table->string('frequency', 20); $table->string('format', 10)->default('pdf'); $table->json('recipients')->nullable();
            $table->json('filters')->nullable(); $table->timestamp('next_run_at')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('entity'); $table->string('key'); $table->string('label');
            $table->string('type', 20); $table->json('options')->nullable(); $table->boolean('is_required')->default(false); $table->integer('sort_order')->default(0); $table->timestamps();
            $table->unique(['tenant_id', 'entity', 'key']);
        });
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->string('event'); $table->string('channel', 20);
            $table->string('subject')->nullable(); $table->text('body'); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['tenant_id', 'event', 'channel']);
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->nullable(); $table->unsignedBigInteger('user_id')->nullable(); $table->string('action', 30);
            $table->string('entity'); $table->unsignedBigInteger('entity_id')->nullable(); $table->json('old_values')->nullable(); $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable(); $table->text('user_agent')->nullable(); $table->timestamps();
            $table->index(['tenant_id', 'entity', 'entity_id']);
        });
    }
    public function down(): void
    {
        foreach (['audit_logs','notification_templates','custom_fields','scheduled_reports','webhook_subscriptions','integration_connections','approval_requests','workflow_definitions','export_jobs','import_jobs'] as $table) Schema::dropIfExists($table);
    }
};
