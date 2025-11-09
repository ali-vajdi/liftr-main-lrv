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
        Schema::create('service_elevator_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_checklist_id')->constrained('service_checklists')->onDelete('cascade')->name('svc_elevator_checklist_checklist_fk');
            $table->foreignId('elevator_id')->constrained('elevators')->onDelete('cascade')->name('svc_elevator_checklist_elevator_fk');
            $table->boolean('verified')->default(false);
            $table->timestamps();
            
            // Ensure one checklist per elevator per service checklist
            $table->unique(['service_checklist_id', 'elevator_id'], 'service_elevator_checklist_unique');
            $table->index('elevator_id', 'svc_elevator_checklist_elevator_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_elevator_checklists');
    }
};
