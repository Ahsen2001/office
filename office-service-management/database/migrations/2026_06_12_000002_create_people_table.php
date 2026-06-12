<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('person_code', 40)->unique();
            $table->string('qr_code_value', 120)->unique();
            $table->string('barcode_value', 120)->unique()->nullable();
            $table->string('qr_code_path')->nullable();
            $table->string('barcode_path')->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('full_name', 210);
            $table->enum('gender', ['male', 'female', 'other', 'not_specified'])->default('not_specified');
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 80)->nullable()->unique();
            $table->string('passport_no', 80)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address_line_1', 180)->nullable();
            $table->string('address_line_2', 180)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Sri Lanka');
            $table->string('photo_path')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registered_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['full_name', 'phone']);
            $table->index('registered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
