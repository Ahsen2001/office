<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_applications', function (Blueprint $table) {
            $table->json('required_documents')->nullable()->after('description');
            $table->text('remarks')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('service_applications', function (Blueprint $table) {
            $table->dropColumn(['required_documents', 'remarks']);
        });
    }
};
