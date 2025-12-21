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
        // Remove 'custom' from payment_frequency_type enum
        // First, update any existing 'custom' values to NULL (shouldn't exist due to validation, but safe to handle)
        DB::table('building_contracts')
            ->where('payment_frequency_type', 'custom')
            ->update(['payment_frequency_type' => null]);
        
        // Modify the enum column to remove 'custom' option
        DB::statement("ALTER TABLE building_contracts MODIFY COLUMN payment_frequency_type ENUM('monthly', 'yearly') NULL COMMENT 'نوع فرکانس: ماهانه، سالانه'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore 'custom' option in the enum
        DB::statement("ALTER TABLE building_contracts MODIFY COLUMN payment_frequency_type ENUM('monthly', 'yearly', 'custom') NULL COMMENT 'نوع فرکانس: ماهانه، سالانه، سفارشی'");
    }
};
