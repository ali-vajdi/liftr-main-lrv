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
        Schema::table('building_financial_records', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['service_id']);
            $table->dropForeign(['payment_period_id']);
            
            // Drop indexes
            $table->dropIndex(['building_id', 'is_pending']);
            $table->dropIndex(['service_id']);
            $table->dropIndex(['payment_period_id']);
            
            // Drop columns
            $table->dropColumn([
                'service_id',
                'payment_period_id',
                'service_month',
                'service_year',
                'is_pending'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_financial_records', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('payment_period_id')->nullable()->after('service_id')->constrained('payment_periods')->onDelete('set null');
            $table->integer('service_month')->nullable()->comment('ماه سرویس (در صورت مرتبط بودن)');
            $table->integer('service_year')->nullable()->comment('سال سرویس (در صورت مرتبط بودن)');
            $table->boolean('is_pending')->default(false)->comment('آیا در انتظار پرداخت است؟');
            
            // Recreate indexes
            $table->index(['building_id', 'is_pending']);
            $table->index(['service_id']);
            $table->index(['payment_period_id']);
        });
    }
};
