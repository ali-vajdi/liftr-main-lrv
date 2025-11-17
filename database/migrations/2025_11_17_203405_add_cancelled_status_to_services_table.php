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
        // Modify the enum to include 'cancelled' status
        DB::statement("ALTER TABLE `services` MODIFY COLUMN `status` ENUM('pending', 'assigned', 'completed', 'expired', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to previous enum values (without cancelled)
        DB::statement("ALTER TABLE `services` MODIFY COLUMN `status` ENUM('pending', 'assigned', 'completed', 'expired') DEFAULT 'pending'");
    }
};
