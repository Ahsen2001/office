<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('service_applications')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types')->restrictOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['uploaded', 'verified', 'rejected'])->default('uploaded');
            $table->text('verification_remarks')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['application_id', 'status']);
            $table->index(['person_id', 'document_type_id']);
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_no', 50)->unique();
            $table->foreignId('application_id')->nullable()->constrained('service_applications')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->enum('status', ['scheduled', 'rescheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department_id', 'appointment_date']);
            $table->index(['officer_id', 'appointment_date']);
            $table->index(['person_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('document_types');
    }
};
