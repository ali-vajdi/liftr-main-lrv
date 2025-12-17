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
        Schema::create('building_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->timestamp('contract_start_date')->nullable()->comment('تاریخ شروع قرارداد');
            $table->timestamp('contract_end_date')->nullable()->comment('تاریخ پایان قرارداد');
            $table->decimal('monthly_amount', 15, 2)->nullable()->comment('مبلغ ماهیانه قرارداد');
            $table->decimal('annual_amount', 15, 2)->nullable()->comment('مبلغ سالیانه قرارداد');
            $table->enum('payment_timing', ['after_service', 'before_service', 'at_contract_time'])->nullable()->comment('زمان دریافت: بعد از سرویس، قبل از سرویس، زمان عقد قرارداد');
            $table->enum('payment_frequency_type', ['monthly', 'yearly', 'custom'])->nullable()->comment('نوع فرکانس: ماهانه، سالانه، سفارشی');
            $table->integer('payment_frequency_value')->nullable()->comment('مقدار فرکانس (تعداد ماه یا سال برای حالت سفارشی)');
            $table->boolean('is_custom_payment_method')->default(false)->comment('آیا روش پرداخت سفارشی است یا از پیش‌تعریف‌شده');
            $table->decimal('previous_debt', 15, 2)->nullable()->default(0)->comment('بدهی قبلی');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_contracts');
    }
};
