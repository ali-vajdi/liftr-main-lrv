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
            $table->enum('status', ['active', 'finished', 'cancelled'])->default('active')->after('previous_debt')->comment('وضعیت قرارداد: فعال، تمام شده، لغو شده');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
