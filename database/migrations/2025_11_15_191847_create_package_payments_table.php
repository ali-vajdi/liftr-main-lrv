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
        Schema::create('package_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_package_id')->constrained('organization_packages')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // مبلغ پرداختی
            $table->timestamp('payment_date'); // تاریخ پرداخت
            $table->text('notes')->nullable(); // یادداشت‌ها
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade'); // کسی که پرداخت را ثبت کرده
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_payments');
    }
};
