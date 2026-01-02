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
            // Convert existing integer contract numbers to strings
            DB::statement('ALTER TABLE building_contracts MODIFY contract_number VARCHAR(255) NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            // Convert back to integer (this may fail if there are non-numeric values)
            DB::statement('ALTER TABLE building_contracts MODIFY contract_number UNSIGNED INTEGER NULL');
        });
    }
};
