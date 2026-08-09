<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('module', 50)->index();
            $table->string('group', 50)->default('general');
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, boolean, integer, float, json, array
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'module', 'group', 'key'], 'settings_unique');
            $table->index(['module', 'group']);
        });

        // Module activation/license table
        Schema::create('module_activations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('module', 50)->unique();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->string('version', 20)->nullable();
            $table->json('enabled_features')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_activations');
        Schema::dropIfExists('module_settings');
    }
};
