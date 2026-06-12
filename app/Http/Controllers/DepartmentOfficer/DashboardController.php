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
        return view('dashboards.officer', [
            'assignedCount' => ServiceApplication::where('assigned_officer_id', $request->user()->id)->count(),
        ]);
    }
}
