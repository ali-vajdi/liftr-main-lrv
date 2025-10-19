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
        // First, update existing data
        DB::table('buildings')->where('building_type', 'مسکونی')->update(['building_type' => 'residential']);
        DB::table('buildings')->where('building_type', 'اداری')->update(['building_type' => 'office']);
        DB::table('buildings')->where('building_type', 'تجاری')->update(['building_type' => 'commercial']);
        
        // Then change the column type
        Schema::table('buildings', function (Blueprint $table) {
            $table->enum('building_type', ['residential', 'office', 'commercial'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update data back to Persian
        DB::table('buildings')->where('building_type', 'residential')->update(['building_type' => 'مسکونی']);
        DB::table('buildings')->where('building_type', 'office')->update(['building_type' => 'اداری']);
        DB::table('buildings')->where('building_type', 'commercial')->update(['building_type' => 'تجاری']);
        
        // Then change the column type back
        Schema::table('buildings', function (Blueprint $table) {
            $table->enum('building_type', ['مسکونی', 'اداری', 'تجاری'])->change();
        });
    }
};
