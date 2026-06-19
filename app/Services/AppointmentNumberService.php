<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Branch;

class AppointmentNumberService
{
    public function next(Branch $branch, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');
        $prefix = strtoupper($branch->code)."-APT-{$year}-";

        $latest = Appointment::withTrashed()
            ->where('branch_id', $branch->id)
            ->where('appointment_no', 'like', $prefix.'%')
            ->orderByDesc('appointment_no')
            ->value('appointment_no');

        $serial = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        do {
            $number = $prefix.str_pad((string) $serial, 4, '0', STR_PAD_LEFT);
            $serial++;
        } while (Appointment::withTrashed()->where('appointment_no', $number)->exists());

        return $number;
    }
}
