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
        Schema::create('building_financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->foreignId('building_contract_id')->nullable()->constrained('building_contracts')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->enum('type', ['debit', 'credit'])->comment('نوع: بدهکار (منفی) یا بستانکار (مثبت)');
            $table->decimal('amount', 15, 2)->comment('مبلغ');
            $table->enum('transaction_type', [
                'service_payment',      // پرداخت بابت سرویس
                'previous_debt',         // بدهی قبلی
                'manual_income',         // درآمد دستی
                'manual_payment',        // پرداخت دستی
                'contract_payment'       // پرداخت بابت قرارداد
            ])->comment('نوع تراکنش');
            $table->text('description')->nullable()->comment('توضیحات');
            $table->integer('service_month')->nullable()->comment('ماه سرویس (در صورت مرتبط بودن)');
            $table->integer('service_year')->nullable()->comment('سال سرویس (در صورت مرتبط بودن)');
            $table->boolean('is_pending')->default(false)->comment('آیا در انتظار پرداخت است؟');
            $table->timestamp('transaction_date')->nullable()->comment('تاریخ تراکنش');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['building_id', 'type']);
            $table->index(['building_id', 'transaction_type']);
            $table->index(['building_id', 'is_pending']);
            $table->index(['building_contract_id']);
            $table->index(['service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_financial_records');
    }
};
