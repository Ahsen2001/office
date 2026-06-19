<?php

namespace Tests\Feature;

use App\Models\ApplicationStatus;
use App\Models\Department;
use App\Models\Person;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available(): void
    {
        $this->get(route('public.home'))->assertOk()->assertSee('Office Service Management System');
        $this->get(route('public.about'))->assertOk()->assertSee('Public service built around clarity');
        $this->get(route('public.services'))->assertOk()->assertSee('Find the office service you need');
        $this->get(route('public.contact'))->assertOk()->assertSee('We are here to help');
        $this->get(route('public.status'))->assertOk()->assertSee('Check your application status');
        $this->get(route('login'))->assertOk()->assertSee('Back to Home');
    }

    public function test_contact_form_saves_message(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'full_name' => 'Public Visitor',
            'email' => 'visitor@example.test',
            'phone' => '+94770000000',
            'subject' => 'Service information',
            'message' => 'Please provide the required documents for this service.',
            'website' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'visitor@example.test',
            'subject' => 'Service information',
            'status' => 'new',
        ]);
    }

    public function test_public_status_page_does_not_expose_private_person_data(): void
    {
        $department = Department::create([
            'code' => 'PUB',
            'name' => 'Public Services',
            'is_active' => true,
        ]);

        $service = Service::create([
            'department_id' => $department->id,
            'code' => 'PUBLIC-CERT',
            'name' => 'Certificate Request',
            'description' => 'Certificate service.',
            'is_active' => true,
        ]);

        $status = ApplicationStatus::create([
            'code' => 'processing',
            'name' => 'Processing',
            'sort_order' => 40,
            'is_terminal' => false,
            'is_active' => true,
        ]);

        $person = Person::create([
            'person_code' => 'PER-2026-009999',
            'qr_code_value' => 'OFFICE:PERSON:PER-2026-009999',
            'barcode_value' => 'PER2026009999',
            'first_name' => 'Private',
            'last_name' => 'Citizen',
            'full_name' => 'Private Citizen',
            'national_id' => 'PRIVATE-NIC-001',
            'phone' => '+94771111111',
            'email' => 'private@example.test',
            'address_line_1' => 'Private home address',
            'country' => 'Sri Lanka',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        ServiceApplication::create([
            'application_no' => 'APP-2026-009999',
            'person_id' => $person->id,
            'service_id' => $service->id,
            'department_id' => $department->id,
            'status_id' => $status->id,
            'priority' => 'normal',
            'description' => 'Private application details.',
            'submitted_at' => now(),
        ]);

        $response = $this->get(route('public.status', ['application_no' => 'APP-2026-009999']));

        $response->assertOk()
            ->assertSee('APP-2026-009999')
            ->assertSee('Certificate Request')
            ->assertSee('Processing')
            ->assertDontSee('Private Citizen')
            ->assertDontSee('+94771111111')
            ->assertDontSee('private@example.test')
            ->assertDontSee('Private home address')
            ->assertDontSee('Private application details');
    }

    public function test_blank_public_status_query_does_not_return_recent_applications(): void
    {
        $response = $this->get(route('public.status', ['application_no' => '']));

        $response->assertOk()
            ->assertDontSee('status-result')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_public_status_displays_safe_in_charge_officer_details(): void
    {
        $department = Department::create(['code' => 'OFF', 'name' => 'Officer Department', 'is_active' => true]);
        $service = Service::create([
            'department_id' => $department->id,
            'code' => 'OFFICER-SVC',
            'name' => 'Officer Service',
            'is_active' => true,
        ]);
        $status = ApplicationStatus::create([
            'code' => 'processing',
            'name' => 'Processing',
            'sort_order' => 40,
            'is_terminal' => false,
            'is_active' => true,
        ]);
        $role = Role::create(['slug' => 'branch_staff', 'name' => 'Branch Staff', 'is_active' => true]);
        $officer = User::factory()->create([
            'name' => 'Public Contact Officer',
            'email' => 'private-officer-login@office.test',
            'phone' => '0112345678',
        ]);
        $officer->roles()->attach($role);
        $person = Person::create([
            'person_code' => 'PER-OFFICER-001',
            'qr_code_value' => 'PER-OFFICER-001',
            'barcode_value' => 'PEROFFICER001',
            'first_name' => 'Hidden',
            'last_name' => 'Applicant',
            'full_name' => 'Hidden Applicant',
            'national_id' => 'HIDDEN-NIC',
            'phone' => '0770000000',
            'address_line_1' => 'Hidden address',
            'registered_at' => now(),
            'is_active' => true,
        ]);

        ServiceApplication::create([
            'application_no' => 'APP-OFFICER-001',
            'person_id' => $person->id,
            'service_id' => $service->id,
            'department_id' => $department->id,
            'assigned_officer_id' => $officer->id,
            'status_id' => $status->id,
            'priority' => 'normal',
            'submitted_at' => now(),
        ]);

        $this->get(route('public.status', ['application_no' => 'APP-OFFICER-001']))
            ->assertOk()
            ->assertSee('Public Contact Officer')
            ->assertSee('Branch Staff')
            ->assertSee('0112345678')
            ->assertSee('Officer branch')
            ->assertDontSee('Hidden Applicant')
            ->assertDontSee('0770000000')
            ->assertDontSee('Hidden address')
            ->assertDontSee('private-officer-login@office.test')
            ->assertDontSee('Missing documents');
    }
}
