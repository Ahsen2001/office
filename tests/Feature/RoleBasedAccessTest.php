<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_is_redirected_to_its_dashboard(): void
    {
        foreach ([
            'admin' => 'admin.dashboard',
            'management' => 'management.dashboard',
            'reception' => 'reception.dashboard',
            'branch_head' => 'branch-head.dashboard',
            'branch_staff' => 'branch-staff.dashboard',
        ] as $slug => $route) {
            $user = $this->userWithRole($slug);
            $this->actingAs($user)->get('/dashboard')->assertRedirect(route($route));
        }
    }

    public function test_each_role_dashboard_renders(): void
    {
        $branch = Branch::create(['name' => 'Registration Branch', 'code' => 'REG', 'is_active' => true]);

        foreach ([
            'admin' => 'admin.dashboard',
            'management' => 'management.dashboard',
            'reception' => 'reception.dashboard',
            'branch_head' => 'branch-head.dashboard',
            'branch_staff' => 'branch-staff.dashboard',
        ] as $slug => $route) {
            $user = $this->userWithRole($slug, in_array($slug, ['branch_head', 'branch_staff'], true) ? $branch : null);
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_only_admin_can_open_user_management(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($this->userWithRole('management'))
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_branch_users_cannot_open_another_branch(): void
    {
        $assignedBranch = Branch::create(['name' => 'Land Branch', 'code' => 'LAND', 'is_active' => true]);
        $otherBranch = Branch::create(['name' => 'Pension Branch', 'code' => 'PENSION', 'is_active' => true]);
        $branchStaff = $this->userWithRole('branch_staff', $assignedBranch);

        $this->actingAs($branchStaff)
            ->get(route('branches.show', $assignedBranch))
            ->assertOk();

        $this->actingAs($branchStaff)
            ->get(route('branches.show', $otherBranch))
            ->assertForbidden();
    }

    public function test_management_can_view_every_branch(): void
    {
        $branch = Branch::create(['name' => 'Development Branch', 'code' => 'DEV', 'is_active' => true]);

        $this->actingAs($this->userWithRole('management'))
            ->get(route('branches.show', $branch))
            ->assertOk();
    }

    private function userWithRole(string $slug, ?Branch $branch = null): User
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => str($slug)->replace('_', ' ')->title(), 'is_active' => true]
        );

        $user = User::factory()->create(['branch_id' => $branch?->id]);
        $user->roles()->attach($role);

        return $user;
    }
}
