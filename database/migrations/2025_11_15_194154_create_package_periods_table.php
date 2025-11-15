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
        Schema::create('package_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_package_id')->constrained('organization_packages')->onDelete('cascade');
            $table->integer('period_number'); // Period number (0, 1, 2, ...)
            $table->decimal('amount', 10, 0); // Period amount (rounded, no decimals)
            $table->integer('days'); // Number of days in this period (usually 30, last period may be less)
            $table->timestamp('start_date'); // Start date of this period
            $table->timestamp('end_date'); // End date of this period
            $table->boolean('is_paid')->default(false); // Whether this period is paid
            $table->timestamp('paid_at')->nullable(); // When this period was paid
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['organization_package_id', 'period_number']);
            $table->index('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_periods');
    }
};
