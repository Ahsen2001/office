<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchAndRoleSeeder;
use Database\Seeders\BranchTestingUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BranchTestingUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_accounts_are_created_for_every_branch_with_correct_roles(): void
    {
        $this->seed(BranchAndRoleSeeder::class);
        $this->seed(BranchTestingUsersSeeder::class);

        $accounts = $this->accounts();

        foreach ($accounts as $branchCode => [$headEmail, $staffEmail]) {
            $branch = Branch::where('code', $branchCode)->firstOrFail();
            $head = User::where('email', $headEmail)->firstOrFail();
            $staff = User::where('email', $staffEmail)->firstOrFail();

            $this->assertSame($branch->id, $head->branch_id);
            $this->assertSame($branch->id, $staff->branch_id);
            $this->assertTrue($head->hasRole('branch_head'));
            $this->assertTrue($staff->hasRole('branch_staff'));
            $this->assertTrue(Hash::check('password', $head->password));
            $this->assertTrue(Hash::check('password', $staff->password));
            $this->assertSame($head->id, $branch->fresh()->branch_head_user_id);
        }

        $this->assertCount(18, User::whereIn('email', collect($accounts)->flatten()->all())->get());
    }

    public function test_testing_user_seeder_is_idempotent(): void
    {
        $this->seed(BranchAndRoleSeeder::class);
        $this->seed(BranchTestingUsersSeeder::class);
        $this->seed(BranchTestingUsersSeeder::class);

        $this->assertSame(18, User::whereIn('email', collect($this->accounts())->flatten()->all())->count());
    }

    private function accounts(): array
    {
        return [
            'ADMIN' => ['adminhead@office.test', 'adminofficer@office.test'],
            'ACC' => ['accountshead@office.test', 'accountsofficer@office.test'],
            'LAND' => ['landhead@office.test', 'landofficer@office.test'],
            'SOC' => ['socialhead@office.test', 'socialofficer@office.test'],
            'SAM' => ['samurdhihead@office.test', 'samurdhiofficer@office.test'],
            'PEN' => ['pensionhead@office.test', 'pensionofficer@office.test'],
            'REG' => ['registrationhead@office.test', 'registrationofficer@office.test'],
            'DEV' => ['developmenthead@office.test', 'developmentofficer@office.test'],
            'GN' => ['gnhead@office.test', 'gnofficer@office.test'],
        ];
    }
}
