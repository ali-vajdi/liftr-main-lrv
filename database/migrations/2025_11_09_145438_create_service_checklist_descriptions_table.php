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
        Schema::create('service_checklist_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_elevator_checklist_id')->constrained('service_elevator_checklists')->onDelete('cascade')->name('svc_checklist_desc_elevator_fk');
            $table->foreignId('checklist_id')->constrained('description_checklists')->onDelete('cascade')->name('svc_checklist_desc_checklist_fk');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('service_elevator_checklist_id', 'svc_checklist_desc_elevator_idx');
            $table->index('checklist_id', 'svc_checklist_desc_checklist_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_checklist_descriptions');
    }
};
