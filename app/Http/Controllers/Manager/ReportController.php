<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\ApplicationStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REPORTS = [
        'people' => 'People Registration',
        'applications' => 'Applications',
        'pending' => 'Pending Applications',
        'completed' => 'Completed Works',
        'rejected' => 'Rejected Applications',
        'branches' => 'Branch-wise',
        'branch_heads' => 'Branch Head Performance',
        'branch_staff' => 'Branch Staff Performance',
        'officers' => 'Officer Workload',
        'delayed' => 'Delayed Applications',
        'appointments' => 'Appointments',
        'daily' => 'Daily Summary',
        'monthly' => 'Monthly Summary',
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $applications = $this->applicationQuery($request, $filters)
            ->with(['person', 'service', 'branch', 'status', 'assignedOfficer'])
            ->get();
        $appointments = $this->appointmentQuery($request, $filters)
            ->with(['person', 'application', 'branch', 'officer'])
            ->get();
        $people = $this->peopleQuery($request, $filters)->get();
        $pendingCodes = ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'];

        return view('manager.reports.index', [
            'filters' => $filters,
            'branches' => Branch::visibleTo($request->user())->orderBy('name')->get(),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
            'officers' => $this->officerOptions($request),
            'availableReports' => $this->availableReports($request),
            'scopeLabel' => $request->user()->isBranchRestricted()
                ? $request->user()->branch?->name.' only'
                : 'All authorized branches',
            'summary' => [
                'people' => $people->count(),
                'applications' => $applications->count(),
                'pending' => $applications->whereIn('status.code', $pendingCodes)->count(),
                'completed' => $applications->where('status.code', 'completed')->count(),
                'rejected' => $applications->where('status.code', 'rejected')->count(),
                'delayed' => $applications->filter(fn ($application) => $application->due_date?->isPast() && in_array($application->status?->code, $pendingCodes, true))->count(),
                'appointments' => $appointments->count(),
            ],
            'applicationsByStatus' => $applications->groupBy(fn ($application) => $application->status?->name ?? 'Unknown')->map->count(),
            'applicationsByBranch' => $applications->groupBy(fn ($application) => $application->branch?->name ?? 'Unassigned')->map->count(),
            'officerWorkload' => $applications->groupBy(fn ($application) => $application->assignedOfficer?->name ?? 'Unassigned')->map->count()->sortDesc()->take(10),
            'monthlyApplications' => $applications
                ->groupBy(fn ($application) => $application->submitted_at?->format('Y-m') ?? 'Unknown')
                ->map->count()
                ->sortKeys(),
            'recentApplications' => $applications->sortByDesc('submitted_at')->take(12),
        ]);
    }

    public function export(Request $request, string $report, string $format)
    {
        abort_unless(array_key_exists($report, $this->availableReports($request)), 403);
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        $filters = $this->filters($request);
        $rows = $this->reportRows($request, $report, $filters);
        $label = self::REPORTS[$report];
        $filename = str($report)->replace('_', '-').'-report';

        if ($format === 'pdf') {
            return Pdf::loadView('manager.reports.export', compact('rows', 'report', 'label', 'filters'))
                ->setPaper('a4', $rows->first() && count($rows->first()) > 7 ? 'landscape' : 'portrait')
                ->download($filename.'.pdf');
        }

        if ($format === 'excel') {
            return (new FastExcel($rows))->download($filename.'.xlsx');
        }

        return $this->csv($rows, $filename.'.csv');
    }

    private function reportRows(Request $request, string $report, array $filters): Collection
    {
        return match ($report) {
            'people' => $this->peopleQuery($request, $filters)
                ->latest('registered_at')
                ->get()
                ->map(fn (Person $person) => [
                    'Person ID' => $person->person_code,
                    'Name' => $person->full_name,
                    'NIC / Passport' => $person->national_id ?: $person->passport_no,
                    'Registered At' => optional($person->registered_at)->format('Y-m-d H:i'),
                ]),
            'appointments' => $this->appointmentQuery($request, $filters)
                ->with(['person', 'branch', 'officer'])
                ->orderByDesc('appointment_date')
                ->get()
                ->map(fn (Appointment $appointment) => [
                    'Appointment No' => $appointment->appointment_no,
                    'Person' => $appointment->person?->full_name,
                    'Branch' => $appointment->branch?->name,
                    'Officer' => $appointment->officer?->name,
                    'Status' => str($appointment->status)->replace('_', ' ')->title(),
                    'Date' => optional($appointment->appointment_date)->format('Y-m-d'),
                    'Time' => optional($appointment->start_time)->format('H:i'),
                ]),
            'branches' => $this->branchPerformanceRows($request, $filters),
            'branch_heads' => $this->branchHeadPerformanceRows($request, $filters),
            'branch_staff' => $this->staffPerformanceRows($request, $filters, 'branch_staff'),
            'officers' => $this->officerWorkloadRows($request, $filters),
            'daily' => $this->periodRows($request, $filters, 'Y-m-d'),
            'monthly' => $this->periodRows($request, $filters, 'Y-m'),
            default => $this->applicationReportQuery($request, $report, $filters)
                ->with(['person', 'service', 'branch', 'status', 'assignedOfficer'])
                ->latest('submitted_at')
                ->get()
                ->map(fn (ServiceApplication $application) => [
                    'Application No' => $application->application_no,
                    'Person' => $application->person?->full_name,
                    'Service' => $application->service?->name,
                    'Branch' => $application->branch?->name,
                    'Officer' => $application->assignedOfficer?->name ?? 'Unassigned',
                    'Status' => $application->status?->name,
                    'Priority' => ucfirst($application->priority),
                    'Deadline' => optional($application->due_date)->format('Y-m-d'),
                    'Submitted At' => optional($application->submitted_at)->format('Y-m-d H:i'),
                ]),
        };
    }

    private function applicationReportQuery(Request $request, string $report, array $filters): Builder
    {
        $pendingCodes = ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'];

        return $this->applicationQuery($request, $filters)
            ->when($report === 'pending', fn ($query) => $query->whereHas('status', fn ($status) => $status->whereIn('code', $pendingCodes)))
            ->when($report === 'completed', fn ($query) => $query->whereHas('status', fn ($status) => $status->where('code', 'completed')))
            ->when($report === 'rejected', fn ($query) => $query->whereHas('status', fn ($status) => $status->where('code', 'rejected')))
            ->when($report === 'delayed', fn ($query) => $query
                ->whereDate('due_date', '<', today())
                ->whereHas('status', fn ($status) => $status->whereIn('code', $pendingCodes)));
    }

    private function applicationQuery(Request $request, array $filters): Builder
    {
        return ServiceApplication::query()
            ->visibleTo($request->user())
            ->when($filters['date_from'], fn ($query) => $query->whereDate('submitted_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('submitted_at', '<=', $filters['date_to']))
            ->when($filters['branch_id'], fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->when($filters['status_id'], fn ($query) => $query->where('status_id', $filters['status_id']))
            ->when($filters['officer_id'], fn ($query) => $query->where('assigned_officer_id', $filters['officer_id']));
    }

    private function appointmentQuery(Request $request, array $filters): Builder
    {
        return Appointment::query()
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->when($filters['date_from'], fn ($query) => $query->whereDate('appointment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('appointment_date', '<=', $filters['date_to']))
            ->when($filters['branch_id'], fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->when($filters['officer_id'], fn ($query) => $query->where('officer_id', $filters['officer_id']));
    }

    private function peopleQuery(Request $request, array $filters): Builder
    {
        return Person::query()
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->whereHas(
                'applications',
                fn ($applications) => $applications->where('branch_id', $request->user()->branch_id)
            ))
            ->when($filters['date_from'], fn ($query) => $query->whereDate('registered_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('registered_at', '<=', $filters['date_to']));
    }

    private function branchPerformanceRows(Request $request, array $filters): Collection
    {
        return Branch::visibleTo($request->user())
            ->when($filters['branch_id'], fn ($query) => $query->whereKey($filters['branch_id']))
            ->withCount([
                'applications as total_applications' => fn ($query) => $this->applyApplicationDateFilters($query, $filters),
                'applications as completed_applications' => fn ($query) => $this->applyApplicationDateFilters($query, $filters)
                    ->whereHas('status', fn ($status) => $status->where('code', 'completed')),
                'applications as rejected_applications' => fn ($query) => $this->applyApplicationDateFilters($query, $filters)
                    ->whereHas('status', fn ($status) => $status->where('code', 'rejected')),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (Branch $branch) => [
                'Branch' => $branch->name,
                'Branch Head' => $branch->head?->name ?? 'Unassigned',
                'Total Applications' => $branch->total_applications,
                'Completed' => $branch->completed_applications,
                'Rejected' => $branch->rejected_applications,
                'Completion Rate' => $branch->total_applications
                    ? round(($branch->completed_applications / $branch->total_applications) * 100, 1).'%'
                    : '0%',
            ]);
    }

    private function staffPerformanceRows(Request $request, array $filters, string $role): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('slug', $role))
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->when($filters['branch_id'], fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->with('branch')
            ->withCount([
                'assignedApplications as workload' => fn ($query) => $this->applyApplicationDateFilters($query, $filters),
                'assignedApplications as completed_workload' => fn ($query) => $this->applyApplicationDateFilters($query, $filters)
                    ->whereHas('status', fn ($status) => $status->where('code', 'completed')),
                'assignedApplications as delayed_workload' => fn ($query) => $this->applyApplicationDateFilters($query, $filters)
                    ->whereDate('due_date', '<', today())
                    ->whereHas('status', fn ($status) => $status->whereIn('code', ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'])),
            ])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'Officer' => $user->name,
                'Designation' => $user->roles->first()?->name,
                'Branch' => $user->branch?->name,
                'Workload' => $user->workload,
                'Completed' => $user->completed_workload,
                'Delayed' => $user->delayed_workload,
                'Completion Rate' => $user->workload ? round(($user->completed_workload / $user->workload) * 100, 1).'%' : '0%',
            ]);
    }

    private function branchHeadPerformanceRows(Request $request, array $filters): Collection
    {
        $pendingCodes = ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'];

        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'branch_head'))
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->when($filters['branch_id'], fn ($query) => $query->where('branch_id', $filters['branch_id']))
            ->with('branch')
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($filters, $pendingCodes) {
                $applications = ServiceApplication::where('branch_id', $user->branch_id);
                $this->applyApplicationDateFilters($applications, $filters);
                $total = (clone $applications)->count();
                $completed = (clone $applications)->whereHas('status', fn ($status) => $status->where('code', 'completed'))->count();
                $delayed = (clone $applications)
                    ->whereDate('due_date', '<', today())
                    ->whereHas('status', fn ($status) => $status->whereIn('code', $pendingCodes))
                    ->count();

                return [
                    'Branch Head' => $user->name,
                    'Branch' => $user->branch?->name,
                    'Branch Applications' => $total,
                    'Completed' => $completed,
                    'Delayed' => $delayed,
                    'Completion Rate' => $total ? round(($completed / $total) * 100, 1).'%' : '0%',
                ];
            });
    }

    private function officerWorkloadRows(Request $request, array $filters): Collection
    {
        return $this->staffPerformanceRows($request, $filters, 'branch_staff')
            ->sortByDesc('Workload')
            ->values();
    }

    private function periodRows(Request $request, array $filters, string $format): Collection
    {
        return $this->applicationQuery($request, $filters)
            ->with('status')
            ->get()
            ->groupBy(fn ($application) => $application->submitted_at?->format($format) ?? 'Unknown')
            ->map(function (Collection $applications, string $period) {
                return [
                    'Period' => $period,
                    'Applications' => $applications->count(),
                    'Pending' => $applications->filter(fn ($application) => in_array($application->status?->code, ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'], true))->count(),
                    'Completed' => $applications->where('status.code', 'completed')->count(),
                    'Rejected' => $applications->where('status.code', 'rejected')->count(),
                ];
            })
            ->sortKeys()
            ->values();
    }

    private function applyApplicationDateFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['date_from'], fn ($query) => $query->whereDate('submitted_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('submitted_at', '<=', $filters['date_to']));
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'status_id' => ['nullable', 'integer', Rule::exists('application_statuses', 'id')],
            'officer_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        if ($request->user()->isBranchRestricted()) {
            $validated['branch_id'] = $request->user()->branch_id;
        }

        return [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'status_id' => $validated['status_id'] ?? null,
            'officer_id' => $validated['officer_id'] ?? null,
        ];
    }

    private function officerOptions(Request $request): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['branch_head', 'branch_staff']))
            ->when($request->user()->isBranchRestricted(), fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->with('branch')
            ->orderBy('name')
            ->get();
    }

    private function availableReports(Request $request): array
    {
        $user = $request->user();

        if ($user->hasRole('admin', 'management')) {
            return self::REPORTS;
        }

        if ($user->hasRole('branch_head')) {
            return collect(self::REPORTS)->only([
                'applications', 'pending', 'completed', 'rejected', 'branches',
                'branch_staff', 'officers', 'delayed', 'appointments', 'daily', 'monthly',
            ])->all();
        }

        if ($user->hasRole('branch_staff')) {
            return collect(self::REPORTS)->only([
                'applications', 'pending', 'completed', 'delayed', 'appointments', 'daily', 'monthly',
            ])->all();
        }

        return collect(self::REPORTS)->only(['people', 'applications', 'daily', 'monthly'])->all();
    }

    private function csv(Collection $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()));
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
