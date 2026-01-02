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
        Schema::table('organizations', function (Blueprint $table) {
            $table->json('invoice_number_format')->nullable()->after('contract_number_increment')->comment('قالب شماره فاکتور (شامل بخش‌ها و جداکننده‌ها)');
            $table->unsignedInteger('invoice_number_increment')->default(0)->after('invoice_number_format')->comment('عدد افزایشی فعلی برای شماره فاکتور');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['invoice_number_format', 'invoice_number_increment']);
        });
    }
};
