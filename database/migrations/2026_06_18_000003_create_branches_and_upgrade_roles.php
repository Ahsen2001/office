<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->foreignId('branch_head_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('location', 180)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('departments')) {
            DB::table('departments')->orderBy('id')->get()->each(function ($department) {
                DB::table('branches')->insert([
                    'id' => $department->id,
                    'name' => $department->name,
                    'code' => $department->code,
                    'description' => $department->description,
                    'branch_head_user_id' => $department->department_officer_id ?? null,
                    'phone' => $department->phone,
                    'email' => $department->email,
                    'location' => $department->location ?? null,
                    'is_active' => $department->is_active,
                    'created_at' => $department->created_at,
                    'updated_at' => $department->updated_at,
                ]);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
            $table->decimal('fee_amount', 12, 2)->default(0)->after('required_documents');
        });

        Schema::table('service_applications', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
        });

        Schema::table('application_status_histories', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
        });

        DB::table('users')->whereNotNull('department_id')->update(['branch_id' => DB::raw('department_id')]);
        DB::table('services')->whereNotNull('department_id')->update(['branch_id' => DB::raw('department_id')]);
        DB::table('service_applications')->whereNotNull('department_id')->update(['branch_id' => DB::raw('department_id')]);
        DB::table('appointments')->whereNotNull('department_id')->update(['branch_id' => DB::raw('department_id')]);
        DB::table('application_status_histories')->whereNotNull('department_id')->update(['branch_id' => DB::raw('department_id')]);

        $roleMap = [
            'staff' => ['name' => 'Reception Staff', 'slug' => 'reception', 'description' => 'Registers people and creates service applications.'],
            'department_officer' => ['name' => 'Branch Staff', 'slug' => 'branch_staff', 'description' => 'Processes applications in an assigned branch.'],
            'manager' => ['name' => 'Divisional Secretary / ADS / AO', 'slug' => 'management', 'description' => 'Monitors all branches, workloads, and reports.'],
        ];

        foreach ($roleMap as $oldSlug => $role) {
            DB::table('roles')->where('slug', $oldSlug)->update($role + ['updated_at' => now()]);
        }

        DB::table('roles')->updateOrInsert(
            ['slug' => 'branch_head'],
            [
                'name' => 'Branch Head',
                'description' => 'Manages an assigned branch and its staff workload.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'reception')->update(['name' => 'Staff', 'slug' => 'staff']);
        DB::table('roles')->where('slug', 'branch_staff')->update(['name' => 'Department Officer', 'slug' => 'department_officer']);
        DB::table('roles')->where('slug', 'management')->update(['name' => 'Manager', 'slug' => 'manager']);
        DB::table('roles')->where('slug', 'branch_head')->delete();

        Schema::table('application_status_histories', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        Schema::table('appointments', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        Schema::table('service_applications', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn('fee_amount');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('branch_id'));
        Schema::dropIfExists('branches');
    }
};
