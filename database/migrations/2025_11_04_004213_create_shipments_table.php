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
            $table->string('tracking_number')->unique(); // رقم التتبع (مثل WP123)
            $table->unsignedTinyInteger('status_code')->default(1); // رقم المرحلة (1-4)
            $table->timestamps(); // وقت الإنشاء والتحديث
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
