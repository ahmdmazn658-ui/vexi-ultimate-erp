<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** منافذ نقاط البيع داخل الفندق: مطعم، بار، مini-bar، سبا... */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_pos_outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('restaurant'); // restaurant | bar | minibar | spa | other
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_pos_outlets');
    }
};
