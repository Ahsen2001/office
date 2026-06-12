<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Person;
use App\Models\ServiceApplication;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.admin', [
            'users' => User::count(),
            'departments' => Department::count(),
            'people' => Person::count(),
            'applications' => ServiceApplication::count(),
        ]);
    }
}
