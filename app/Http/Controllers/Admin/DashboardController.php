<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Person;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = ServiceApplication::query()
            ->join('application_statuses', 'service_applications.status_id', '=', 'application_statuses.id')
            ->selectRaw('application_statuses.code, count(*) as total')
            ->groupBy('application_statuses.code')
            ->pluck('total', 'code');

        $monthlyApplications = ServiceApplication::query()
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['submitted_at'])
            ->groupBy(fn ($application) => $application->submitted_at?->format('Y-m'))
            ->map->count()
            ->sortKeys();

        $departmentApplications = Branch::query()
            ->leftJoin('service_applications', 'branches.id', '=', 'service_applications.branch_id')
            ->select('branches.name', DB::raw('count(service_applications.id) as total'))
            ->groupBy('branches.id', 'branches.name')
            ->orderBy('branches.name')
            ->pluck('total', 'branches.name');

        return view('dashboards.admin', [
            'users' => User::query()->count(),
            'departments' => Branch::query()->count(),
            'people' => Person::query()->count(),
            'applications' => ServiceApplication::query()->count(),
            'pendingApplications' => ($statusCounts['submitted'] ?? 0) + ($statusCounts['pending'] ?? 0) + ($statusCounts['under_review'] ?? 0) + ($statusCounts['processing'] ?? 0) + ($statusCounts['waiting_for_documents'] ?? 0),
            'completedApplications' => $statusCounts['completed'] ?? 0,
            'rejectedApplications' => $statusCounts['rejected'] ?? 0,
            'services' => Service::query()->count(),
            'staff' => User::query()->whereHas('roles', fn ($query) => $query->whereIn('slug', ['reception', 'branch_head', 'branch_staff']))->count(),
            'todayApplications' => ServiceApplication::query()->whereDate('submitted_at', today())->count(),
            'todayAppointments' => Appointment::query()->whereDate('appointment_date', today())->count(),
            'recentAppointments' => Appointment::query()->with(['person', 'department', 'officer'])->whereDate('appointment_date', '>=', today())->orderBy('appointment_date')->orderBy('start_time')->limit(6)->get(),
            'monthlyApplications' => $monthlyApplications,
            'departmentApplications' => $departmentApplications,
            'recentApplications' => ServiceApplication::query()->with(['person', 'service', 'department', 'status'])->latest()->limit(6)->get(),
            'recentPeople' => Person::query()->latest()->limit(6)->get(),
            'pendingTasks' => ServiceApplication::query()->with(['person', 'service', 'status'])
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents']))
                ->latest()
                ->limit(6)
                ->get(),
            'recentActivities' => AuditLog::query()->with('user')->latest()->limit(8)->get(),
            'auditSummary' => AuditLog::query()->selectRaw('action, count(*) total')->groupBy('action')->orderByDesc('total')->limit(5)->pluck('total', 'action'),
        ]);
    }
}
