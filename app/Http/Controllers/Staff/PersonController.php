<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index()
    {
        return Person::latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'national_id' => ['nullable', 'string', 'max:80', 'unique:people,national_id'],
        ]);

        $data['full_name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['person_code'] = 'PER-'.now()->format('Y').'-'.str_pad((string) (Person::withTrashed()->count() + 1), 6, '0', STR_PAD_LEFT);
        $data['qr_code_value'] = 'OFFICE:PERSON:'.$data['person_code'];
        $data['barcode_value'] = str_replace('-', '', $data['person_code']);
        $data['registered_by'] = $request->user()->id;
        $data['registered_at'] = now();

        return Person::create($data);
    }

    public function show(Person $person)
    {
        return $person->load(['applications.service', 'applications.department', 'documents']);
    }
}
