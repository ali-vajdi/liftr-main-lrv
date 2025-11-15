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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->morphs('transactionable'); // transactionable_type, transactionable_id
            
            // Payment details
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->decimal('amount', 10, 2); // مبلغ
            $table->enum('type', ['income', 'expense'])->default('income'); // نوع تراکنش
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            
            // Transaction details
            $table->string('reference_number')->nullable(); // شماره مرجع
            $table->text('description')->nullable(); // توضیحات
            $table->timestamp('transaction_date'); // تاریخ تراکنش
            
            // Related entities
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->foreignId('moderator_id')->nullable()->constrained('moderators')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['transactionable_type', 'transactionable_id'], 'transactions_transactionable_index');
            $table->index('transaction_date', 'transactions_date_index');
            $table->index('organization_id', 'transactions_organization_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
