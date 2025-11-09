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
        Schema::create('service_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_checklist_id')->constrained('service_checklists')->onDelete('cascade')->name('svc_signature_checklist_fk');
            $table->enum('type', ['manager', 'technician']);
            $table->string('name');
            $table->text('signature'); // Base64 encoded image
            $table->timestamps();
            
            // Ensure one signature per type per checklist
            $table->unique(['service_checklist_id', 'type'], 'svc_signature_checklist_type_unique');
            $table->index('service_checklist_id', 'svc_signature_checklist_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_signatures');
    }
};
