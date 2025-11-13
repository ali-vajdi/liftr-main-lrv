<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the actual foreign key constraint name from the database
        $constraint = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'service_checklist_descriptions' 
            AND COLUMN_NAME = 'checklist_id' 
            AND CONSTRAINT_NAME LIKE '%fk%'
            LIMIT 1
        ");
        
        // Drop the foreign key constraint if it exists
        if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `service_checklist_descriptions` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        } else {
            // Try the expected name
            try {
                DB::statement("ALTER TABLE `service_checklist_descriptions` DROP FOREIGN KEY `svc_checklist_desc_checklist_fk`");
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        Schema::table('service_checklist_descriptions', function (Blueprint $table) {
            // Make checklist_id nullable to support custom checklists
            $table->unsignedBigInteger('checklist_id')->nullable()->change();
            
            // Re-add the foreign key constraint (allows NULL for custom checklists)
            $table->foreign('checklist_id', 'svc_checklist_desc_checklist_fk')
                ->references('id')
                ->on('description_checklists')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_checklist_descriptions', function (Blueprint $table) {
            // Drop the foreign key constraint (using the name we set)
            try {
                $table->dropForeign('svc_checklist_desc_checklist_fk');
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Make checklist_id required again
            $table->unsignedBigInteger('checklist_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('checklist_id', 'svc_checklist_desc_checklist_fk')
                ->references('id')
                ->on('description_checklists')
                ->onDelete('cascade');
        });
    }
};
