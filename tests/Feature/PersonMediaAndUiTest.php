<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Person;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonMediaAndUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_card_generates_and_serves_qr_and_barcode_assets(): void
    {
        Storage::fake('public');
        $person = $this->person();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('staff.people.card', $person))
            ->assertOk()
            ->assertSee(route('staff.people.qr.view', $person), false)
            ->assertSee(route('staff.people.barcode.view', $person), false);

        $person->refresh();
        Storage::disk('public')->assertExists($person->qr_code_path);
        Storage::disk('public')->assertExists($person->barcode_path);

        $this->get(route('staff.people.qr.view', $person))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');

        $this->get(route('staff.people.barcode.view', $person))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');
    }

    public function test_profile_photo_accepts_up_to_ten_megabytes_and_uses_protected_route(): void
    {
        Storage::fake('public');
        $user = $this->userWithRole('reception');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=');

        $response = $this->actingAs($user)->post(route('staff.people.store'), [
            'full_name' => 'Photo Person',
            'national_id' => 'PHOTO-NIC-001',
            'gender' => 'not_specified',
            'profile_photo' => UploadedFile::fake()->createWithContent('profile.png', $png)->size(9 * 1024),
        ]);

        $person = Person::query()->where('national_id', 'PHOTO-NIC-001')->firstOrFail();
        $response->assertRedirect(route('staff.people.show', $person));
        Storage::disk('public')->assertExists($person->photo_path);

        $this->get(route('staff.people.show', $person))
            ->assertOk()
            ->assertSee(route('staff.people.photo', $person), false);

        $this->get(route('staff.people.photo', $person))->assertOk();
    }

    public function test_login_hides_development_credentials_and_uses_compact_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Development login credentials')
            ->assertDontSee('admin@office.test')
            ->assertDontSee('Password for development accounts')
            ->assertSee('max-width: 960px', false);
    }

    public function test_services_hide_empty_actions_for_read_only_users(): void
    {
        $branch = Branch::query()->create(['name' => 'Test Branch', 'code' => 'TEST', 'is_active' => true]);
        $department = Department::query()->create(['name' => 'Test Department', 'code' => 'TEST', 'is_active' => true]);
        Service::query()->create([
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'name' => 'Test Service',
            'code' => 'TEST-SVC',
            'fee_amount' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->userWithRole('management'))
            ->get(route('office.services.index'))
            ->assertOk()
            ->assertDontSee('<th class="text-end">Actions</th>', false);

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('office.services.index'))
            ->assertOk()
            ->assertSee('Actions')
            ->assertSee('Edit');
    }

    public function test_reports_have_a_working_print_control_and_shared_alignment_styles(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('id="print-report"', false)
            ->assertSee('data-print-page', false)
            ->assertSee('window.print();', false)
            ->assertSee(asset('css/office.css'), false);
    }

    public function test_person_card_uses_shared_print_control(): void
    {
        Storage::fake('public');
        $person = $this->person();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('staff.people.card', $person))
            ->assertOk()
            ->assertSee('Print Card')
            ->assertSee('data-print-page', false)
            ->assertSee('window.print();', false)
            ->assertSee(route('staff.people.qr.view', $person), false)
            ->assertSee(route('staff.people.barcode.view', $person), false);
    }

    private function userWithRole(string $slug): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => str($slug)->replace('_', ' ')->title(), 'is_active' => true],
        );
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function person(): Person
    {
        return Person::query()->create([
            'person_code' => 'PER-2026-MEDIA01',
            'qr_code_value' => 'PER-2026-MEDIA01',
            'barcode_value' => 'PER2026MEDIA01',
            'first_name' => 'Media',
            'last_name' => 'Person',
            'full_name' => 'Media Person',
            'national_id' => 'MEDIA-NIC-001',
            'gender' => 'not_specified',
            'registered_at' => now(),
            'is_active' => true,
        ]);
    }
}
