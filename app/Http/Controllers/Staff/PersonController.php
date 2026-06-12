<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\CodeGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $people = Person::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('passport_no', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('person_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.people.index', compact('people', 'search'));
    }

    public function create(): View
    {
        return view('staff.people.create', [
            'person' => new Person(['country' => 'Sri Lanka', 'gender' => 'not_specified']),
        ]);
    }

    public function store(Request $request, CodeGeneratorService $codes): RedirectResponse
    {
        $data = $this->validated($request);

        $person = DB::transaction(function () use ($request, $data, $codes) {
            $data['person_code'] = $this->generatePersonCode();
            $data['qr_code_value'] = $data['person_code'];
            $data['barcode_value'] = str_replace('-', '', $data['person_code']);
            $data['registered_by'] = $request->user()->id;
            $data['registered_at'] = now();

            if ($request->hasFile('profile_photo')) {
                $data['photo_path'] = $request->file('profile_photo')->store('people/photos', 'public');
            }

            $person = Person::create($data);
            $person->update($codes->generateForPerson($person));

            return $person;
        });

        return redirect()->route('staff.people.show', $person)->with('success', 'Person registered successfully.');
    }

    public function show(Person $person): View
    {
        return view('staff.people.show', [
            'person' => $person->load(['registrar', 'applications.service', 'applications.department', 'applications.status']),
        ]);
    }

    public function edit(Person $person): View
    {
        return view('staff.people.edit', compact('person'));
    }

    public function update(Request $request, Person $person): RedirectResponse
    {
        $data = $this->validated($request, $person);

        if ($request->hasFile('profile_photo')) {
            if ($person->photo_path) {
                Storage::disk('public')->delete($person->photo_path);
            }

            $data['photo_path'] = $request->file('profile_photo')->store('people/photos', 'public');
        }

        $person->update($data);

        return redirect()->route('staff.people.show', $person)->with('success', 'Person updated successfully.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $person->delete();

        return redirect()->route('staff.people.index')->with('success', 'Person deleted successfully.');
    }

    public function card(Person $person): View
    {
        return view('staff.people.card', compact('person'));
    }

    private function validated(Request $request, ?Person $person = null): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:210'],
            'national_id' => ['nullable', 'required_without:passport_no', 'string', 'max:80', Rule::unique('people', 'national_id')->ignore($person)],
            'passport_no' => ['nullable', 'required_without:national_id', 'string', 'max:80', Rule::unique('people', 'passport_no')->ignore($person)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other,not_specified'],
            'address_line_1' => ['nullable', 'string', 'max:180'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $nameParts = preg_split('/\s+/', trim($data['full_name']), 2);
        $data['first_name'] = $nameParts[0] ?? $data['full_name'];
        $data['last_name'] = $nameParts[1] ?? '-';
        $data['country'] = 'Sri Lanka';

        unset($data['profile_photo']);

        return $data;
    }

    private function generatePersonCode(): string
    {
        $prefix = 'PER-'.now()->format('Y').'-';
        $next = Person::withTrashed()->where('person_code', 'like', $prefix.'%')->count() + 1;

        do {
            $code = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Person::withTrashed()->where('person_code', $code)->exists());

        return $code;
    }
}
