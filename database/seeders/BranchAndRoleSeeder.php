<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BranchAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system control.'],
            ['name' => 'Divisional Secretary / ADS / AO', 'slug' => 'management', 'description' => 'Views every branch and management report.'],
            ['name' => 'Reception Staff', 'slug' => 'reception', 'description' => 'Registers people and creates applications.'],
            ['name' => 'Branch Head', 'slug' => 'branch_head', 'description' => 'Manages an assigned branch and staff.'],
            ['name' => 'Branch Staff', 'slug' => 'branch_staff', 'description' => 'Processes applications in an assigned branch.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role + ['is_active' => true]);
        }

        $branches = [
            ['code' => 'ADMIN', 'name' => 'Administration Branch'],
            ['code' => 'LAND', 'name' => 'Land Branch'],
            ['code' => 'SOC', 'name' => 'Social Services Branch'],
            ['code' => 'SAM', 'name' => 'Samurdhi Branch'],
            ['code' => 'PEN', 'name' => 'Pension Branch'],
            ['code' => 'REG', 'name' => 'Registration Branch'],
            ['code' => 'ACC', 'name' => 'Accounts Branch'],
            ['code' => 'DEV', 'name' => 'Development Branch'],
            ['code' => 'GN', 'name' => 'Grama Niladhari Coordination Branch'],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['code' => $branch['code']], $branch + ['is_active' => true]);
        }

        $this->mergeLegacyBranch('GEN', 'ADMIN');
        $this->mergeLegacyBranch('REC', 'REG');

        $registration = Branch::where('code', 'REG')->firstOrFail();
        $branchHead = User::updateOrCreate(
            ['email' => 'branchhead@office.test'],
            [
                'branch_id' => $registration->id,
                'name' => 'Registration Branch Head',
                'designation' => 'Branch Head',
                'phone' => '+94770000005',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $branchHead->roles()->sync([Role::where('slug', 'branch_head')->value('id')]);
        Branch::where('branch_head_user_id', $branchHead->id)->where('id', '!=', $registration->id)->update(['branch_head_user_id' => null]);
        $registration->update(['branch_head_user_id' => $branchHead->id]);
    }

    private function mergeLegacyBranch(string $legacyCode, string $targetCode): void
    {
        $legacy = Branch::where('code', $legacyCode)->first();
        $target = Branch::where('code', $targetCode)->first();

        if (! $legacy || ! $target || $legacy->is($target)) {
            return;
        }

        foreach (['users', 'services', 'service_applications', 'appointments', 'application_status_histories'] as $table) {
            DB::table($table)->where('branch_id', $legacy->id)->update(['branch_id' => $target->id]);
        }

        $legacy->delete();
    }
}
