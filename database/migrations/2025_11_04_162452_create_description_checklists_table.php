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
        Schema::create('description_checklists', function (Blueprint $table) {
            $table->id();
            $table->text('title'); // عنوان چک لیست
            $table->integer('order')->default(0); // ترتیب نمایش
            $table->foreignId('moderator_id')->nullable()->constrained('moderators')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('description_checklists');
    }
};
