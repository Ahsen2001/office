<?php

namespace App\Http\Controllers;

use App\Models\ServiceApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicApplicationStatusController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'application_no' => ['nullable', 'string', 'max:50'],
            'person_code' => ['nullable', 'string', 'max:120'],
            'nic' => ['nullable', 'string', 'max:80'],
            'qr' => ['nullable', 'string', 'max:500'],
        ]);

        $applications = collect();
        $applicationNo = trim($validated['application_no'] ?? '');
        $personCode = $this->normalizeQrValue(trim(($validated['person_code'] ?? '') ?: ($validated['qr'] ?? '')));
        $nic = trim($validated['nic'] ?? '');
        $searched = $applicationNo !== '' || $personCode !== '' || $nic !== '';

        if ($searched) {
            $applications = ServiceApplication::query()
                ->with([
                    'service:id,name',
                    'branch:id,name,phone',
                    'assignedOfficer:id,name,phone,designation,branch_id',
                    'assignedOfficer.roles:id,name',
                    'status:id,code,name',
                    'appointments' => fn ($query) => $query
                        ->select(['id', 'application_id', 'appointment_date', 'start_time', 'status'])
                        ->whereIn('status', ['scheduled', 'rescheduled']),
                ])
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
        }

        return response()
            ->view('public.status-check', compact('applications', 'searched'))
            ->header('Cache-Control', 'no-store, private, max-age=0')
            ->header('Pragma', 'no-cache');
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
