<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\ApplicationStatusHistory;
use App\Models\Department;
use App\Models\Person;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $statusId = $request->integer('status_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $applications = ServiceApplication::with(['person', 'service', 'department', 'assignedOfficer', 'status'])
            ->when($statusId, fn ($query) => $query->where('status_id', $statusId))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('application_no', 'like', "%{$search}%")
                        ->orWhereHas('person', function ($query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%")
                                ->orWhere('national_id', 'like', "%{$search}%")
                                ->orWhere('person_code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('staff.applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
            'departments' => Department::orderBy('name')->get(),
            'search' => $search,
            'statusId' => $statusId,
            'departmentId' => $departmentId,
        ]);
    }

    public function create(Request $request): View
    {
        $person = $request->integer('person_id') ? Person::find($request->integer('person_id')) : null;

        return view('staff.applications.create', $this->formData(new ServiceApplication(), $person));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $application = DB::transaction(function () use ($request, $data) {
            $service = Service::findOrFail($data['service_id']);
            $status = ApplicationStatus::findOrFail($data['status_id']);

            $data['application_no'] = $this->generateApplicationNumber();
            $data['department_id'] = $data['department_id'] ?: $service->department_id;
            $data['submitted_by'] = $request->user()->id;
            $data['submitted_at'] = $data['submitted_at'] ?? now();
            $data['total_fee'] = $service->fee_amount;
            $data['required_documents'] = $service->required_documents ?? [];

            $application = ServiceApplication::create($data);
            $this->recordStatusHistory($application, null, $status->id, $request->user()->id, 'Application created.');

            return $application;
        });

        return redirect()->route('staff.applications.show', $application)->with('success', 'Application created successfully.');
    }

    public function show(ServiceApplication $application): View
    {
        return view('staff.applications.show', [
            'application' => $application->load([
                'person',
                'service',
                'department',
                'assignedOfficer',
                'status',
                'documents.documentType',
                'payments.method',
                'appointments.officer',
                'notes.creator',
                'statusHistories.fromStatus',
                'statusHistories.toStatus',
                'statusHistories.changedBy',
            ]),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
        ]);
    }

    public function edit(ServiceApplication $application): View
    {
        return view('staff.applications.edit', $this->formData($application, $application->person));
    }

    public function update(Request $request, ServiceApplication $application): RedirectResponse
    {
        $data = $this->validated($request, $application);
        $oldStatusId = $application->status_id;

        $application->update($data);

        if ((int) $oldStatusId !== (int) $data['status_id']) {
            $this->recordStatusHistory($application, $oldStatusId, (int) $data['status_id'], $request->user()->id, $data['remarks'] ?? null);
        }

        return redirect()->route('staff.applications.show', $application)->with('success', 'Application updated successfully.');
    }

    public function updateStatus(Request $request, ServiceApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'status_id' => ['required', 'exists:application_statuses,id'],
            'remarks' => ['nullable', 'string'],
            'rejection_reason' => ['nullable', 'required_if:status_id,'.ApplicationStatus::where('code', 'rejected')->value('id'), 'string'],
        ]);

        $oldStatusId = $application->status_id;
        $status = ApplicationStatus::findOrFail($data['status_id']);

        $timestamps = [];
        if (in_array($status->code, ['approved', 'rejected', 'completed', 'cancelled'], true)) {
            $timestamps[$status->code.'_at'] = now();
        }

        $application->update([
            'status_id' => $status->id,
            'remarks' => $data['remarks'] ?? $application->remarks,
            'rejection_reason' => $data['rejection_reason'] ?? $application->rejection_reason,
        ] + $timestamps);

        $this->recordStatusHistory($application, $oldStatusId, $status->id, $request->user()->id, $data['remarks'] ?? null);

        return back()->with('success', 'Application status updated successfully.');
    }

    public function receipt(ServiceApplication $application): View
    {
        return view('staff.applications.receipt', [
            'application' => $application->load(['person', 'service', 'department', 'assignedOfficer', 'status', 'payments.method']),
        ]);
    }

    private function validated(Request $request, ?ServiceApplication $application = null): array
    {
        return $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'service_id' => ['required', 'exists:services,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'submitted_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:submitted_at'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'status_id' => ['required', 'exists:application_statuses,id'],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function formData(ServiceApplication $application, ?Person $person = null): array
    {
        return [
            'application' => $application,
            'person' => $person,
            'people' => Person::orderBy('full_name')->limit(200)->get(),
            'services' => Service::where('is_active', true)->with('department')->orderBy('name')->get(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'officers' => User::where('is_active', true)->whereHas('roles', fn ($query) => $query->where('slug', 'department_officer'))->orderBy('name')->get(),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
        ];
    }

    private function generateApplicationNumber(): string
    {
        $prefix = 'APP-'.now()->format('Y').'-';
        $next = ServiceApplication::withTrashed()->where('application_no', 'like', $prefix.'%')->count() + 1;

        do {
            $number = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (ServiceApplication::withTrashed()->where('application_no', $number)->exists());

        return $number;
    }

    private function recordStatusHistory(ServiceApplication $application, ?int $fromStatusId, int $toStatusId, int $userId, ?string $remarks): void
    {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status_id' => $fromStatusId,
            'to_status_id' => $toStatusId,
            'changed_by' => $userId,
            'remarks' => $remarks,
            'changed_at' => now(),
        ]);
    }
}
