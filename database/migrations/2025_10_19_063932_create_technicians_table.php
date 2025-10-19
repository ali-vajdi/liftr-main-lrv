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
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_user_id')->constrained('organization_users')->onDelete('cascade');
            $table->string('first_name'); // نام
            $table->string('last_name'); // نام خانوادگی
            $table->string('national_id')->unique(); // کدملی
            $table->string('phone_number'); // شماره تماس
            $table->string('username')->nullable(); // نام کاربری (اختیاری)
            $table->string('password')->nullable(); // رمز عبور (اختیاری)
            $table->boolean('status')->default(true); // وضعیت فعال/غیرفعال
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technicians');
    }
};