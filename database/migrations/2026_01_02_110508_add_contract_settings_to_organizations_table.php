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
            $table->json('contract_number_format')->nullable()->after('sms_cost_per_message')->comment('قالب شماره فاکتور (شامل بخش‌ها و جداکننده‌ها)');
            $table->unsignedInteger('contract_number_increment')->default(0)->after('contract_number_format')->comment('عدد افزایشی فعلی برای شماره قرارداد');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['contract_number_format', 'contract_number_increment']);
        });
    }
};
