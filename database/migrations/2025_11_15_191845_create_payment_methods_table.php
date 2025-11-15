<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام روش پرداخت
            $table->string('code')->unique(); // کد یکتا (system, gateway_1, etc.)
            $table->text('description')->nullable(); // توضیحات
            $table->boolean('is_active')->default(true); // فعال/غیرفعال
            $table->boolean('is_system')->default(false); // آیا روش پرداخت سیستمی است
            $table->json('config')->nullable(); // تنظیمات اضافی (برای گیت‌وی‌های آینده)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
