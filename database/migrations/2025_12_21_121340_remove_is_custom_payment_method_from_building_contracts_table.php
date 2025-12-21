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
            $table->dropColumn('is_custom_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_contracts', function (Blueprint $table) {
            $table->boolean('is_custom_payment_method')->default(false)->comment('آیا روش پرداخت سفارشی است یا از پیش‌تعریف‌شده');
        });
    }
};
