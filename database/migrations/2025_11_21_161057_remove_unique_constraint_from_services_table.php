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
        Schema::table('services', function (Blueprint $table) {
            // First, create a regular index on building_id so MySQL can use it for the foreign key
            // This allows us to drop the unique composite index
            $table->index('building_id');
        });
        
        // Now we can safely drop the unique constraint
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['building_id', 'service_month', 'service_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Re-add the unique constraint if rolling back
            $table->unique(['building_id', 'service_month', 'service_year']);
        });
    }
};
