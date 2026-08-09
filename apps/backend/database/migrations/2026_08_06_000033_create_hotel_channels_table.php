<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * قنوات الحجز الخارجية (Channel Manager): Booking.com, Expedia, الموقع
 * المباشر، الهاتف... كل قناة ممكن يكون ليها إعدادات اتصال (API key/secret)
 * محفوظة مشفّرة في config. المزامنة الفعلية مع أي مزوّد خارجي محتاجة
 * بيانات اعتماد حقيقية من المزوّد — البنية هنا جاهزة، والتنفيذ الفعلي
 * (HTTP client لكل مزوّد) بيتضاف وقت ما يتحدد المزوّد المطلوب فعليًا.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Booking.com, Expedia, Direct, Walk-in...
            $table->string('code')->unique(); // booking_com, expedia, direct
            $table->string('provider')->nullable(); // اسم المزوّد التقني لو API خارجي
            $table->json('config')->nullable(); // إعدادات الاتصال (مشفّرة عبر cast)
            $table->decimal('commission_rate', 5, 2)->default(0); // % عمولة القناة
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // القناة المباشرة (Direct) موجودة دايمًا كقيمة افتراضية للحجوزات اليدوية
        DB::table('hotel_channels')->insert([
            'name' => 'Direct / Walk-in',
            'code' => 'direct',
            'provider' => null,
            'config' => null,
            'commission_rate' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_channels');
    }
};
