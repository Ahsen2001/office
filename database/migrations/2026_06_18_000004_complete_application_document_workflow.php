<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_documents', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('application_id')->constrained('branches')->nullOnDelete();
            $table->enum('visibility', ['internal', 'branch', 'public'])->default('internal')->after('remarks');
        });

        Schema::table('application_status_histories', function (Blueprint $table) {
            $table->foreignId('assigned_officer_id')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
        });

        DB::table('application_documents')
            ->whereNotNull('application_id')
            ->update([
                'branch_id' => DB::raw('(select branch_id from service_applications where service_applications.id = application_documents.application_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('application_status_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_officer_id');
        });

        Schema::table('application_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn('visibility');
        });
    }
};
