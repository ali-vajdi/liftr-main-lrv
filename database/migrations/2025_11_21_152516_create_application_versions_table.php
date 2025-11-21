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
        Schema::create('application_versions', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['web', 'android'])->default('android');
            $table->string('version', 50);
            $table->boolean('force_update')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('moderator_id')->nullable()->constrained('moderators')->onDelete('set null');
            $table->timestamps();
            
            $table->index('platform');
            $table->index('version');
            $table->index('force_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_versions');
    }
};
