<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceApplication;

class ReportController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'applications_by_status' => ServiceApplication::query()
                ->join('application_statuses', 'service_applications.status_id', '=', 'application_statuses.id')
                ->selectRaw('application_statuses.name, count(*) as total')
                ->groupBy('application_statuses.name')
                ->pluck('total', 'name'),
            'paid_total' => Payment::where('status', 'paid')->sum('amount'),
        ]);
    }
}
