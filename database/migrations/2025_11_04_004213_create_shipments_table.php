<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();

            // 🔹 رقم الشحنة الفريد مثل nov0001
            $table->string('tracking_number')->unique();

            // 🔹 المرحلة (1 إلى 4)
            $table->unsignedTinyInteger('status_code')->default(1);

            // 🔹 بيانات الزبون
            $table->string('customer_name')->nullable();      // اسم الزبون
            $table->string('customer_whatsapp')->nullable();  // رقم الواتساب

            // 🔹 تفاصيل الشحنة
            $table->decimal('price_usd', 10, 2)->nullable();  // السعر بالدولار
            $table->decimal('price_lyd', 10, 2)->nullable();  // السعر بالدينار الليبي
            $table->integer('quantity')->default(1);          // عدد القطع
            $table->text('description')->nullable();          // وصف إضافي

            // 🔹 الموظف المسؤول (ربط مع جدول users)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');  // إذا تم حذف المستخدم، لا تُحذف الشحنة

            $table->timestamps(); // تاريخ الإنشاء والتحديث
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
