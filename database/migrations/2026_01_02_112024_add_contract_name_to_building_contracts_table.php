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
            $table->string('contract_name')->nullable()->after('contract_number')->comment('نام قرارداد بر اساس قالب تنظیمات سازمان');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->dropColumn('contract_name');
        });
    }
};
