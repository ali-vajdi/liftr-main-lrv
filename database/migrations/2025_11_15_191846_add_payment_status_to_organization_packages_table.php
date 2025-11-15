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
        Schema::table('organization_packages', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'fully_paid'])->default('unpaid')->after('package_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_packages', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
