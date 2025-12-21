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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('building_contract_id')->nullable()->after('building_id')->constrained('building_contracts')->onDelete('cascade');
            $table->decimal('monthly_amount', 15, 2)->nullable()->after('building_contract_id')->comment('مبلغ ماهیانه سرویس از قرارداد');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['building_contract_id']);
            $table->dropColumn(['building_contract_id', 'monthly_amount']);
        });
    }
};
