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
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->datetime('started_at'); // زمان شروع اشتراک
            $table->datetime('expires_at'); // زمان انقضای اشتراک
            $table->integer('remaining_days'); // روزهای باقی‌مانده
            $table->boolean('is_active')->default(true); // آیا اشتراک فعال است
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->timestamps();
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
