<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request, ServiceApplication $application)
    {
        $data = $request->validate([
            'officer_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'purpose' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        return Appointment::create($data + [
            'appointment_no' => 'APT-'.now()->format('YmdHis'),
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'department_id' => $application->department_id,
            'created_by' => $request->user()->id,
        ]);
    }
}
