<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that should support soft deletes across the app domain.
     *
     * Note: we intentionally exclude framework/internal tables like telescope/failed_jobs/tokens,
     * unless they are part of the app domain.
     */
    private array $tables = [
        'application_versions',
        'buildings',
        'building_contracts',
        'cities',
        'elevators',
        'invoices',
        'messages',
        'moderators',
        'organizations',
        'organization_packages',
        'organization_users',
        'packages',
        'package_payments',
        'package_periods',
        'payment_methods',
        'pdf_verification_codes',
        'provinces',
        'services',
        'service_checklists',
        'service_checklist_descriptions',
        'service_checklist_history',
        'service_elevator_checklists',
        'service_signatures',
        'service_views',
        'sms',
        'technicians',
        'technician_otp_verifications',
        'transactions',
        'unit_checklists',
        'users',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
                $blueprint->index('deleted_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['deleted_at']);
                $blueprint->dropSoftDeletes();
            });
        }
    }
};



