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
        Schema::table('building_financial_records', function (Blueprint $table) {
            $table->text('extra_descriptions')->nullable()->after('description')->comment('توضیحات بیشتر');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_financial_records', function (Blueprint $table) {
            $table->dropColumn('extra_descriptions');
        });
    }
};
