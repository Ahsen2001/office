<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('location', 180)->nullable()->after('email');
            $table->foreignId('department_officer_id')->nullable()->after('location')->constrained('users')->nullOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->json('required_documents')->nullable()->after('description');
            $table->unsignedSmallInteger('processing_time_days')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['required_documents', 'processing_time_days']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['department_officer_id']);
            $table->dropColumn(['department_officer_id', 'location']);
        });
    }
};
