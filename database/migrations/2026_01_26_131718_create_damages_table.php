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
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('report_date'); // تاریخ اعلام
            $table->dateTime('visit_date')->nullable(); // تاریخ مراجعه
            $table->text('description'); // توضیحات
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
