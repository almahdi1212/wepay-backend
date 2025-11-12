<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_status_histories', function (Blueprint $table) {
            $table->id();

            // 🔗 ربط بالحقل الأساسي في الشحنات
            $table->foreignId('shipment_id')
                  ->constrained('shipments')
                  ->onDelete('cascade');

            // 🔹 الحالة الجديدة
            $table->unsignedTinyInteger('status_code');

            // 🔹 ملاحظات أو وصف (اختياري)
            $table->text('note')->nullable();

            // 🔹 المستخدم الذي قام بالتغيير (اختياري)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_status_histories');
    }
};
