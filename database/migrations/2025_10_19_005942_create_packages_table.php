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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // پکیج یک ماهه
            $table->integer('duration_days'); // مدت زمان به روز (1, 15, 30, 180, 365)
            $table->string('duration_label'); // برچسب مدت (1 month, 15 days, 6 months, 1 year)
            $table->decimal('price', 10, 2); // قیمت
            $table->boolean('is_public')->default(true); // آیا عمومی است یا خیر
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
