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
        Schema::table('organization_packages', function (Blueprint $table) {
            $table->boolean('use_periods')->default(false)->after('package_price');
            $table->integer('period_days')->nullable()->after('use_periods')->comment('Number of days per period (e.g., 30 for monthly periods)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_packages', function (Blueprint $table) {
            $table->dropColumn(['use_periods', 'period_days']);
        });
    }
};
