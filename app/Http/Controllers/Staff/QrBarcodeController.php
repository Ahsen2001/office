<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrBarcodeController extends Controller
{
    public function scanner(): View
    {
        return view('staff.people.scanner');
    }

    public function resolve(Request $request): JsonResponse|RedirectResponse
    {
        $code = $this->normalizeCode($request->string('code')->toString());
        $person = $this->findPersonByCode($code);

        if (! $person) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid QR code or barcode.'], 404);
            }

            return back()->withErrors(['code' => 'Invalid QR code, barcode, or person ID.']);
        }

        $url = route('staff.people.show', $person);

        if ($request->expectsJson()) {
            return response()->json(['url' => $url]);
        }

        return redirect($url);
    }

    public function show(string $code): RedirectResponse
    {
        $person = $this->findPersonByCode($this->normalizeCode($code));

        abort_unless($person, 404);

        return redirect()->route('staff.people.show', $person);
    }

    public function downloadQr(Person $person): StreamedResponse
    {
        abort_unless($person->qr_code_path && Storage::disk('public')->exists($person->qr_code_path), 404);

        return Storage::disk('public')->download($person->qr_code_path, $person->person_code.'-qr.svg');
    }

    public function downloadBarcode(Person $person): StreamedResponse
    {
        abort_unless($person->barcode_path && Storage::disk('public')->exists($person->barcode_path), 404);

        return Storage::disk('public')->download($person->barcode_path, $person->person_code.'-barcode.svg');
    }

    public function regenerate(Person $person, CodeGeneratorService $codes): RedirectResponse
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $person->update($codes->generateForPerson($person));

        return back()->with('success', 'QR code and barcode regenerated successfully.');
    }

    private function findPersonByCode(string $code): ?Person
    {
        return Person::where('person_code', $code)
            ->orWhere('qr_code_value', $code)
            ->orWhere('barcode_value', $code)
            ->first();
    }

    private function normalizeCode(string $code): string
    {
        $code = trim($code);

        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $path = parse_url($code, PHP_URL_PATH) ?: '';
            $segments = array_values(array_filter(explode('/', $path)));
            $code = end($segments) ?: $code;
        }

        return $code;
    }
}
