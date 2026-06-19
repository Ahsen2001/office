<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BranchTestingUsersSeeder extends Seeder
{
    public function run(): void
    {
        $branchHeadRole = Role::where('slug', 'branch_head')->firstOrFail();
        $branchStaffRole = Role::where('slug', 'branch_staff')->firstOrFail();

        $accounts = [
            'ADMIN' => ['Administration', 'adminhead@office.test', 'adminofficer@office.test'],
            'ACC' => ['Accounts', 'accountshead@office.test', 'accountsofficer@office.test'],
            'LAND' => ['Land', 'landhead@office.test', 'landofficer@office.test'],
            'SOC' => ['Social Services', 'socialhead@office.test', 'socialofficer@office.test'],
            'SAM' => ['Samurdhi', 'samurdhihead@office.test', 'samurdhiofficer@office.test'],
            'PEN' => ['Pension', 'pensionhead@office.test', 'pensionofficer@office.test'],
            'REG' => ['Registration', 'registrationhead@office.test', 'registrationofficer@office.test'],
            'DEV' => ['Development', 'developmenthead@office.test', 'developmentofficer@office.test'],
            'GN' => ['GN Coordination', 'gnhead@office.test', 'gnofficer@office.test'],
        ];

        foreach ($accounts as $branchCode => [$label, $headEmail, $staffEmail]) {
            $branch = Branch::where('code', $branchCode)->firstOrFail();

            $head = $this->createUser(
                email: $headEmail,
                name: "{$label} Branch Head",
                branch: $branch,
                roleId: $branchHeadRole->id,
                designation: 'Branch Head',
            );

            $this->createUser(
                email: $staffEmail,
                name: "{$label} Branch Officer",
                branch: $branch,
                roleId: $branchStaffRole->id,
                designation: "{$label} Officer",
            );

            $branch->update(['branch_head_user_id' => $head->id]);
        }
    }

    private function createUser(string $email, string $name, Branch $branch, int $roleId, string $designation): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'branch_id' => $branch->id,
                'name' => $name,
                'designation' => $designation,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $user->roles()->sync([$roleId]);

        return $user;
    }
}
