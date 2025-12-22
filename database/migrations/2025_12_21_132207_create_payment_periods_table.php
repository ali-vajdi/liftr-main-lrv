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
        Schema::create('payment_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_contract_id')->constrained('building_contracts')->onDelete('cascade');
            $table->integer('period_number')->comment('شماره دوره پرداخت (شروع از 1)');
            $table->decimal('amount', 15, 2)->default(0)->comment('مبلغ کل دوره (جمع مبالغ سرویس‌های این دوره)');
            $table->enum('payment_timing', ['after_service', 'before_service', 'at_contract_time'])->nullable()->comment('زمان دریافت: بعد از سرویس، قبل از سرویس، زمان عقد قرارداد');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending')->comment('وضعیت پرداخت');
            $table->timestamp('paid_at')->nullable()->comment('تاریخ پرداخت');
            $table->text('notes')->nullable()->comment('یادداشت');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['building_contract_id', 'period_number']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_periods');
    }
};
