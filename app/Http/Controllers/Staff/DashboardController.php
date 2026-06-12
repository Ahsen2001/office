<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\ServiceApplication;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.staff', [
            'peopleCount' => Person::count(),
            'applicationsCount' => ServiceApplication::count(),
        ]);
    }
}
