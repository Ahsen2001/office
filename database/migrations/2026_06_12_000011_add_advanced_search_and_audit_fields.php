<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('module');
            }

            $table->index(['user_id', 'created_at'], 'audit_user_created_idx');
            $table->index(['module', 'created_at'], 'audit_module_created_idx');
        });

        Schema::table('service_applications', function (Blueprint $table) {
            $table->index(['application_no', 'submitted_at'], 'applications_no_submitted_idx');
            $table->index(['service_id', 'status_id'], 'applications_service_status_idx');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->index(['person_code', 'full_name'], 'people_code_name_idx');
            $table->index(['phone', 'registered_at'], 'people_phone_registered_idx');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_code_name_idx');
            $table->dropIndex('people_phone_registered_idx');
        });

        Schema::table('service_applications', function (Blueprint $table) {
            $table->dropIndex('applications_no_submitted_idx');
            $table->dropIndex('applications_service_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_user_created_idx');
            $table->dropIndex('audit_module_created_idx');

            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
