<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->dropColumn('payment_frequency_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->enum('payment_frequency_type', ['monthly', 'yearly'])->nullable()->after('payment_timing')->comment('نوع فرکانس: ماهانه، سالانه');
        });
    }
};
