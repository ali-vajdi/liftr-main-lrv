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
        Schema::dropIfExists('organization_packages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('organization_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
