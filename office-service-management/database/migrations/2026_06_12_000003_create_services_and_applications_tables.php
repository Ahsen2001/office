<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('code', 40)->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('estimated_days')->nullable();
            $table->boolean('requires_appointment')->default(false);
            $table->boolean('requires_payment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['department_id', 'is_active']);
        });

        Schema::create('application_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 80)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no', 50)->unique();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_id')->constrained('application_statuses')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('subject')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'status_id']);
            $table->index(['department_id', 'status_id']);
            $table->index(['assigned_officer_id', 'status_id']);
            $table->index('submitted_at');
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('service_applications')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('application_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('application_statuses')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['application_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('service_applications');
        Schema::dropIfExists('application_statuses');
        Schema::dropIfExists('services');
    }
};
