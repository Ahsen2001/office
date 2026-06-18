<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_status_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('application_status_histories', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('application_id')->constrained('departments')->nullOnDelete();
            }
        });

        Schema::table('application_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('application_documents', 'document_title')) {
                $table->string('document_title', 180)->nullable()->after('document_type_id');
            }

            if (! Schema::hasColumn('application_documents', 'file_type')) {
                $table->string('file_type', 20)->nullable()->after('file_path');
            }

            if (! Schema::hasColumn('application_documents', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('application_documents', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
            });

            Schema::table('application_documents', function (Blueprint $table) {
                $table->foreignId('application_id')->nullable()->change();
                $table->foreign('application_id')->references('id')->on('service_applications')->cascadeOnDelete();
            });
        }

    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('application_documents', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
            });

            Schema::table('application_documents', function (Blueprint $table) {
                $table->foreignId('application_id')->nullable(false)->change();
                $table->foreign('application_id')->references('id')->on('service_applications')->cascadeOnDelete();
            });
        }

        Schema::table('application_documents', function (Blueprint $table) {
            foreach (['document_title', 'file_type', 'remarks'] as $column) {
                if (Schema::hasColumn('application_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('application_status_histories', function (Blueprint $table) {
            if (Schema::hasColumn('application_status_histories', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
        });
    }
};
