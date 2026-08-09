<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('property_code')->unique();
            $table->string('name');
            $table->enum('property_type', ['residential', 'commercial', 'land', 'industrial', 'mixed_use'])
                ->default('residential');
            $table->string('location')->nullable();
            $table->decimal('area_sqm', 10, 2)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->enum('status', ['available', 'reserved', 'sold', 'rented', 'under_construction'])
                ->default('available');
            $table->string('unit_number')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
