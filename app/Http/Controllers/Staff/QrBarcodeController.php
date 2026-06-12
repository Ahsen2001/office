<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\CodeGeneratorService;

class QrBarcodeController extends Controller
{
    public function show(string $code)
    {
        return Person::where('person_code', $code)
            ->orWhere('qr_code_value', $code)
            ->orWhere('barcode_value', $code)
            ->with(['applications.service', 'applications.status'])
            ->firstOrFail();
    }

    public function generate(Person $person, CodeGeneratorService $codes)
    {
        $paths = $codes->generateForPerson($person);

        $person->update($paths);

        return $person->refresh();
    }
}
