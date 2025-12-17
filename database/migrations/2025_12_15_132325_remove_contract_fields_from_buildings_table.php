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
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['service_start_date', 'service_end_date', 'monthly_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->timestamp('service_start_date')->nullable()->after('selected_longitude');
            $table->timestamp('service_end_date')->nullable()->after('service_start_date');
            $table->decimal('monthly_amount', 15, 2)->nullable()->after('elevators_count');
        });
    }
};
