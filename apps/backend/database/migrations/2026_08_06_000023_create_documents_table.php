<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module: document-management (مستندات مرتبطة بأي كيان: مشروع، عقد، موظف، عميل...)
 * علاقة polymorphic عشان تتربط بأي موديول من غير تعديل جداول تانية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('category')->nullable(); // contract, invoice, license, drawing...
            $table->nullableMorphs('documentable'); // documentable_id + documentable_type
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
