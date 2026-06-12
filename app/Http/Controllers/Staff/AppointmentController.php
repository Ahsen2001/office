<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $departmentId = $request->integer('department_id') ?: null;
        $status = $request->string('status')->toString();
        $date = $request->date('date')?->format('Y-m-d');

        $appointments = Appointment::with(['person', 'application', 'department', 'officer'])
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($date, fn ($query) => $query->whereDate('appointment_date', $date))
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('staff.appointments.index', [
            'appointments' => $appointments,
            'departments' => Department::orderBy('name')->get(),
            'departmentId' => $departmentId,
            'status' => $status,
            'date' => $date,
            'todayAppointments' => Appointment::whereDate('appointment_date', today())->count(),
        ]);
    }

    public function calendar(Request $request): View
    {
        $month = $request->date('month') ?: now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $appointments = Appointment::with(['person', 'department', 'officer'])
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->format('Y-m-d'));

        return view('staff.appointments.calendar', [
            'appointmentsByDate' => $appointments,
            'month' => $month,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function create(Request $request): View
    {
        $application = $request->integer('application_id') ? ServiceApplication::find($request->integer('application_id')) : null;
        $person = $application?->person ?? ($request->integer('person_id') ? Person::find($request->integer('person_id')) : null);

        return view('staff.appointments.create', $this->formData(new Appointment(), $application, $person));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $application = ServiceApplication::find($data['application_id'] ?? null);

        $appointment = Appointment::create([
            'appointment_no' => $this->generateAppointmentNumber(),
            'application_id' => $application?->id,
            'person_id' => $application?->person_id ?? $data['person_id'],
            'department_id' => $application?->department_id ?? $data['department_id'],
            'officer_id' => $data['officer_id'] ?? null,
            'created_by' => $request->user()->id,
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
            'purpose' => $data['purpose'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ]);

        if ($application) {
            app(NotificationService::class)->assignedOfficer(
                $application->loadMissing('assignedOfficer'),
                'Appointment scheduled',
                "{$appointment->appointment_no} was scheduled for {$appointment->appointment_date->format('Y-m-d')}.",
                'appointment_scheduled'
            );
        }

        AuditLogger::log('create', 'appointments', "Created appointment {$appointment->appointment_no}.", $appointment, null, $appointment->only(['appointment_no', 'person_id', 'application_id', 'department_id', 'appointment_date', 'start_time']), $request);

        return redirect()->route('staff.appointments.show', $appointment)->with('success', 'Appointment booked successfully.');
    }

    public function storeForApplication(Request $request, ServiceApplication $application): RedirectResponse
    {
        $data = $this->validated($request);

        $appointment = Appointment::create([
            'appointment_no' => $this->generateAppointmentNumber(),
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'department_id' => $application->department_id,
            'officer_id' => $data['officer_id'] ?? null,
            'created_by' => $request->user()->id,
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'status' => $data['status'] ?? 'scheduled',
            'purpose' => $data['purpose'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ]);

        app(NotificationService::class)->assignedOfficer(
            $application->loadMissing('assignedOfficer'),
            'Appointment scheduled',
            "{$appointment->appointment_no} was scheduled for {$appointment->appointment_date->format('Y-m-d')}.",
            'appointment_scheduled'
        );

        AuditLogger::log('create', 'appointments', "Created appointment {$appointment->appointment_no}.", $appointment, null, $appointment->only(['appointment_no', 'person_id', 'application_id', 'department_id', 'appointment_date', 'start_time']), $request);

        return redirect()->route('staff.appointments.show', $appointment)->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment): View
    {
        return view('staff.appointments.show', [
            'appointment' => $appointment->load(['person', 'application.service', 'department', 'officer', 'creator']),
        ]);
    }

    public function edit(Appointment $appointment): View
    {
        return view('staff.appointments.edit', $this->formData($appointment, $appointment->application, $appointment->person));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment->update($this->validated($request, $appointment));

        return redirect()->route('staff.appointments.show', $appointment)->with('success', 'Appointment updated successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled,missed,rescheduled'],
            'remarks' => ['nullable', 'string'],
        ]);

        $appointment->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? $appointment->remarks,
        ]);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'remarks' => ['nullable', 'string'],
        ]);

        $appointment->update($data + ['status' => 'rescheduled']);

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['nullable', 'string']]);

        $appointment->update([
            'status' => 'cancelled',
            'remarks' => $data['remarks'] ?? $appointment->remarks,
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    private function validated(Request $request, ?Appointment $appointment = null): array
    {
        return $request->validate([
            'person_id' => ['required_without:application_id', 'nullable', 'exists:people,id'],
            'application_id' => ['nullable', 'exists:service_applications,id'],
            'department_id' => ['required_without:application_id', 'nullable', 'exists:departments,id'],
            'officer_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'status' => ['nullable', 'in:scheduled,completed,cancelled,missed,rescheduled'],
            'purpose' => ['nullable', 'string', 'max:500'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function formData(Appointment $appointment, ?ServiceApplication $application = null, ?Person $person = null): array
    {
        return [
            'appointment' => $appointment,
            'application' => $application,
            'person' => $person,
            'people' => Person::orderBy('full_name')->limit(200)->get(),
            'applications' => ServiceApplication::with('person')->latest()->limit(200)->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'officers' => User::where('is_active', true)->whereHas('roles', fn ($query) => $query->where('slug', 'department_officer'))->orderBy('name')->get(),
        ];
    }

    private function generateAppointmentNumber(): string
    {
        $prefix = 'APT-'.now()->format('Y').'-';
        $next = Appointment::withTrashed()->where('appointment_no', 'like', $prefix.'%')->count() + 1;

        do {
            $number = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Appointment::withTrashed()->where('appointment_no', $number)->exists());

        return $number;
    }
}
