<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceApplication;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboards.manager', [
            'applicationsCount' => ServiceApplication::count(),
            'paidTotal' => Payment::where('status', 'paid')->sum('amount'),
        ]);
    }
}
