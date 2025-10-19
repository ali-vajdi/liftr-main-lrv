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
        Schema::create('organization_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            
            // Store package information at the time of assignment
            $table->string('package_name');
            $table->integer('package_duration_days');
            $table->string('package_duration_label');
            $table->decimal('package_price', 10, 2);
            
            // Package assignment details
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_packages');
    }
};
