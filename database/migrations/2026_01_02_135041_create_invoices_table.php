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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->string('invoice_number')->unique()->comment('شماره فاکتور');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('جمع کل قبل از تخفیف و مالیات');
            $table->decimal('discount', 15, 2)->default(0)->comment('تخفیف');
            $table->decimal('tax_percentage', 5, 2)->default(0)->comment('درصد مالیات');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('مبلغ مالیات');
            $table->decimal('total', 15, 2)->default(0)->comment('قیمت کل');
            $table->timestamp('invoice_date')->nullable()->comment('تاریخ فاکتور');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['organization_id', 'building_id']);
            $table->index('invoice_number');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
