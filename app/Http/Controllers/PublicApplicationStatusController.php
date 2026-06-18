<?php

namespace App\Http\Controllers;

use App\Models\ServiceApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicApplicationStatusController extends Controller
{
    public function index(Request $request): View
    {
        $application = null;
        $applications = collect();
        $searched = $request->hasAny(['application_no', 'person_code', 'nic', 'qr']);

        if ($searched) {
            $applicationNo = trim($request->string('application_no')->toString());
            $personCode = $this->normalizeQrValue(trim($request->string('person_code')->toString() ?: $request->string('qr')->toString()));
            $nic = trim($request->string('nic')->toString());

            $applications = ServiceApplication::query()
                ->with(['person', 'service', 'department', 'status', 'documents.documentType', 'appointments'])
                ->when($applicationNo, fn ($query) => $query->where('application_no', '=', $applicationNo))
                ->when($personCode, fn ($query) => $query->whereHas('person', fn ($personQuery) => $personQuery
                    ->where('person_code', '=', $personCode)
                    ->orWhere('qr_code_value', '=', $personCode)
                    ->orWhere('barcode_value', '=', $personCode)))
                ->when($nic, fn ($query) => $query->whereHas('person', fn ($personQuery) => $personQuery
                    ->where('national_id', '=', $nic)
                    ->orWhere('passport_no', '=', $nic)))
                ->latest('submitted_at')
                ->limit(10)
                ->get();

            $application = $applications->first();
        }

        return view('public.status-check', compact('application', 'applications', 'searched'));
    }

    private function normalizeQrValue(string $value): string
    {
        if (str_starts_with($value, 'OFFICE:PERSON:')) {
            return str_replace('OFFICE:PERSON:', '', $value);
        }

        if (preg_match('/PER-\d{4}-\d{6}/', $value, $matches)) {
            return $matches[0];
        }

        return $value;
    }
}
