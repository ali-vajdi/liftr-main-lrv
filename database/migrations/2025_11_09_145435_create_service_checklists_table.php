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
        Schema::create('service_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('technicians')->onDelete('cascade');
            $table->timestamp('submitted_at');
            $table->timestamps();
            
            // Ensure one checklist per service
            $table->unique('service_id', 'svc_checklist_service_unique');
            $table->index('technician_id', 'svc_checklist_technician_idx');
            $table->index('submitted_at', 'svc_checklist_submitted_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_checklists');
    }
};
