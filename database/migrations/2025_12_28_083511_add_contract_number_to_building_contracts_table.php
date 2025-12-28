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
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->unsignedInteger('contract_number')->nullable()->after('id')->comment('شماره قرارداد برای هر سازمان (شروع از 1)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->dropColumn('contract_number');
        });
    }
};
