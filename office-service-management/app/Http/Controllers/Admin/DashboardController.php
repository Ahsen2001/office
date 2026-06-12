<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
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
            ->selectRaw("DATE_FORMAT(submitted_at, '%Y-%m') as month, count(*) as total")
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $departmentApplications = Department::query()
            ->leftJoin('service_applications', 'departments.id', '=', 'service_applications.department_id')
            ->select('departments.name', DB::raw('count(service_applications.id) as total'))
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->pluck('total', 'departments.name');

        return view('dashboards.admin', [
            'users' => User::count(),
            'departments' => Department::count(),
            'people' => Person::count(),
            'applications' => ServiceApplication::count(),
            'pendingApplications' => ($statusCounts['submitted'] ?? 0) + ($statusCounts['pending'] ?? 0) + ($statusCounts['under_review'] ?? 0) + ($statusCounts['processing'] ?? 0) + ($statusCounts['waiting_for_documents'] ?? 0),
            'completedApplications' => $statusCounts['completed'] ?? 0,
            'rejectedApplications' => $statusCounts['rejected'] ?? 0,
            'services' => Service::count(),
            'staff' => User::whereHas('roles', fn ($query) => $query->where('slug', 'staff'))->count(),
            'todayApplications' => ServiceApplication::whereDate('submitted_at', today())->count(),
            'monthlyApplications' => $monthlyApplications,
            'departmentApplications' => $departmentApplications,
            'recentApplications' => ServiceApplication::with(['person', 'service', 'department', 'status'])->latest()->limit(6)->get(),
            'recentPeople' => Person::latest()->limit(6)->get(),
            'pendingTasks' => ServiceApplication::with(['person', 'service', 'status'])
                ->whereHas('status', fn ($query) => $query->whereIn('code', ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents']))
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
