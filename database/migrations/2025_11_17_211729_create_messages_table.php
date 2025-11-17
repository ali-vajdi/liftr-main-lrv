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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->enum('sender_type', ['admin', 'organization']); // Who sent the message
            $table->unsignedBigInteger('sender_id')->nullable(); // moderator_id or organization_id
            $table->enum('receiver_type', ['organization', 'technician']); // Who receives the message
            $table->unsignedBigInteger('receiver_id')->nullable(); // organization_id or technician_id (null = all)
            $table->string('subject'); // Message subject
            $table->text('message'); // Message content
            $table->boolean('is_read')->default(false); // Read status
            $table->timestamp('read_at')->nullable(); // When was it read
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null'); // Related service (for automatic messages)
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['receiver_type', 'receiver_id', 'is_read']);
            $table->index(['sender_type', 'sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
