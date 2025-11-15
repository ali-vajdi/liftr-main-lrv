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
            $table->json('paid_periods')->nullable()->after('payment_status'); // Array of paid 30-day period numbers [0, 1, 2, ...]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_packages', function (Blueprint $table) {
            $table->dropColumn('paid_periods');
        });
    }
};
