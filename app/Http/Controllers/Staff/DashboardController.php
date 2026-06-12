<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
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
            'todayAppointments' => Appointment::whereDate('appointment_date', today())->count(),
            'upcomingAppointments' => Appointment::with(['person', 'department'])->whereDate('appointment_date', '>=', today())->orderBy('appointment_date')->orderBy('start_time')->limit(6)->get(),
        ]);
    }
}
