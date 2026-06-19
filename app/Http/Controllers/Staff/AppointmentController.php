<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use App\Services\AppointmentNumberService;
use App\Services\NotificationService;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $branchId = $request->integer('branch_id') ?: null;
        $status = $request->string('status')->toString();
        $date = $request->date('date')?->format('Y-m-d');

        $base = Appointment::query()
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id));

        $appointments = (clone $base)
            ->with(['person', 'application', 'branch', 'officer'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($date, fn ($query) => $query->whereDate('appointment_date', $date))
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('staff.appointments.index', [
            'appointments' => $appointments,
            'branches' => Branch::visibleTo($request->user())->orderBy('name')->get(),
            'branchId' => $branchId,
            'status' => $status,
            'date' => $date,
            'todayAppointments' => (clone $base)->whereDate('appointment_date', today())->count(),
            'canCreate' => $request->user()->hasRole('admin', 'reception'),
        ]);
    }

    public function calendar(Request $request): View
    {
        $month = $request->date('month') ?: now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $appointments = Appointment::with(['person', 'branch', 'officer'])
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->format('Y-m-d'));

        return view('staff.appointments.calendar', compact('month', 'start', 'end') + [
            'appointmentsByDate' => $appointments,
        ]);
    }

    public function create(Request $request, AppointmentNumberService $numbers): View
    {
        $this->authorizeCreation($request);

        $application = $request->integer('application_id')
            ? ServiceApplication::with('person')->find($request->integer('application_id'))
            : null;
        $person = $application?->person
            ?? ($request->integer('person_id') ? Person::find($request->integer('person_id')) : null);
        $branch = $application?->branch;

        return view('staff.appointments.create', $this->formData(
            new Appointment,
            $application,
            $person,
            $branch ? $numbers->next($branch) : null
        ));
    }

    public function store(Request $request, AppointmentNumberService $numbers): RedirectResponse
    {
        $this->authorizeCreation($request);
        $data = $this->validated($request);

        $appointment = DB::transaction(function () use ($request, $data, $numbers) {
            $branch = Branch::query()->lockForUpdate()->findOrFail($data['branch_id']);
            $application = $data['application_id']
                ? ServiceApplication::findOrFail($data['application_id'])
                : null;

            $this->validateRelationships($data, $application);

            return Appointment::create([
                'appointment_no' => $numbers->next($branch),
                'application_id' => $application?->id,
                'person_id' => $data['person_id'],
                'department_id' => $application?->department_id,
                'branch_id' => $branch->id,
                'officer_id' => $data['officer_id'] ?? null,
                'created_by' => $request->user()->id,
                'appointment_date' => $data['appointment_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'] ?? null,
                'status' => $data['status'],
                'purpose' => $data['purpose'],
                'remarks' => $data['remarks'] ?? null,
            ]);
        });

        $this->notifyAndAudit($appointment, $request);

        return redirect()->route('staff.appointments.show', $appointment)
            ->with('success', 'Appointment booked successfully.');
    }

    public function storeForApplication(
        Request $request,
        ServiceApplication $application,
        AppointmentNumberService $numbers
    ): RedirectResponse {
        $request->merge([
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'branch_id' => $application->branch_id,
        ]);

        return $this->store($request, $numbers);
    }

    public function generateAppointmentNumber(
        Request $request,
        Branch $branch,
        AppointmentNumberService $numbers
    ): JsonResponse {
        $this->authorizeCreation($request);
        abort_unless($branch->is_active, 404);

        return response()->json(['appointment_number' => $numbers->next($branch)]);
    }

    public function getOfficersByBranch(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeCreation($request);

        $officers = User::query()
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['branch_head', 'branch_staff']))
            ->with('roles:id,name,slug')
            ->orderByRaw("CASE WHEN EXISTS (
                SELECT 1 FROM role_user
                INNER JOIN roles ON roles.id = role_user.role_id
                WHERE role_user.user_id = users.id AND roles.slug = 'branch_head'
            ) THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(fn (User $officer) => [
                'id' => $officer->id,
                'name' => $officer->name,
                'designation' => $officer->designation ?: $officer->roles->first()?->name,
                'label' => $officer->name.' - '.($officer->designation ?: $officer->roles->first()?->name ?: 'Officer'),
            ]);

        return response()->json(['officers' => $officers]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        $this->authorizeBranch($request, $appointment);

        return view('staff.appointments.show', [
            'appointment' => $appointment->load(['person', 'application.service', 'branch', 'officer.roles', 'creator']),
            'canEdit' => $request->user()->hasRole('admin', 'reception'),
        ]);
    }

    public function edit(Request $request, Appointment $appointment, AppointmentNumberService $numbers): View
    {
        abort_unless($request->user()->hasRole('admin', 'reception'), 403);

        return view('staff.appointments.edit', $this->formData(
            $appointment,
            $appointment->application,
            $appointment->person,
            $appointment->appointment_no
        ));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin', 'reception'), 403);
        $data = $this->validated($request, $appointment);
        $application = $data['application_id'] ? ServiceApplication::findOrFail($data['application_id']) : null;
        $this->validateRelationships($data, $application);

        $appointment->update([
            'application_id' => $application?->id,
            'person_id' => $data['person_id'],
            'department_id' => $application?->department_id,
            'branch_id' => $data['branch_id'],
            'officer_id' => $data['officer_id'] ?? null,
            'appointment_date' => $data['appointment_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'] ?? null,
            'status' => $data['status'],
            'purpose' => $data['purpose'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        return redirect()->route('staff.appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeBranch($request, $appointment);
        $data = $request->validate([
            'status' => ['required', 'in:scheduled,completed,cancelled,missed,rescheduled'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $appointment->update([
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? $appointment->remarks,
        ]);

        return back()->with('success', 'Appointment status updated successfully.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeBranch($request, $appointment);
        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $appointment->update($data + ['status' => 'rescheduled']);

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeBranch($request, $appointment);
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:5000']]);

        $appointment->update([
            'status' => 'cancelled',
            'remarks' => $data['remarks'] ?? $appointment->remarks,
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    private function validated(Request $request, ?Appointment $appointment = null): array
    {
        return $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'application_id' => ['nullable', 'exists:service_applications,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'officer_id' => ['nullable', 'exists:users,id'],
            'appointment_date' => ['required', 'date', $appointment ? 'after_or_equal:today' : 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'in:scheduled,completed,cancelled,missed,rescheduled'],
            'purpose' => ['required', 'string', 'max:500'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function validateRelationships(array $data, ?ServiceApplication $application): void
    {
        if ($application && (
            (int) $application->person_id !== (int) $data['person_id']
            || (int) $application->branch_id !== (int) $data['branch_id']
        )) {
            throw ValidationException::withMessages([
                'application_id' => 'The selected application must belong to the selected person and branch.',
            ]);
        }

        if (! empty($data['officer_id'])) {
            $validOfficer = User::whereKey($data['officer_id'])
                ->where('branch_id', $data['branch_id'])
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['branch_head', 'branch_staff']))
                ->exists();

            if (! $validOfficer) {
                throw ValidationException::withMessages([
                    'officer_id' => 'The selected officer must be active and belong to the selected branch.',
                ]);
            }
        }
    }

    private function formData(
        Appointment $appointment,
        ?ServiceApplication $application = null,
        ?Person $person = null,
        ?string $appointmentNumber = null
    ): array {
        $selectedBranchId = old('branch_id', $application?->branch_id ?? $appointment->branch_id);

        return [
            'appointment' => $appointment,
            'application' => $application,
            'person' => $person,
            'appointmentNumber' => $appointmentNumber,
            'people' => Person::orderBy('full_name')->limit(300)->get(),
            'applications' => ServiceApplication::with(['person', 'branch'])->latest()->limit(300)->get(),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'officers' => $selectedBranchId ? $this->officerQuery((int) $selectedBranchId)->get() : collect(),
        ];
    }

    private function officerQuery(int $branchId)
    {
        return User::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['branch_head', 'branch_staff']))
            ->with('roles:id,name,slug')
            ->orderBy('name');
    }

    private function authorizeCreation(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin', 'reception'), 403);
    }

    private function authorizeBranch(Request $request, Appointment $appointment): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $user->isBranchRestricted()) {
            return;
        }

        abort_unless((int) $appointment->branch_id === (int) $user->branch_id, 403);
    }

    private function notifyAndAudit(Appointment $appointment, Request $request): void
    {
        $appointment->loadMissing('application.assignedOfficer');

        if ($appointment->application) {
            app(NotificationService::class)->assignedOfficer(
                $appointment->application,
                'Appointment scheduled',
                "{$appointment->appointment_no} was scheduled for {$appointment->appointment_date->format('Y-m-d')}.",
                'appointment_scheduled'
            );
        }

        AuditLogger::log(
            'create',
            'appointments',
            "Created appointment {$appointment->appointment_no}.",
            $appointment,
            null,
            $appointment->only(['appointment_no', 'person_id', 'application_id', 'branch_id', 'officer_id', 'appointment_date', 'start_time']),
            $request
        );
    }
}
