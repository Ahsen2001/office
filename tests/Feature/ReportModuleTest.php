<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Person;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_management_can_view_all_branch_reports(): void
    {
        $data = $this->reportData();

        foreach ([$data['admin'], $data['management']] as $user) {
            $this->actingAs($user)
                ->get(route('reports.index'))
                ->assertOk()
                ->assertSee('Operational reports')
                ->assertSee('Registration Branch')
                ->assertSee('Land Branch')
                ->assertSee('Branch Head Performance');
        }
    }

    public function test_branch_head_report_scope_cannot_be_widened_with_query_parameters(): void
    {
        $data = $this->reportData();

        $response = $this->actingAs($data['branch_head'])
            ->get(route('reports.index', ['branch_id' => $data['other_branch']->id]));

        $response->assertOk()
            ->assertSee('Registration Branch only')
            ->assertSee('APP-OWN-001')
            ->assertDontSee('APP-OTHER-001');
    }

    public function test_branch_staff_and_reception_receive_only_permitted_report_exports(): void
    {
        $data = $this->reportData();

        $this->actingAs($data['branch_staff'])
            ->get(route('reports.export', ['report' => 'branch_heads', 'format' => 'csv']))
            ->assertForbidden();

        $this->actingAs($data['reception'])
            ->get(route('reports.export', ['report' => 'officers', 'format' => 'pdf']))
            ->assertForbidden();

        $this->actingAs($data['reception'])
            ->get(route('reports.export', ['report' => 'people', 'format' => 'csv']))
            ->assertOk()
            ->assertDownload('people-report.csv');
    }

    public function test_report_filters_support_officer_status_branch_and_date_range(): void
    {
        $data = $this->reportData();

        $this->actingAs($data['admin'])
            ->get(route('reports.index', [
                'branch_id' => $data['branch']->id,
                'status_id' => $data['completed']->id,
                'officer_id' => $data['branch_staff']->id,
                'date_from' => now()->subDay()->format('Y-m-d'),
                'date_to' => now()->addDay()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('APP-OWN-001')
            ->assertDontSee('APP-OTHER-001');
    }

    public function test_pdf_and_excel_exports_download_for_authorized_users(): void
    {
        $data = $this->reportData();

        $this->actingAs($data['admin'])
            ->get(route('reports.export', ['report' => 'applications', 'format' => 'pdf']))
            ->assertOk()
            ->assertDownload('applications-report.pdf');

        $this->actingAs($data['management'])
            ->get(route('reports.export', ['report' => 'applications', 'format' => 'excel']))
            ->assertOk()
            ->assertDownload('applications-report.xlsx');
    }

    private function reportData(): array
    {
        $department = Department::create(['code' => 'REPORT', 'name' => 'Report Department', 'is_active' => true]);
        $branch = Branch::create(['code' => 'REG', 'name' => 'Registration Branch', 'is_active' => true]);
        $otherBranch = Branch::create(['code' => 'LAND', 'name' => 'Land Branch', 'is_active' => true]);

        $admin = $this->userWithRole('admin');
        $management = $this->userWithRole('management');
        $reception = $this->userWithRole('reception', $branch);
        $branchHead = $this->userWithRole('branch_head', $branch);
        $branchStaff = $this->userWithRole('branch_staff', $branch);
        $otherStaff = $this->userWithRole('branch_staff', $otherBranch);

        $branch->update(['branch_head_user_id' => $branchHead->id]);

        $completed = ApplicationStatus::create([
            'code' => 'completed',
            'name' => 'Completed',
            'sort_order' => 80,
            'is_terminal' => true,
            'is_active' => true,
        ]);

        $service = Service::create([
            'department_id' => $department->id,
            'branch_id' => $branch->id,
            'code' => 'REPORT-SVC',
            'name' => 'Report Service',
            'is_active' => true,
        ]);

        $otherService = Service::create([
            'department_id' => $department->id,
            'branch_id' => $otherBranch->id,
            'code' => 'OTHER-SVC',
            'name' => 'Other Service',
            'is_active' => true,
        ]);

        $person = $this->person('PER-REPORT-001', 'Report Person');
        $otherPerson = $this->person('PER-REPORT-002', 'Other Person');

        ServiceApplication::create([
            'application_no' => 'APP-OWN-001',
            'person_id' => $person->id,
            'service_id' => $service->id,
            'department_id' => $department->id,
            'branch_id' => $branch->id,
            'assigned_officer_id' => $branchStaff->id,
            'status_id' => $completed->id,
            'priority' => 'normal',
            'submitted_at' => now(),
            'due_date' => today(),
        ]);

        ServiceApplication::create([
            'application_no' => 'APP-OTHER-001',
            'person_id' => $otherPerson->id,
            'service_id' => $otherService->id,
            'department_id' => $department->id,
            'branch_id' => $otherBranch->id,
            'assigned_officer_id' => $otherStaff->id,
            'status_id' => $completed->id,
            'priority' => 'normal',
            'submitted_at' => now(),
            'due_date' => today(),
        ]);

        return compact(
            'admin',
            'management',
            'reception',
            'branchHead',
            'branchStaff',
            'branch',
            'otherBranch',
            'completed'
        ) + [
            'branch_head' => $branchHead,
            'branch_staff' => $branchStaff,
            'other_branch' => $otherBranch,
        ];
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

    private function person(string $code, string $name): Person
    {
        return Person::create([
            'person_code' => $code,
            'qr_code_value' => $code,
            'barcode_value' => str_replace('-', '', $code),
            'first_name' => $name,
            'last_name' => '-',
            'full_name' => $name,
            'national_id' => $code.'-NIC',
            'registered_at' => now(),
            'is_active' => true,
        ]);
    }
}
