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
        Schema::create('service_checklist_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_checklist_id')->constrained('service_checklists')->onDelete('cascade')->name('svc_checklist_history_checklist_fk');
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->onDelete('set null')->name('svc_checklist_history_tech_fk');
            $table->string('action'); // 'created', 'updated', 'submitted', etc.
            $table->json('changes')->nullable(); // Store what changed
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
            
            $table->index('service_checklist_id', 'svc_checklist_history_checklist_idx');
            $table->index('technician_id', 'svc_checklist_history_tech_idx');
            $table->index('created_at', 'svc_checklist_history_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_checklist_history');
    }
};
