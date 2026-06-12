<?php

namespace App\Http\Controllers\DepartmentOfficer;

use App\Http\Controllers\Controller;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $departmentId = $request->user()->department_id;

        $base = ServiceApplication::where('department_id', $departmentId);

        return view('dashboards.officer', [
            'assignedCount' => (clone $base)->count(),
            'pendingCount' => (clone $base)->whereHas('status', fn ($query) => $query->whereIn('code', ['submitted', 'pending', 'under_review', 'waiting_for_documents']))->count(),
            'processingCount' => (clone $base)->whereHas('status', fn ($query) => $query->where('code', 'processing'))->count(),
            'completedCount' => (clone $base)->whereHas('status', fn ($query) => $query->where('code', 'completed'))->count(),
            'recentApplications' => (clone $base)->with(['person', 'service', 'status'])->latest()->limit(8)->get(),
        ]);
    }
}
