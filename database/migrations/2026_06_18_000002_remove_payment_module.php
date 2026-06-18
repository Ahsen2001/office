<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');

        Schema::table('service_applications', function (Blueprint $table) {
            if (Schema::hasColumn('service_applications', 'total_fee')) {
                $table->dropColumn('total_fee');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['fee_amount', 'requires_payment'],
                fn (string $column) => Schema::hasColumn('services', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'fee_amount')) {
                $table->decimal('fee_amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('services', 'requires_payment')) {
                $table->boolean('requires_payment')->default(false);
            }
        });

        Schema::table('service_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('service_applications', 'total_fee')) {
                $table->decimal('total_fee', 12, 2)->default(0);
            }
        });
    }
};
