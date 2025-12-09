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
        Schema::create('pdf_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('code', 6);
            $table->string('ip_address', 45);
            $table->string('download_token', 64)->nullable();
            $table->boolean('used')->default(false);
            $table->boolean('verified')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'ip_address', 'used']);
            $table->index(['service_id', 'code', 'used', 'verified']);
            $table->index('download_token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_verification_codes');
    }
};
