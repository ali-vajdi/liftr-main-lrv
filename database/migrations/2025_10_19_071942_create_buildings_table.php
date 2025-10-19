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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name'); // نام ساختمان/پروژه
            $table->string('manager_name'); // نام و نام خانوادگی مدیر/نماینده
            $table->string('manager_phone'); // شماره تماس مدیر/نماینده
            $table->enum('building_type', ['مسکونی', 'اداری', 'تجاری']); // نوع ساختمان
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->text('address'); // آدرس متنی
            $table->decimal('selected_latitude', 10, 8)->nullable(); // لوکیشن انتخابی
            $table->decimal('selected_longitude', 11, 8)->nullable(); // لوکیشن انتخابی
            $table->integer('service_day_of_month')->nullable(); // بازه زمانی سرویس (روز ماه)
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
