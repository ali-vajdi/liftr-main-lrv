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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->text('description')->comment('شرح');
            $table->integer('quantity')->default(1)->comment('تعداد');
            $table->decimal('unit_price', 15, 2)->comment('قیمت واحد');
            $table->decimal('total', 15, 2)->comment('جمع کل (تعداد × قیمت واحد)');
            $table->integer('order')->default(0)->comment('ترتیب نمایش');
            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
