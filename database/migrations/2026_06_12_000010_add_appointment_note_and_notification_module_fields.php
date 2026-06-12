<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('application_notes', 'note_type')) {
                $table->string('note_type', 60)->default('general')->after('created_by');
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('application_notes', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
            });

            Schema::table('application_notes', function (Blueprint $table) {
                $table->foreignId('application_id')->nullable()->change();
                $table->foreign('application_id')->references('id')->on('service_applications')->cascadeOnDelete();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE application_notes MODIFY visibility ENUM('internal', 'department', 'public') NOT NULL DEFAULT 'internal'");
            DB::statement("UPDATE appointments SET status = 'missed' WHERE status = 'no_show'");
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled', 'completed', 'cancelled', 'missed', 'rescheduled') NOT NULL DEFAULT 'scheduled'");
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type', 80)->default('system')->after('message');
            }

            if (! Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('type');
            }
        });

        DB::table('notifications')->whereNotNull('read_at')->update(['is_read' => true]);
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'is_read')) {
                $table->dropColumn('is_read');
            }

            if (Schema::hasColumn('notifications', 'type')) {
                $table->dropColumn('type');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('scheduled', 'rescheduled', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'scheduled'");
            DB::statement("ALTER TABLE application_notes MODIFY visibility ENUM('internal', 'manager', 'public') NOT NULL DEFAULT 'internal'");
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('application_notes', function (Blueprint $table) {
                $table->dropForeign(['application_id']);
            });

            Schema::table('application_notes', function (Blueprint $table) {
                $table->foreignId('application_id')->nullable(false)->change();
                $table->foreign('application_id')->references('id')->on('service_applications')->cascadeOnDelete();
            });
        }

        Schema::table('application_notes', function (Blueprint $table) {
            if (Schema::hasColumn('application_notes', 'note_type')) {
                $table->dropColumn('note_type');
            }
        });
    }
};
