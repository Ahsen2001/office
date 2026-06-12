<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ApplicationStatus;
use App\Models\Department;
use App\Models\Payment;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $applications = $this->applicationQuery($filters)->with(['person', 'service', 'department', 'status', 'submitter'])->get();
        $payments = $this->paymentQuery($filters)->with(['person', 'application', 'service', 'method'])->get();
        $appointments = $this->appointmentQuery($filters)->with(['person', 'application', 'department', 'officer'])->get();
        $people = Person::query()
            ->when($filters['date_from'], fn ($query) => $query->whereDate('registered_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('registered_at', '<=', $filters['date_to']))
            ->get();

        return view('manager.reports.index', [
            'filters' => $filters,
            'departments' => Department::orderBy('name')->get(),
            'statuses' => ApplicationStatus::orderBy('sort_order')->get(),
            'summary' => [
                'people' => $people->count(),
                'applications' => $applications->count(),
                'pending' => $applications->whereIn('status.code', ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'])->count(),
                'completed' => $applications->where('status.code', 'completed')->count(),
                'rejected' => $applications->where('status.code', 'rejected')->count(),
                'payments' => $payments->sum('amount'),
                'appointments' => $appointments->count(),
            ],
            'applicationsByStatus' => $applications->groupBy(fn ($application) => $application->status?->name ?? 'Unknown')->map->count(),
            'applicationsByDepartment' => $applications->groupBy(fn ($application) => $application->department?->name ?? 'Unknown')->map->count(),
            'staffPerformance' => $applications->groupBy(fn ($application) => $application->submitter?->name ?? 'Unassigned')->map->count()->sortDesc()->take(10),
            'paymentsByStatus' => $payments->groupBy('status')->map->sum('amount'),
            'appointmentsByStatus' => $appointments->groupBy('status')->map->count(),
            'recentApplications' => $applications->sortByDesc('submitted_at')->take(10),
        ]);
    }

    public function export(Request $request, string $report, string $format)
    {
        $filters = $this->filters($request);
        $rows = $this->reportRows($report, $filters);

        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        if ($format === 'pdf') {
            return Pdf::loadView('manager.reports.export', compact('rows', 'report', 'filters'))->download($report.'-report.pdf');
        }

        if ($format === 'excel') {
            return (new FastExcel($rows))->download($report.'-report.xlsx');
        }

        return $this->csv($rows, $report.'-report.csv');
    }

    private function reportRows(string $report, array $filters): Collection
    {
        return match ($report) {
            'people' => Person::query()
                ->when($filters['date_from'], fn ($query) => $query->whereDate('registered_at', '>=', $filters['date_from']))
                ->when($filters['date_to'], fn ($query) => $query->whereDate('registered_at', '<=', $filters['date_to']))
                ->latest('registered_at')
                ->get()
                ->map(fn (Person $person) => [
                    'Person ID' => $person->person_code,
                    'Name' => $person->full_name,
                    'NIC' => $person->national_id,
                    'Phone' => $person->phone,
                    'Registered At' => optional($person->registered_at)->toDateTimeString(),
                ]),
            'payments' => $this->paymentQuery($filters)->with(['person', 'application', 'service', 'method'])->get()->map(fn (Payment $payment) => [
                'Receipt' => $payment->receipt_no,
                'Person' => $payment->person?->full_name,
                'Application' => $payment->application?->application_no,
                'Service' => $payment->service?->name,
                'Status' => $payment->status,
                'Amount' => $payment->amount,
                'Date' => optional($payment->payment_date)->toDateTimeString(),
            ]),
            'appointments' => $this->appointmentQuery($filters)->with(['person', 'department', 'officer'])->get()->map(fn (Appointment $appointment) => [
                'Appointment No' => $appointment->appointment_no,
                'Person' => $appointment->person?->full_name,
                'Department' => $appointment->department?->name,
                'Officer' => $appointment->officer?->name,
                'Status' => $appointment->status,
                'Date' => optional($appointment->appointment_date)->format('Y-m-d'),
                'Time' => optional($appointment->start_time)->format('H:i'),
            ]),
            'staff' => User::whereHas('roles', fn ($query) => $query->where('slug', 'staff'))->get()->map(fn (User $user) => [
                'Staff' => $user->name,
                'Applications Submitted' => ServiceApplication::where('submitted_by', $user->id)->count(),
                'People Registered' => Person::where('registered_by', $user->id)->count(),
            ]),
            default => $this->applicationReportQuery($report, $filters)->with(['person', 'service', 'department', 'status', 'submitter'])->get()->map(fn (ServiceApplication $application) => [
                'Application No' => $application->application_no,
                'Person' => $application->person?->full_name,
                'Service' => $application->service?->name,
                'Department' => $application->department?->name,
                'Status' => $application->status?->name,
                'Submitted By' => $application->submitter?->name,
                'Submitted At' => optional($application->submitted_at)->toDateTimeString(),
            ]),
        };
    }

    private function applicationReportQuery(string $report, array $filters): Builder
    {
        return $this->applicationQuery($filters)
            ->when($report === 'pending', fn ($query) => $query->whereHas('status', fn ($statusQuery) => $statusQuery->whereIn('code', ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'])))
            ->when($report === 'completed', fn ($query) => $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', 'completed')))
            ->when($report === 'rejected', fn ($query) => $query->whereHas('status', fn ($statusQuery) => $statusQuery->where('code', 'rejected')));
    }

    private function applicationQuery(array $filters): Builder
    {
        return ServiceApplication::query()
            ->when($filters['date_from'], fn ($query) => $query->whereDate('submitted_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('submitted_at', '<=', $filters['date_to']))
            ->when($filters['department_id'], fn ($query) => $query->where('department_id', $filters['department_id']))
            ->when($filters['status_id'], fn ($query) => $query->where('status_id', $filters['status_id']));
    }

    private function paymentQuery(array $filters): Builder
    {
        return Payment::query()
            ->when($filters['date_from'], fn ($query) => $query->whereDate('payment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('payment_date', '<=', $filters['date_to']))
            ->when($filters['department_id'], fn ($query) => $query->whereHas('application', fn ($applicationQuery) => $applicationQuery->where('department_id', $filters['department_id'])));
    }

    private function appointmentQuery(array $filters): Builder
    {
        return Appointment::query()
            ->when($filters['date_from'], fn ($query) => $query->whereDate('appointment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($query) => $query->whereDate('appointment_date', '<=', $filters['date_to']))
            ->when($filters['department_id'], fn ($query) => $query->where('department_id', $filters['department_id']));
    }

    private function filters(Request $request): array
    {
        return [
            'date_from' => $request->date('date_from')?->format('Y-m-d'),
            'date_to' => $request->date('date_to')?->format('Y-m-d'),
            'department_id' => $request->integer('department_id') ?: null,
            'status_id' => $request->integer('status_id') ?: null,
        ];
    }

    private function csv(Collection $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()));
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
