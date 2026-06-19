<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('designation', 150)->nullable()->after('phone');
            $table->foreignId('created_by')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            });
        }

        $codeMap = [
            'ACCOUNTS' => 'ACC',
            'SOCIAL' => 'SOC',
            'SAMURDHI' => 'SAM',
            'PENSION' => 'PEN',
            'REC' => 'REG',
        ];

        foreach ($codeMap as $oldCode => $newCode) {
            DB::table('branches')->where('code', $oldCode)->update(['code' => $newCode]);
        }

        DB::table('users')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('roles.slug', 'branch_head');
            })
            ->whereNull('designation')
            ->update(['designation' => 'Branch Head']);

        DB::table('users')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->whereColumn('role_user.user_id', 'users.id')
                    ->where('roles.slug', 'branch_staff');
            })
            ->whereNull('designation')
            ->update(['designation' => 'Branch Officer']);

        $appointments = DB::table('appointments')
            ->join('branches', 'branches.id', '=', 'appointments.branch_id')
            ->select('appointments.id', 'appointments.appointment_date', 'appointments.created_at', 'branches.code')
            ->orderBy('appointments.branch_id')
            ->orderBy('appointments.created_at')
            ->get()
            ->groupBy(fn ($appointment) => $appointment->code.'-'.substr((string) ($appointment->appointment_date ?: $appointment->created_at), 0, 4));

        foreach ($appointments as $group) {
            foreach ($group->values() as $index => $appointment) {
                $year = substr((string) ($appointment->appointment_date ?: $appointment->created_at), 0, 4);
                DB::table('appointments')->where('id', $appointment->id)->update([
                    'appointment_no' => "{$appointment->code}-APT-{$year}-".str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('designation');
        });
    }
};
