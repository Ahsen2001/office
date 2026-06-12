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

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('person_id')->constrained('services')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('status');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('unpaid', 'paid', 'partially_paid', 'refunded') NOT NULL DEFAULT 'unpaid'");
        }

        DB::table('payments')
            ->orderBy('id')
            ->get(['id', 'application_id', 'paid_at', 'created_at'])
            ->each(function ($payment) {
                $serviceId = DB::table('service_applications')->where('id', $payment->application_id)->value('service_id');

                DB::table('payments')->where('id', $payment->id)->update([
                    'service_id' => $serviceId,
                    'payment_date' => $payment->paid_at ?: $payment->created_at ?: now(),
                ]);
            });

        DB::table('payment_methods')->upsert([
            ['code' => 'CASH', 'name' => 'Cash', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CARD', 'name' => 'Card', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ONLINE_PAYMENT', 'name' => 'Online Payment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code'], ['name', 'is_active', 'updated_at']);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'service_id')) {
                $table->dropConstrainedForeignId('service_id');
            }

            if (Schema::hasColumn('payments', 'payment_date')) {
                $table->dropColumn('payment_date');
            }
        });

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
