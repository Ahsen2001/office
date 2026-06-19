<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function management(): View
    {
        $pendingCodes = ['submitted', 'pending', 'under_review', 'processing', 'waiting_for_documents'];
        $branches = Branch::withCount([
            'applications',
            'applications as pending_count' => fn ($query) => $query->whereHas('status', fn ($status) => $status->whereIn('code', $pendingCodes)),
            'applications as completed_count' => fn ($query) => $query->whereHas('status', fn ($status) => $status->where('code', 'completed')),
        ])->orderBy('name')->get();

        $monthly = ServiceApplication::query()
            ->where('submitted_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['submitted_at'])
            ->groupBy(fn ($application) => $application->submitted_at?->format('Y-m'))
            ->map->count()
            ->sortKeys();

        return view('dashboards.management', [
            'branches' => $branches,
            'pendingCount' => ServiceApplication::query()->whereHas('status', fn ($query) => $query->whereIn('code', $pendingCodes))->count(),
            'completedCount' => ServiceApplication::query()->whereHas('status', fn ($query) => $query->where('code', 'completed'))->count(),
            'delayedCount' => ServiceApplication::query()->whereDate('due_date', '<', today())->whereHas('status', fn ($query) => $query->whereIn('code', $pendingCodes))->count(),
            'officerWorkload' => User::query()->whereHas('roles', fn ($query) => $query->whereIn('slug', ['branch_head', 'branch_staff']))
                ->withCount(['assignedApplications as workload'])->orderByDesc('workload')->limit(10)->get(),
            'monthlyApplications' => $monthly,
        ]);
    }

    public function reception(): View
    {
        return view('dashboards.reception', [
            'todayPeople' => Person::query()->whereDate('registered_at', today())->count(),
            'todayApplications' => ServiceApplication::query()->whereDate('submitted_at', today())->count(),
            'recentPeople' => Person::query()->latest('registered_at')->limit(8)->get(),
            'recentApplications' => ServiceApplication::query()->with(['person', 'branch', 'status'])->latest('submitted_at')->limit(8)->get(),
        ]);
    }

    public function branchHead(Request $request): View
    {
        return $this->branchDashboard($request, true);
    }

    public function branchStaff(Request $request): View
    {
        return $this->branchDashboard($request, false);
    }

    private function branchDashboard(Request $request, bool $head): View
    {
        $branch = Branch::query()->findOrFail($request->user()->branch_id);
        $base = ServiceApplication::query()->where('branch_id', $branch->id);
        $assigned = $head ? clone $base : (clone $base)->where('assigned_officer_id', $request->user()->id);

        return view($head ? 'dashboards.branch-head' : 'dashboards.branch-staff', [
            'branch' => $branch,
            'assignedCount' => (clone $assigned)->count(),
            'pendingCount' => (clone $assigned)->whereHas('status', fn ($query) => $query->whereIn('code', ['submitted', 'pending', 'under_review']))->count(),
            'processingCount' => (clone $assigned)->whereHas('status', fn ($query) => $query->where('code', 'processing'))->count(),
            'completedCount' => (clone $assigned)->whereHas('status', fn ($query) => $query->where('code', 'completed'))->count(),
            'rejectedCount' => (clone $base)->whereHas('status', fn ($query) => $query->where('code', 'rejected'))->count(),
            'waitingDocumentsCount' => (clone $assigned)->whereHas('status', fn ($query) => $query->where('code', 'waiting_for_documents'))->count(),
            'recentApplications' => (clone $assigned)->with(['person', 'service', 'status', 'assignedOfficer'])->latest()->limit(10)->get(),
            'staffPerformance' => $head ? User::query()->where('branch_id', $branch->id)
                ->whereHas('roles', fn ($query) => $query->where('slug', 'branch_staff'))
                ->withCount(['assignedApplications as workload', 'assignedApplications as completed_workload' => fn ($query) => $query->whereHas('status', fn ($status) => $status->where('code', 'completed'))])
                ->orderBy('name')->get() : collect(),
        ]);
    }
}
