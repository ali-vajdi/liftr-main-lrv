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
            $table->foreignId('payment_period_id')->nullable()->after('service_id')->constrained('payment_periods')->onDelete('set null');
            $table->index('payment_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_financial_records', function (Blueprint $table) {
            $table->dropForeign(['payment_period_id']);
            $table->dropIndex(['payment_period_id']);
            $table->dropColumn('payment_period_id');
        });
    }
};
