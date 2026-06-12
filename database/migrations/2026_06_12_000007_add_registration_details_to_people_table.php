<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('occupation', 120)->nullable()->after('photo_path');
            $table->string('emergency_contact_name', 150)->nullable()->after('occupation');
            $table->string('emergency_contact_number', 30)->nullable()->after('emergency_contact_name');
            $table->text('notes')->nullable()->after('emergency_contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn([
                'occupation',
                'emergency_contact_name',
                'emergency_contact_number',
                'notes',
            ]);
        });
    }
};
