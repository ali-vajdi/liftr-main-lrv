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
        Schema::table('organization_packages', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['moderator_id']);
            
            // Make moderator_id nullable
            $table->foreignId('moderator_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable and set null on delete
            $table->foreign('moderator_id')->references('id')->on('moderators')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_packages', function (Blueprint $table) {
            // Drop the nullable foreign key constraint
            $table->dropForeign(['moderator_id']);
            
            // Make moderator_id not nullable again
            $table->foreignId('moderator_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint with cascade on delete
            $table->foreign('moderator_id')->references('id')->on('moderators')->onDelete('cascade');
        });
    }
};
