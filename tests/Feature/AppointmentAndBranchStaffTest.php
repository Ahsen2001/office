<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Person;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AppointmentAndBranchStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_loads_branch_number_and_only_active_branch_officers(): void
    {
        $data = $this->data();

        $this->actingAs($data['reception'])
            ->getJson(route('staff.branches.appointment-number', $data['accounts']))
            ->assertOk()
            ->assertJson(['appointment_number' => 'ACC-APT-2026-0001']);

        $response = $this->actingAs($data['reception'])
            ->getJson(route('staff.branches.officers', $data['accounts']));

        $response->assertOk()
            ->assertJsonCount(2, 'officers')
            ->assertJsonFragment(['label' => 'Accounts Head - Branch Head'])
            ->assertJsonFragment(['label' => 'Accounts Officer - Accounts Officer'])
            ->assertJsonMissing(['name' => 'Land Officer']);
    }

    public function test_appointments_use_separate_branch_number_series_and_save_relationships(): void
    {
        $data = $this->data();

        $payload = [
            'person_id' => $data['person']->id,
            'application_id' => $data['application']->id,
            'branch_id' => $data['accounts']->id,
            'officer_id' => $data['accounts_staff']->id,
            'appointment_date' => today()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => 'scheduled',
            'purpose' => 'Document verification',
            'remarks' => 'Bring original documents.',
        ];

        $this->actingAs($data['reception'])->post(route('staff.appointments.general.store'), $payload)->assertRedirect();
        $payload['application_id'] = null;
        $this->actingAs($data['reception'])->post(route('staff.appointments.general.store'), $payload)->assertRedirect();

        $landPerson = $this->person('PER-LAND-001', 'Land Person');
        $payload['person_id'] = $landPerson->id;
        $payload['branch_id'] = $data['land']->id;
        $payload['officer_id'] = $data['land_staff']->id;
        $this->actingAs($data['reception'])->post(route('staff.appointments.general.store'), $payload)->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'appointment_no' => 'ACC-APT-2026-0001',
            'application_id' => $data['application']->id,
            'person_id' => $data['person']->id,
            'branch_id' => $data['accounts']->id,
            'officer_id' => $data['accounts_staff']->id,
        ]);
        $this->assertDatabaseHas('appointments', ['appointment_no' => 'ACC-APT-2026-0002']);
        $this->assertDatabaseHas('appointments', ['appointment_no' => 'LAND-APT-2026-0001']);
    }

    public function test_reception_cannot_assign_an_officer_from_another_branch(): void
    {
        $data = $this->data();

        $this->actingAs($data['reception'])
            ->post(route('staff.appointments.general.store'), [
                'person_id' => $data['person']->id,
                'application_id' => $data['application']->id,
                'branch_id' => $data['accounts']->id,
                'officer_id' => $data['land_staff']->id,
                'appointment_date' => today()->addDay()->format('Y-m-d'),
                'start_time' => '10:00',
                'status' => 'scheduled',
                'purpose' => 'Application follow-up',
            ])
            ->assertSessionHasErrors('officer_id');
    }

    public function test_branch_users_cannot_open_or_submit_the_reception_appointment_form(): void
    {
        $data = $this->data();

        $this->actingAs($data['accounts_head'])
            ->get(route('staff.appointments.create'))
            ->assertForbidden();

        $this->actingAs($data['accounts_staff'])
            ->post(route('staff.appointments.general.store'), [])
            ->assertForbidden();
    }

    public function test_branch_head_can_manage_only_branch_staff_in_assigned_branch(): void
    {
        $data = $this->data();

        $response = $this->actingAs($data['accounts_head'])
            ->post(route('branch-head.staff.store'), [
                'name' => 'New Accounts Officer',
                'email' => 'new.accounts@office.test',
                'phone' => '0771234567',
                'designation' => 'Senior Accounts Officer',
                'role' => 'branch_staff',
                'branch_id' => $data['accounts']->id,
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_active' => '1',
            ]);

        $staff = User::where('email', 'new.accounts@office.test')->firstOrFail();
        $response->assertRedirect(route('branch-head.staff.show', $staff));
        $this->assertSame($data['accounts']->id, $staff->branch_id);
        $this->assertSame($data['accounts_head']->id, $staff->created_by);
        $this->assertTrue($staff->hasRole('branch_staff'));

        $this->actingAs($data['accounts_head'])
            ->get(route('branch-head.staff.show', $data['land_staff']))
            ->assertForbidden();

        $this->actingAs($data['accounts_head'])
            ->put(route('branch-head.staff.password', $staff), [
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-secure-password', $staff->fresh()->password));
    }

    public function test_branch_head_cannot_tamper_with_role_or_branch_during_staff_creation(): void
    {
        $data = $this->data();

        $this->actingAs($data['accounts_head'])
            ->post(route('branch-head.staff.store'), [
                'name' => 'Tampered User',
                'email' => 'tampered@office.test',
                'designation' => 'Administrator',
                'role' => 'admin',
                'branch_id' => $data['land']->id,
                'password' => 'password',
                'password_confirmation' => 'password',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors(['role', 'branch_id']);

        $this->assertDatabaseMissing('users', ['email' => 'tampered@office.test']);
    }

    public function test_public_status_shows_connected_appointment_and_officer_but_not_private_data(): void
    {
        $data = $this->data();
        Appointment::create([
            'appointment_no' => 'ACC-APT-2026-0001',
            'application_id' => $data['application']->id,
            'person_id' => $data['person']->id,
            'branch_id' => $data['accounts']->id,
            'officer_id' => $data['accounts_staff']->id,
            'appointment_date' => today()->addDay(),
            'start_time' => '11:00',
            'status' => 'scheduled',
            'purpose' => 'Private internal purpose',
            'remarks' => 'Private appointment remarks',
        ]);

        $this->get(route('public.status', ['application_no' => $data['application']->application_no]))
            ->assertOk()
            ->assertSee('Accounts Officer')
            ->assertSee('Officer designation')
            ->assertSee('0770000002')
            ->assertSee(today()->addDay()->format('Y-m-d'))
            ->assertDontSee('Private Applicant')
            ->assertDontSee('Private internal purpose')
            ->assertDontSee('Private appointment remarks');
    }

    private function data(): array
    {
        $roles = collect(['reception', 'branch_head', 'branch_staff'])->mapWithKeys(fn ($slug) => [
            $slug => Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => str($slug)->replace('_', ' ')->title(), 'is_active' => true]
            ),
        ]);

        $department = Department::create(['code' => 'APP', 'name' => 'Appointments', 'is_active' => true]);
        $accounts = Branch::create(['code' => 'ACC', 'name' => 'Accounts Branch', 'phone' => '0110000001', 'is_active' => true]);
        $land = Branch::create(['code' => 'LAND', 'name' => 'Land Branch', 'phone' => '0110000002', 'is_active' => true]);

        $reception = $this->user('Reception User', 'reception@office.test', $roles['reception']);
        $accountsHead = $this->user('Accounts Head', 'accounts.head@office.test', $roles['branch_head'], $accounts, 'Branch Head', '0770000001');
        $accountsStaff = $this->user('Accounts Officer', 'accounts.officer@office.test', $roles['branch_staff'], $accounts, 'Accounts Officer', '0770000002');
        $landStaff = $this->user('Land Officer', 'land.officer@office.test', $roles['branch_staff'], $land, 'Land Officer', '0770000003');

        $person = $this->person('PER-ACC-001', 'Private Applicant');
        $status = ApplicationStatus::create([
            'code' => 'processing',
            'name' => 'Processing',
            'sort_order' => 40,
            'is_active' => true,
        ]);
        $service = Service::create([
            'department_id' => $department->id,
            'branch_id' => $accounts->id,
            'code' => 'ACC-SVC',
            'name' => 'Accounts Service',
            'is_active' => true,
        ]);
        $application = ServiceApplication::create([
            'application_no' => 'APP-ACC-001',
            'person_id' => $person->id,
            'service_id' => $service->id,
            'department_id' => $department->id,
            'branch_id' => $accounts->id,
            'assigned_officer_id' => $accountsStaff->id,
            'status_id' => $status->id,
            'priority' => 'normal',
            'submitted_at' => now(),
        ]);

        return [
            'reception' => $reception,
            'accounts' => $accounts,
            'land' => $land,
            'accounts_head' => $accountsHead,
            'accounts_staff' => $accountsStaff,
            'land_staff' => $landStaff,
            'person' => $person,
            'application' => $application,
        ];
    }

    private function user(
        string $name,
        string $email,
        Role $role,
        ?Branch $branch = null,
        ?string $designation = null,
        ?string $phone = null
    ): User {
        $user = User::factory()->create(compact('name', 'email', 'designation', 'phone') + [
            'branch_id' => $branch?->id,
        ]);
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
            'phone' => '0779999999',
            'address_line_1' => 'Private address',
            'registered_at' => now(),
            'is_active' => true,
        ]);
    }
}
